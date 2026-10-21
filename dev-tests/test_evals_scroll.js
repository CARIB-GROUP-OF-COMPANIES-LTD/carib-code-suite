const fs = require('fs');
const http = require('http');
const path = require('path');

const CDP_PORT = 9392;
const TEST_URL = 'https://platform.caribcode.caribgroupofcompanies.ltd/evals';
const ARTIFACTS_DIR = path.join(__dirname, '..', '..', '.tempmediaStorage');

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
  const newTarget = await putJson(`/json/new?${encodeURIComponent(TEST_URL)}`);
  const client = await connectWebSocket(newTarget.webSocketDebuggerUrl);
  await client.send('Page.enable');
  await client.send('Runtime.enable');
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: 1440,
    height: 900,
    deviceScaleFactor: 1,
    mobile: false
  });

  await new Promise(r => setTimeout(r, 3500));

  // Run benchmark so terminal drawer is populated and open
  await client.evaluate(`document.getElementById('btnEvalsRunAll')?.click()`);
  await new Promise(r => setTimeout(r, 2500));

  // Scroll main container to terminal drawer and test cases
  await client.evaluate(`
    const main = document.querySelector('#view-platform') || document.querySelector('.main-content') || window;
    const drawer = document.getElementById('evalsTerminalDrawer');
    if (drawer) {
      drawer.scrollIntoView({ behavior: 'instant', block: 'start' });
    }
  `);
  await new Promise(r => setTimeout(r, 600));

  await client.captureScreenshot('evals_harness_terminal_stream.png');

  // Scroll to test cases
  await client.evaluate(`
    const cases = document.querySelector('.evals-cases-container');
    if (cases) {
      cases.scrollIntoView({ behavior: 'instant', block: 'start' });
    }
  `);
  await new Promise(r => setTimeout(r, 600));

  await client.captureScreenshot('evals_harness_test_cases.png');

  await client.send('Page.close');
  client.ws.close();
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
