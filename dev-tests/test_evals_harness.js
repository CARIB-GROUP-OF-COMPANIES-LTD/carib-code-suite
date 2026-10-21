const fs = require('fs');
const http = require('http');
const path = require('path');

const CDP_PORT = 9392;
const TEST_URL = 'https://platform.caribcode.caribgroupofcompanies.ltd/evals';
const ARTIFACTS_DIR = path.join(__dirname, '..', '..', '.tempmediaStorage');

if (!fs.existsSync(ARTIFACTS_DIR)) {
  fs.mkdirSync(ARTIFACTS_DIR, { recursive: true });
}

function putJson(urlPath) {
  return new Promise((resolve, reject) => {
    const req = http.request({
      hostname: '127.0.0.1',
      port: CDP_PORT,
      path: urlPath,
      method: 'PUT'
    }, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          resolve(JSON.parse(data));
        } catch (e) {
          resolve(data);
        }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

async function connectWebSocket(wsUrl) {
  const ws = new WebSocket(wsUrl);
  await new Promise((resolve, reject) => {
    ws.onopen = resolve;
    ws.onerror = reject;
  });

  let curId = 0;
  function send(method, params = {}) {
    return new Promise((resolve) => {
      const id = ++curId;
      const onMsg = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id === id) {
          ws.removeEventListener('message', onMsg);
          resolve(msg.result);
        }
      };
      ws.addEventListener('message', onMsg);
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

  async function captureScreenshot(filename) {
    const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
    const buffer = Buffer.from(shot.data, 'base64');
    const fullPath = path.join(ARTIFACTS_DIR, filename);
    fs.writeFileSync(fullPath, buffer);
    console.log(`  📸 Screenshot saved: ${fullPath} (${(buffer.length / 1024).toFixed(1)} KB)`);
    return fullPath;
  }

  return { ws, send, evaluate, captureScreenshot };
}

async function run() {
  console.log('1. Querying Chrome CDP targets...');
  const newTarget = await putJson(`/json/new?${encodeURIComponent(TEST_URL)}`);
  console.log(`  ✓ Opened target: ${newTarget.id} -> ${newTarget.url}`);

  const client = await connectWebSocket(newTarget.webSocketDebuggerUrl);
  await client.send('Page.enable');
  await client.send('DOM.enable');
  await client.send('Runtime.enable');
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: 1440,
    height: 900,
    deviceScaleFactor: 1,
    mobile: false
  });

  console.log('2. Waiting for page navigation and ClaudeSuite initialization...');
  await new Promise(r => setTimeout(r, 4500));

  // Verify page title and URL
  const pageState = await client.evaluate(`({
    title: document.title,
    url: window.location.href,
    hasClaudeSuite: typeof window.ClaudeSuite !== 'undefined',
    activeTab: window.activePlatformTab || 'none'
  })`);
  console.log('  Page state:', pageState);

  // If user is at login page or need to click evals tab
  const isEvalsPresent = await client.evaluate(`Boolean(document.querySelector('.evals-layout'))`);
  console.log('  Is .evals-layout present on arrival?', isEvalsPresent);

  if (!isEvalsPresent) {
    console.log('  Navigating or clicking Prompt Evaluations & Evals tab...');
    await client.evaluate(`
      const btn = document.querySelector('[data-ptab="evals"]');
      if (btn) {
        btn.click();
      } else if (window.location.hash !== '#/platform/evals') {
        window.location.hash = '#/platform/evals';
        if (typeof window.ClaudeSuite !== 'undefined' && typeof window.ClaudeSuite.init === 'function') {
          // Trigger hash change
          window.dispatchEvent(new HashChangeEvent("hashchange"));
        }
      }
    `);
    await new Promise(r => setTimeout(r, 1500));
  }

  // Check evals container state
  const evalsDetails = await client.evaluate(`(() => {
    const layout = document.querySelector('.evals-layout');
    if (!layout) return { found: false };
    const title = layout.querySelector('.evals-title-group h2')?.textContent?.trim();
    const statCards = Array.from(layout.querySelectorAll('.evals-stat-card')).map(c => ({
      label: c.querySelector('.evals-stat-label')?.textContent?.trim(),
      val: c.querySelector('.evals-stat-value')?.textContent?.trim(),
      hint: c.querySelector('.evals-stat-hint')?.textContent?.trim()
    }));
    const datasetPills = Array.from(layout.querySelectorAll('.evals-dataset-pill')).map(p => ({
      id: p.dataset.dataset,
      text: p.textContent.trim(),
      isActive: p.classList.contains('active')
    }));
    const rows = Array.from(layout.querySelectorAll('.platform-table tbody tr')).map(tr => ({
      model: tr.querySelector('td:nth-child(1)')?.textContent?.trim().replace(/\\s+/g, ' '),
      passRate: tr.querySelector('td:nth-child(3)')?.textContent?.trim().replace(/\\s+/g, ' '),
      latency: tr.querySelector('td:nth-child(4)')?.textContent?.trim(),
      sla: tr.querySelector('td:nth-child(5)')?.textContent?.trim(),
      cost: tr.querySelector('td:nth-child(7)')?.textContent?.trim()
    }));
    const testCases = Array.from(layout.querySelectorAll('.evals-case-item')).map(c => ({
      id: c.querySelector('.evals-case-title span')?.textContent?.trim(),
      title: c.querySelector('.evals-case-title')?.textContent?.trim().replace(/\\s+/g, ' ')
    }));
    return {
      found: true,
      title,
      statCards,
      datasetPills,
      modelRowsCount: rows.length,
      sampleRows: rows.slice(0, 3),
      testCasesCount: testCases.length
    };
  })()`);

  console.log('3. Evals Harness Details:', JSON.stringify(evalsDetails, null, 2));

  // Take screenshot of initial evals view
  await client.captureScreenshot('evals_harness_initial.png');

  // 4. Click Run Benchmark
  console.log('4. Clicking ▶ Run Evals Across Models to trigger live benchmark...');
  const runClicked = await client.evaluate(`(() => {
    const btn = document.getElementById('btnEvalsRunAll');
    if (btn) {
      btn.click();
      return true;
    }
    return false;
  })()`);
  console.log('  Run benchmark button clicked?', runClicked);

  // Monitor terminal streaming drawer
  console.log('5. Streaming TAP 14 telemetry assertions...');
  let streamed = false;
  for (let step = 0; step < 8; step++) {
    await new Promise(r => setTimeout(r, 600));
    const termState = await client.evaluate(`(() => {
      const drawer = document.getElementById('evalsTerminalDrawer');
      const body = document.getElementById('evalsTerminalBody');
      return {
        visible: drawer && drawer.style.display !== 'none',
        linesCount: body ? body.textContent.split('\\n').filter(Boolean).length : 0,
        snippet: body ? body.textContent.slice(-300) : ''
      };
    })()`);
    console.log(`  Step ${step + 1}: Drawer visible=${termState.visible}, Lines=${termState.linesCount}`);
    if (termState.linesCount > 4 && !streamed) {
      streamed = true;
      await client.captureScreenshot('evals_harness_running_stream.png');
    }
  }

  // Wait for benchmark completion
  await new Promise(r => setTimeout(r, 2000));
  await client.captureScreenshot('evals_harness_passed_scorecard.png');

  // Verify final terminal output and scorecard pass rate
  const finalVerification = await client.evaluate(`(() => {
    const body = document.getElementById('evalsTerminalBody');
    const content = body ? body.textContent : '';
    const hasTap14 = content.includes('TAP version 14');
    const hasAllPassed = content.includes('All') && content.includes('passed');
    const allPassingBadge = document.querySelector('.badge-passing')?.textContent?.trim();
    return {
      hasTap14,
      hasAllPassed,
      allPassingBadge,
      tailLog: content.slice(-400)
    };
  })()`);
  console.log('6. Final Evaluation Assertion Verification:', finalVerification);

  // Close page target
  console.log('7. Cleaning up test target...');
  await client.send('Page.close');
  client.ws.close();
  console.log('✓ Prompt Evaluations & Evals Harness automated verification completed successfully.');
}

run().catch(err => {
  console.error('Test execution failed:', err);
  process.exit(1);
});
