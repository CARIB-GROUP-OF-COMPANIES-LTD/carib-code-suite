const fs = require('fs');
const http = require('http');

async function getCaribTarget() {
  return new Promise((resolve, reject) => {
    http.get('http://127.0.0.1:9392/json', (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        const targets = JSON.parse(data);
        const carib = targets.find(t => t.url && t.url.includes('caribclaude') && t.type === 'page');
        if (carib) resolve(carib);
        else reject(new Error('CaribClaude page target not found in CDP'));
      });
    }).on('error', reject);
  });
}

async function run() {
  const target = await getCaribTarget();
  console.log(`Connecting to CDP target: ${target.id} (${target.url})`);

  const ws = new WebSocket(target.webSocketDebuggerUrl);

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

  console.log('1. Reloading page with cache bypass...');
  await send('Page.reload', { ignoreCache: true });
  await new Promise(r => setTimeout(r, 4000));

  console.log('2. Inspecting DOM and Layout Features...');
  const pageState = await evaluate(`(() => {
    const heroTitle = document.querySelector('.claude-hero-title')?.textContent?.trim();
    const heroIcon = document.querySelector('.claude-sunburst')?.textContent?.trim();
    const chips = Array.from(document.querySelectorAll('.claude-category-chip')).map(c => c.textContent.trim().replace(/\\s+/g, ' '));
    const activeCard = document.querySelector('.active-session-card');
    const activeCardTitle = activeCard?.querySelector('.active-session-title')?.textContent?.trim();
    const activeCardTokens = activeCard?.querySelector('.active-session-meta')?.textContent?.trim();
    const sidebarBrand = document.querySelector('.brand h1')?.textContent?.trim();
    const sidebarNav = Array.from(document.querySelectorAll('.nav-link')).map(n => n.textContent.trim());
    const chatsHeading = document.querySelector('.chats-section-header span')?.textContent?.trim();
    const codeSessionInSidebar = document.querySelector('.code-session-item')?.textContent?.trim()?.replace(/\\s+/g, ' ');
    const userBadge = document.querySelector('#userName')?.textContent?.trim();
    const modelOptions = Array.from(document.querySelectorAll('#claudeModelSelect option')).map(o => o.textContent.trim());
    const textareaPlaceholder = document.querySelector('#claudeMainPrompt')?.getAttribute('placeholder');

    return {
      heroTitle,
      heroIcon,
      chips,
      activeCardTitle,
      activeCardTokens,
      sidebarBrand,
      sidebarNav,
      chatsHeading,
      codeSessionInSidebar,
      userBadge,
      modelOptions,
      textareaPlaceholder
    };
  })()`);

  console.log('--- Page State Diagnostic ---');
  console.log(JSON.stringify(pageState, null, 2));

  console.log('3. Testing Category Chips Interaction...');
  // Click the 'Code' chip
  const clickCodeResult = await evaluate(`(() => {
    const codeChip = document.querySelectorAll('.claude-category-chip')[1];
    if (codeChip) {
      codeChip.click();
      return document.querySelector('#claudeMainPrompt')?.value;
    }
    return null;
  })()`);
  console.log('Textarea value after clicking Code chip:', clickCodeResult);

  console.log('4. Capturing High-Res Viewport Screenshot...');
  const shot = await send('Page.captureScreenshot', { format: 'png' });
  const buffer = Buffer.from(shot.data, 'base64');
  const screenshotPath = '/Users/djooozyb/.gemini/antigravity-ide/brain/3bf3e0e6-610d-417b-bcdc-e33186d43866/caribcold_dashboard_verified.png';
  fs.writeFileSync(screenshotPath, buffer);
  console.log(`Saved screenshot to: ${screenshotPath} (${buffer.length} bytes)`);

  console.log('5. Testing 1-Click Resume into Active Coding Session...');
  const resumeResult = await evaluate(`(() => {
    const resumeBtn = document.querySelector('.btn-resume-session');
    if (resumeBtn) {
      resumeBtn.click();
      return {
        sessionActive: document.querySelector('.app-shell').classList.contains('session-active'),
        activeView: window.state ? window.state.activeView : 'unknown',
        codeRootVisible: !document.getElementById('codeRoot')?.hidden
      };
    }
    return null;
  })()`);
  console.log('Resume button action result:', resumeResult);

  await new Promise(r => setTimeout(r, 2000));
  const shotCode = await send('Page.captureScreenshot', { format: 'png' });
  const codeBuffer = Buffer.from(shotCode.data, 'base64');
  const codeScreenshotPath = '/Users/djooozyb/.gemini/antigravity-ide/brain/3bf3e0e6-610d-417b-bcdc-e33186d43866/caribcold_code_session_resumed.png';
  fs.writeFileSync(codeScreenshotPath, codeBuffer);
  console.log(`Saved code session screenshot to: ${codeScreenshotPath} (${codeBuffer.length} bytes)`);

  ws.close();
  console.log('Test completed successfully!');
}

run().catch(err => {
  console.error('Test error:', err);
  process.exit(1);
});
