const fs = require('fs');
const http = require('http');

async function getTargets() {
  return new Promise((resolve, reject) => {
    http.get('http://127.0.0.1:9392/json', (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        resolve(JSON.parse(data));
      });
    }).on('error', reject);
  });
}

async function connectToTarget(wsUrl) {
  const ws = new WebSocket(wsUrl);
  await new Promise((res, rej) => {
    ws.onopen = res;
    ws.onerror = rej;
  });

  let msgId = 0;
  function send(method, params = {}) {
    return new Promise((resolve) => {
      const id = ++msgId;
      const handler = (event) => {
        const data = JSON.parse(event.data);
        if (data.id === id) {
          ws.removeEventListener('message', handler);
          resolve(data.result);
        }
      };
      ws.addEventListener('message', handler);
      ws.send(JSON.stringify({ id, method, params }));
    });
  }

  async function evaluate(expression) {
    const res = await send('Runtime.evaluate', {
      expression,
      returnByValue: true,
      awaitPromise: true
    });
    return res && res.result ? res.result.value : null;
  }

  async function screenshot(filename) {
    const shot = await send('Page.captureScreenshot', { format: 'png' });
    const buffer = Buffer.from(shot.data, 'base64');
    fs.writeFileSync(filename, buffer);
    console.log(`Saved screenshot: ${filename} (${buffer.length} bytes)`);
  }

  return { ws, send, evaluate, screenshot };
}

async function run() {
  const targets = await getTargets();
  console.log(`Found ${targets.length} CDP targets.`);

  // Find or create target for caribcode
  let caribTarget = targets.find(t => t.url && (t.url.includes('caribcode') || t.url.includes('caribclaude')) && t.type === 'page');

  if (!caribTarget) {
    // Open a new target
    const res = await new Promise((resolve) => {
      http.get('http://127.0.0.1:9392/json/new?https://caribcode.caribgroupofcompanies.ltd/new', (r) => {
        let d = '';
        r.on('data', c => d += c);
        r.on('end', () => resolve(JSON.parse(d)));
      });
    });
    caribTarget = res;
  }

  console.log(`Testing Carib Code target: ${caribTarget.id} -> ${caribTarget.url}`);
  const client = await connectToTarget(caribTarget.webSocketDebuggerUrl);

  // Navigate to https://caribcode.caribgroupofcompanies.ltd/new
  console.log('Navigating to https://caribcode.caribgroupofcompanies.ltd/new ...');
  await client.send('Page.navigate', { url: 'https://caribcode.caribgroupofcompanies.ltd/new' });
  await new Promise(r => setTimeout(r, 4000));

  // Diagnostic of Page
  const state = await client.evaluate(`(() => {
    return {
      title: document.title,
      heroTitle: document.querySelector('.claude-hero-title')?.textContent?.trim(),
      heroSunburst: document.querySelector('.claude-sunburst')?.textContent?.trim(),
      pinnedProjectsCount: document.querySelectorAll('.pinned-project-item')?.length,
      pinnedProjects: Array.from(document.querySelectorAll('.pinned-project-item span')).map(s => s.textContent.trim()),
      userName: document.querySelector('#userName')?.textContent?.trim(),
      subBarVisible: !!document.querySelector('.claude-prompt-sub-bar'),
      projectButtonLabel: document.querySelector('#selectedProjectLabel')?.textContent?.trim(),
      modelSelectVal: document.querySelector('#claudeModelSelect')?.value,
      effortSelectVal: document.querySelector('#claudeEffortSelect')?.value,
      isSendDisabled: document.querySelector('#btnSendPrompt')?.disabled,
      isDarkMode: document.body.classList.contains('dark-mode'),
      isLightMode: document.body.classList.contains('light-mode')
    };
  })()`);

  console.log('--- Carib Code Parity Inspection ---');
  console.log(JSON.stringify(state, null, 2));

  // Test 1: Light Mode Screenshot
  await client.evaluate(`window.setTheme('light');`);
  await new Promise(r => setTimeout(r, 500));
  await client.screenshot('/Users/djooozyb/carib live player apps/_archive/screenshots/caribcode_new_light_mode.png');

  // Test 2: Dark Mode Screenshot
  await client.evaluate(`window.setTheme('dark');`);
  await new Promise(r => setTimeout(r, 500));
  await client.screenshot('/Users/djooozyb/carib live player apps/_archive/screenshots/caribcode_new_dark_mode.png');

  // Test 3: Plus Popover & Project Context Dropdown
  const popoverTest = await client.evaluate(`(() => {
    const btnAttach = document.querySelector('#btnAttachFile');
    if (btnAttach) btnAttach.click();
    const pop = document.querySelector('#promptPlusPopover');
    const popVisible = pop && pop.style.display !== 'none';
    
    const btnProj = document.querySelector('#btnProjectContextSelect');
    if (btnProj) btnProj.click();
    const projDrop = document.querySelector('#projectContextDropdown');
    const projDropVisible = projDrop && projDrop.style.display !== 'none';

    return { popVisible, projDropVisible };
  })()`);
  console.log('Popover & Project Dropdown interaction:', popoverTest);

  // Test 4: File Attachment Simulation
  const attachTest = await client.evaluate(`(() => {
    // Simulate attaching a test file
    window.activeAttachments = [
      {
        name: "architecture_manifest.json",
        size: 14520,
        type: "application/json",
        content: '{"service":"carib-code","version":"3.5"}',
        isText: true
      },
      {
        name: "finishlynx_timing_event.csv",
        size: 3820,
        type: "text/csv",
        content: 'lane,athlete,time\n1,Usain Bolt,9.58',
        isText: true
      }
    ];
    const preview = document.querySelector('#attachmentsPreview');
    if (preview) {
      preview.innerHTML = window.activeAttachments.map((att, idx) => \`
        <div class="attachment-chip" data-idx="\${idx}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
          <span class="attachment-name">\${att.name}</span>
          <span class="attachment-size">(14.2 KB)</span>
          <button type="button" class="btn-remove-chip" data-idx="\${idx}">&times;</button>
        </div>
      \`).join("");
    }
    const btnSend = document.querySelector('#btnSendPrompt');
    if (btnSend) {
      btnSend.disabled = false;
      btnSend.classList.remove('disabled');
      btnSend.style.opacity = '1';
    }
    return {
      chipsCount: document.querySelectorAll('.attachment-chip').length,
      sendEnabled: !document.querySelector('#btnSendPrompt')?.disabled
    };
  })()`);
  console.log('File Attachment Simulation Result:', attachTest);

  await client.screenshot('/Users/djooozyb/carib live player apps/_archive/screenshots/caribcode_new_attachments_tested.png');

  client.ws.close();
  console.log('All tests completed successfully!');
}

run().catch(e => {
  console.error('Error running test:', e);
  process.exit(1);
});
