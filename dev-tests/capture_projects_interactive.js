const http = require('http');
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const WebSocket = globalThis.WebSocket;

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const DEBUG_PORT = 9222;
const ARTIFACTS_DIR = '/Users/djooozyb/.gemini/antigravity-ide/brain/011bd4a5-0e36-485f-8357-ed41643e5108';

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function getJson(url) {
  return new Promise((resolve, reject) => {
    http.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try { resolve(JSON.parse(data)); } catch (e) { reject(e); }
      });
    }).on('error', reject);
  });
}

async function runInteractiveCapture() {
  console.log('Launching headless Chrome with remote debugging on port ' + DEBUG_PORT);
  const chromeProc = spawn(CHROME_PATH, [
    '--headless',
    '--disable-gpu',
    `--remote-debugging-port=${DEBUG_PORT}`,
    '--window-size=1280,850',
    'about:blank'
  ]);

  chromeProc.on('error', (err) => {
    console.error('Failed to spawn Chrome:', err);
  });

  await sleep(1500);

  try {
    const list = await getJson(`http://127.0.0.1:${DEBUG_PORT}/json`);
    const pageTarget = list.find(t => t.type === 'page') || list[0];
    if (!pageTarget) throw new Error('No page target found in Chrome');

    console.log('Connecting CDP WebSocket to:', pageTarget.webSocketDebuggerUrl);
    const ws = new WebSocket(pageTarget.webSocketDebuggerUrl);

    await new Promise((resolve, reject) => {
      ws.addEventListener('open', resolve, { once: true });
      ws.addEventListener('error', reject, { once: true });
    });

    let msgId = 1;
    function sendCommand(method, params = {}) {
      return new Promise((resolve, reject) => {
        const id = msgId++;
        const handler = (evt) => {
          try {
            const resp = JSON.parse(evt.data);
            if (resp.id === id) {
              ws.removeEventListener('message', handler);
              if (resp.error) reject(resp.error);
              else resolve(resp.result);
            }
          } catch (e) {}
        };
        ws.addEventListener('message', handler);
        ws.send(JSON.stringify({ id, method, params }));
      });
    }

    await sendCommand('Page.enable');
    await sendCommand('Runtime.enable');

    console.log('Navigating to http://127.0.0.1:8888/#projects ...');
    await sendCommand('Page.navigate', { url: 'http://127.0.0.1:8888/#projects' });
    await sleep(2000);

    // Capture Projects Gallery view
    console.log('Capturing Projects Gallery...');
    const galleryShot = await sendCommand('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(ARTIFACTS_DIR, 'verify_projects_gallery_interactive.png'), Buffer.from(galleryShot.data, 'base64'));
    console.log('Saved verify_projects_gallery_interactive.png');

    // Click on the Carib Live Results Track Dashboard project card
    console.log('Clicking on Carib Live Results project card...');
    await sendCommand('Runtime.evaluate', {
      expression: `
        (async () => {
          const card = document.querySelector('.project-card');
          if (card) {
            card.click();
            return 'clicked';
          }
          if (window.openProjectWorkspace) {
            await window.openProjectWorkspace('proj_carib_live_results');
            return 'called openProjectWorkspace';
          }
          return 'not found';
        })()
      `,
      awaitPromise: true
    });
    await sleep(1500);

    // Capture Project Workspace
    console.log('Capturing Project Workspace...');
    const wsShot = await sendCommand('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(ARTIFACTS_DIR, 'verify_project_workspace_live.png'), Buffer.from(wsShot.data, 'base64'));
    console.log('Saved verify_project_workspace_live.png');

    // Open Project Configuration Modal
    console.log('Opening Project Configuration Modal...');
    await sendCommand('Runtime.evaluate', {
      expression: `
        if (window.openProjectConfigModal && window.activeProject) {
          window.openProjectConfigModal(window.activeProject);
        } else {
          const cfgBtn = document.querySelector('.project-workspace-header button');
          if (cfgBtn) cfgBtn.click();
        }
      `
    });
    await sleep(1000);

    // Capture Project Configuration Modal
    console.log('Capturing Project Configuration Modal...');
    const modalShot = await sendCommand('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(ARTIFACTS_DIR, 'verify_project_config_modal.png'), Buffer.from(modalShot.data, 'base64'));
    console.log('Saved verify_project_config_modal.png');

    // Close Config Modal and Open Add Doc Modal
    console.log('Opening Add Doc Modal...');
    await sendCommand('Runtime.evaluate', {
      expression: `
        if (window.closeProjectConfigModal) window.closeProjectConfigModal();
        if (window.openAddDocModal && window.activeProject) {
          window.openAddDocModal(window.activeProject.id);
        }
      `
    });
    await sleep(1000);

    // Capture Add Doc Modal
    console.log('Capturing Add Doc Modal...');
    const addDocShot = await sendCommand('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(path.join(ARTIFACTS_DIR, 'verify_project_add_doc_modal.png'), Buffer.from(addDocShot.data, 'base64'));
    console.log('Saved verify_project_add_doc_modal.png');

    ws.close();
  } catch (err) {
    console.error('Error during interactive capture:', err);
  } finally {
    try {
      chromeProc.kill('SIGKILL');
    } catch (_) {}
  }
}

runInteractiveCapture().then(() => {
  console.log('Interactive capture complete.');
  process.exit(0);
}).catch(e => {
  console.error(e);
  process.exit(1);
});
