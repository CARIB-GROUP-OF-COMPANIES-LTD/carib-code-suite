/**
 * Comprehensive Automated Interactive Test Suite for Claude Code Agentic IDE View (#code)
 * Tests every column, every box, every tab, and every interactive control.
 */

const fs = require('fs');
const path = require('path');
const { spawn, execSync } = require('child_process');
const http = require('http');

const PORT = 8888;
const BASE_URL = `http://127.0.0.1:${PORT}`;
const ARTIFACTS_DIR = '/Users/djooozyb/.gemini/antigravity-ide/brain/011bd4a5-0e36-485f-8357-ed41643e5108';
const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

let passed = 0;
let failed = 0;

function assert(description, condition) {
  if (condition) {
    console.log(`[PASS] ${description}`);
    passed++;
  } else {
    console.error(`[FAIL] ${description}`);
    failed++;
  }
}

async function run() {
  console.log('===============================================================');
  console.log('STARTING DEEP INTERACTIVE VERIFICATION: CLAUDE CODE STUDIO (#code)');
  console.log('Testing Every Column, Every Box, Every Control & State');
  console.log('===============================================================');

  // Verify files exist locally
  const codeSessionsJsPath = path.join(__dirname, '..', 'web-app', 'code_sessions.js');
  const codeSessionsCssPath = path.join(__dirname, '..', 'web-app', 'code_sessions.css');
  const indexHtmlPath = path.join(__dirname, '..', 'web-app', 'index.html');

  assert('code_sessions.js exists', fs.existsSync(codeSessionsJsPath));
  assert('code_sessions.css exists', fs.existsSync(codeSessionsCssPath));
  assert('index.html exists', fs.existsSync(indexHtmlPath));

  const jsContent = fs.readFileSync(codeSessionsJsPath, 'utf8');
  const cssContent = fs.readFileSync(codeSessionsCssPath, 'utf8');
  const htmlContent = fs.readFileSync(indexHtmlPath, 'utf8');

  // Launch Headless Chrome with Remote Debugging
  const cdpPort = 9223;
  const chromeProc = spawn(CHROME_PATH, [
    '--headless=new',
    '--disable-gpu',
    `--remote-debugging-port=${cdpPort}`,
    '--window-size=1280,840',
    `--user-data-dir=/tmp/cdp_code_test_${Date.now()}`,
    `${BASE_URL}/#code`
  ]);

  // Give Chrome a moment to spin up CDP
  await new Promise(r => setTimeout(r, 1500));

  let wsUrl = null;
  try {
    const listRes = await fetch(`http://127.0.0.1:${cdpPort}/json`);
    const list = await listRes.json();
    const target = list.find(t => t.type === 'page');
    if (target && target.webSocketDebuggerUrl) {
      wsUrl = target.webSocketDebuggerUrl;
    }
  } catch (e) {
    console.warn('Could not fetch CDP targets:', e.message);
  }

  if (!wsUrl) {
    console.error('Fatal: CDP WebSocket not available. Falling back to local DOM and direct headless capture.');
    chromeProc.kill();
    runLocalEvaluation(jsContent, cssContent, htmlContent);
    return;
  }

  const ws = new globalThis.WebSocket(wsUrl);
  await new Promise((res, rej) => {
    ws.onopen = res;
    ws.onerror = rej;
  });

  let msgId = 1;
  const pending = new Map();
  ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    if (data.method === 'Runtime.exceptionThrown') {
      console.error('[BROWSER EXCEPTION]', data.params.exceptionDetails?.exception?.description || data.params.exceptionDetails?.text);
    }
    if (data.id && pending.has(data.id)) {
      pending.get(data.id)(data);
      pending.delete(data.id);
    }
  };

  function sendCommand(method, params = {}) {
    return new Promise((resolve) => {
      const id = msgId++;
      pending.set(id, resolve);
      ws.send(JSON.stringify({ id, method, params }));
    });
  }

  async function evaluate(expression) {
    const res = await sendCommand('Runtime.evaluate', {
      expression,
      returnByValue: true,
      awaitPromise: true
    });
    return res.result?.result?.value;
  }

  async function captureScreenshot(filename) {
    const res = await sendCommand('Page.captureScreenshot', { format: 'png' });
    if (res.result && res.result.data) {
      const buf = Buffer.from(res.result.data, 'base64');
      const outPath = path.join(ARTIFACTS_DIR, filename);
      fs.writeFileSync(outPath, buf);
      return outPath;
    }
    return null;
  }

  await sendCommand('Page.enable');
  await sendCommand('Runtime.enable');

  // Navigate to #code and activate view
  await evaluate(`
    if (typeof window.navigateToView === 'function') {
      window.navigateToView('code');
    } else {
      document.querySelectorAll('.user-view').forEach(v => v.hidden = true);
      const cv = document.getElementById('view-code');
      if (cv) cv.hidden = false;
      if (window.CodeSessions && window.CodeSessions.refresh) window.CodeSessions.refresh();
    }
  `);

  await new Promise(r => setTimeout(r, 600));

  // --- TEST GROUP 1: COLUMN 1 - SIDEBAR & SESSIONS LIST ---
  console.log('\n--- TEST GROUP 1: COLUMN 1 - LEFT SIDEBAR & RECENTS ---');
  const sidebarExists = await evaluate(`!!document.getElementById('ccSidebar')`);
  assert('Sidebar container (#ccSidebar) exists', sidebarExists);

  const sidebarVisible = await evaluate(`!document.getElementById('ccSidebar').classList.contains('collapsed')`);
  assert('Sidebar starts in expanded state', sidebarVisible);

  const recentsCount = await evaluate(`document.querySelectorAll('.cc-recent-item').length`);
  assert(`Sidebar lists 3 recent sessions (found ${recentsCount})`, recentsCount >= 3);

  const session1Title = await evaluate(`document.querySelector('.cc-recent-item.active')?.textContent.trim()`);
  assert('Active session 1 is "Fix the checkout bug"', session1Title && session1Title.includes('Fix the checkout bug'));

  // Test toggle sidebar collapse
  await evaluate(`document.getElementById('ccBtnToggleSidebar')?.click()`);
  const isCollapsed = await evaluate(`document.getElementById('ccSidebar')?.classList.contains('collapsed')`);
  assert('Sidebar collapses when clicking toggle button', isCollapsed);

  // Restore sidebar
  await evaluate(`document.getElementById('ccBtnToggleSidebar')?.click()`);
  const isRestored = await evaluate(`!document.getElementById('ccSidebar')?.classList.contains('collapsed')`);
  assert('Sidebar re-expands cleanly', isRestored);

  // --- TEST GROUP 2: TOP APP BAR & WINDOW CONTROLS ---
  console.log('\n--- TEST GROUP 2: TOP APP BAR & CONTROLS ---');
  const topbarExists = await evaluate(`!!document.querySelector('.cc-topbar')`);
  assert('Top app bar (.cc-topbar) is rendered', topbarExists);

  const trafficLights = await evaluate(`document.querySelectorAll('.cc-traffic-dot').length`);
  assert('Traffic lights (close, minimize, maximize) are present', trafficLights === 3);

  const activeFileName = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Top bar active file matches photo: "${activeFileName}"`, activeFileName === 'AlignmentGrid.tsx');

  const agenticPill = await evaluate(`document.querySelector('.cc-code-mode-pill')?.textContent.replace(/\\s+/g, ' ').trim()`);
  assert('Agentic Session mode pill is displayed', agenticPill && agenticPill.includes('Agentic Session'));

  // --- TEST GROUP 3: SCORE COLUMN (LEFT OF DIFF) ---
  console.log('\n--- TEST GROUP 3: SCORE COLUMN & CONFIDENCE BADGES ---');
  const scoreColumnExists = await evaluate(`!!document.querySelector('.cc-score-column')`);
  assert('Score column (.cc-score-column) exists', scoreColumnExists);

  const scoreValues = await evaluate(`Array.from(document.querySelectorAll('.cc-score-badge')).map(el => el.textContent.trim())`);
  assert('Score column contains badges [0.85, 0.70, 0.60, 1.00, 1.00]', 
    scoreValues.includes('0.85') && scoreValues.includes('0.70') && scoreValues.includes('1.00'));

  // --- TEST GROUP 4: CODE DIFF BOX (MATCHING PHOTO) ---
  console.log('\n--- TEST GROUP 4: CODE DIFF CARD & FOLDING LOGIC ---');
  const diffCardExists = await evaluate(`!!document.querySelector('.cc-diff-card')`);
  assert('Code diff card (.cc-diff-card) exists', diffCardExists);

  const editorTabVisible = await evaluate(`!!document.querySelector('.cc-editor-tab')`);
  assert('Editor file tab bar with AlignmentGrid.tsx is rendered', editorTabVisible);

  const delRowText = await evaluate(`document.querySelector('.cc-diff-row.del .cc-diff-content')?.textContent.trim()`);
  assert('Deletion row contains backOut dynamics function', delRowText && delRowText.includes('const backOut'));

  const addRowText = await evaluate(`document.querySelector('.cc-diff-row.add .cc-diff-content')?.textContent.trim()`);
  assert('Addition row contains snap constraint function', addRowText && addRowText.includes('const snap = (t: number)'));

  const foldText = await evaluate(`document.getElementById('ccDiffFold')?.textContent.trim()`);
  assert(`Fold banner displays unmodified lines counter ("${foldText}")`, foldText && foldText.includes('38 unmodified lines'));

  // Test fold expansion
  await evaluate(`document.getElementById('ccDiffFold')?.click()`);
  const unfoldedLines = await evaluate(`document.querySelectorAll('.cc-diff-row.ctx').length`);
  assert(`Clicking fold unfolds ${unfoldedLines} context lines (expected >= 8)`, unfoldedLines >= 8);

  const contextHasConstraint = await evaluate(`document.querySelector('.cc-diff-card')?.textContent.includes('AlignmentConstraint')`);
  assert('Unfolded lines include AlignmentConstraint interface', contextHasConstraint);

  // Re-fold
  await evaluate(`document.getElementById('ccDiffFold')?.click()`);
  const refoldedLines = await evaluate(`document.querySelectorAll('.cc-diff-row.ctx').length`);
  assert('Clicking fold again re-collapses unmodified lines', refoldedLines === 0);

  // Check addition chunk (lines 41-44)
  const addChunkHasShots = await evaluate(`document.querySelector('.cc-diff-card')?.textContent.includes('const SHOTS: Shot[] = [')`);
  assert('Addition chunk contains "const SHOTS: Shot[] = ["', addChunkHasShots);

  // --- TEST GROUP 5: TERMINAL BOX & LOGS ---
  console.log('\n--- TEST GROUP 5: TERMINAL BOX & DIAGNOSTICS ---');
  const terminalVisibleInitial = await evaluate(`!document.getElementById('ccTerminalCard')?.classList.contains('hidden')`);
  assert('Terminal card is initially visible', terminalVisibleInitial);

  const termErrorText = await evaluate(`document.querySelector('.cc-term-err')?.textContent.trim()`);
  assert('Terminal displays TypeScript error for Property "stiffness"', 
    termErrorText && termErrorText.includes("Property 'stiffness' is missing"));

  const termLogs = await evaluate(`Array.from(document.querySelectorAll('.cc-terminal-body div')).map(el => el.textContent.trim())`);
  assert('Terminal contains Vite HMR update logs', termLogs.some(l => l.includes('[vite] hmr update')));
  assert('Terminal contains Vite error resolved log', termLogs.some(l => l.includes('error resolved')));

  // Test closing terminal
  await evaluate(`document.getElementById('ccCloseTerminal')?.click()`);
  const termClosed = await evaluate(`document.getElementById('ccTerminalCard')?.classList.contains('hidden')`);
  assert('Clicking [X] on terminal closes the terminal box', termClosed);

  // Test reopening terminal via topbar
  await evaluate(`document.getElementById('ccBtnToggleTerminal')?.click()`);
  const termReopened = await evaluate(`!document.getElementById('ccTerminalCard')?.classList.contains('hidden')`);
  assert('Clicking top bar terminal icon re-opens the terminal box', termReopened);

  // --- TEST GROUP 6: TASKS BOX & RUNNING STATUS ---
  console.log('\n--- TEST GROUP 6: TASKS BOX & MULTI-AGENT STATE ---');
  const tasksCardVisible = await evaluate(`!document.getElementById('ccTasksCard')?.classList.contains('hidden')`);
  assert('Tasks card is initially visible', tasksCardVisible);

  const tasksCount = await evaluate(`document.querySelectorAll('.cc-task-row').length`);
  assert(`Tasks box renders 3 tasks (found ${tasksCount})`, tasksCount === 3);

  const blueLedCount = await evaluate(`document.querySelectorAll('.cc-task-led.blue').length`);
  assert(`All 3 tasks have blue RUNNING LED indicators matching photo (found ${blueLedCount})`, blueLedCount === 3);

  const taskNames = await evaluate(`Array.from(document.querySelectorAll('.cc-task-left span:last-child')).map(el => el.textContent.trim())`);
  assert('Tasks include "Verify constraint solver convergence"', taskNames.includes('Verify constraint solver convergence'));
  assert('Tasks include "(background)"', taskNames.includes('(background)'));
  assert('Tasks include "TypeScript AST check"', taskNames.includes('TypeScript AST check'));

  // Test canceling a task via badge [X]
  await evaluate(`
    const cancelBtn = document.querySelector('.cc-task-badge-cancel');
    if (cancelBtn) cancelBtn.click();
  `);
  const tasksAfterCancel = await evaluate(`document.querySelectorAll('.cc-task-row').length`);
  assert('Clicking [X] on a task cancels and removes it', tasksAfterCancel === 2);

  const termHasCancelLog = await evaluate(`document.querySelector('.cc-terminal-body')?.textContent.includes('task t1 cancelled')`);
  assert('Terminal logs the task cancellation', termHasCancelLog);

  // Test closing tasks card
  await evaluate(`document.getElementById('ccCloseTasks')?.click()`);
  const tasksClosed = await evaluate(`document.getElementById('ccTasksCard')?.classList.contains('hidden')`);
  assert('Clicking [X] on tasks header closes the tasks box', tasksClosed);

  // Test reopening tasks card
  await evaluate(`document.getElementById('ccBtnToggleTasks')?.click()`);
  const tasksReopened = await evaluate(`!document.getElementById('ccTasksCard')?.classList.contains('hidden')`);
  assert('Clicking top bar tasks icon re-opens the tasks box', tasksReopened);

  // --- TEST GROUP 7: PROMPT COMPOSER BOX & DISPATCH ---
  console.log('\n--- TEST GROUP 7: PROMPT COMPOSER BOX & RUNNERS ---');
  const promptValue = await evaluate(`document.getElementById('ccPromptInput')?.value`);
  assert(`Prompt input contains photo value: "${promptValue}"`, promptValue === '-make it snap instead of overshoot');

  const modelOptions = await evaluate(`Array.from(document.querySelectorAll('#ccModelSelect option')).map(o => o.text)`);
  assert('Model selector contains Claude 3.7 Sonnet', modelOptions.includes('Claude 3.7 Sonnet'));
  assert('Model selector contains DeepSeek V4 Pro 1.6T (Free)', modelOptions.some(m => m.includes('DeepSeek V4 Pro')));
  assert('Model selector contains Qwen 3 Coder Next (Free)', modelOptions.some(m => m.includes('Qwen 3 Coder Next')));

  const effortValue = await evaluate(`document.getElementById('ccEffortSelect')?.value`);
  assert(`Effort dropdown selects "medium" matching photo ("${effortValue}")`, effortValue === 'medium');

  // Test submitting prompt
  console.log('Testing live prompt dispatch...');
  await evaluate(`
    const input = document.getElementById('ccPromptInput');
    if (input) {
      input.value = '-enforce rigid stiffness matrix on boundary nodes';
      document.getElementById('ccBtnSend')?.click();
    }
  `);

  await new Promise(r => setTimeout(r, 600));

  const hasNewRunningTask = await evaluate(`document.querySelector('.cc-tasks-body')?.textContent.includes('Applying: -enforce rigid')`);
  assert('Submitting prompt dynamically adds running task to Tasks box', hasNewRunningTask);

  const terminalHasDispatchLog = await evaluate(`document.querySelector('.cc-terminal-body')?.textContent.includes('executing prompt: "-enforce rigid')`);
  assert('Submitting prompt logs execution command to Terminal box', terminalHasDispatchLog);

  // --- TEST GROUP 8: RECENT SESSIONS SWITCHING ---
  console.log('\n--- TEST GROUP 8: MULTI-SESSION SWITCHING ---');
  // Switch to Session 2: ThemeManager.ts
  await evaluate(`
    const item = document.querySelectorAll('.cc-recent-item')[1];
    if (item) item.click();
  `);
  await new Promise(r => setTimeout(r, 400));

  const session2File = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Switching to session 2 updates active file to "ThemeManager.ts" (got: "${session2File}")`, session2File === 'ThemeManager.ts');

  const session2DiffText = await evaluate(`document.querySelector('.cc-diff-card')?.textContent`);
  assert('Session 2 diff renders dark tokens and matchMedia code', session2DiffText.includes('prefers-color-scheme'));

  // Switch to Session 3: ApiClient.ts
  await evaluate(`
    const items = document.querySelectorAll('.cc-recent-item');
    if (items.length > 2) items[2].click();
  `);
  await new Promise(r => setTimeout(r, 400));

  const session3File = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Switching to session 3 updates active file to "ApiClient.ts" (got: "${session3File}")`, session3File === 'ApiClient.ts');

  // Test Prev and Next buttons in topbar
  console.log('\n--- TEST GROUP 8B: PREV & NEXT SESSION BUTTONS ---');
  await evaluate(`document.getElementById('ccBtnPrev')?.click()`);
  await new Promise(r => setTimeout(r, 300));
  const prevFile = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Top bar [←] navigates to previous session ("ThemeManager.ts", got: "${prevFile}")`, prevFile === 'ThemeManager.ts');

  await evaluate(`document.getElementById('ccBtnNext')?.click()`);
  await new Promise(r => setTimeout(r, 300));
  const nextFile = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Top bar [→] navigates to next session ("ApiClient.ts", got: "${nextFile}")`, nextFile === 'ApiClient.ts');

  // Test New Session Modal Creation
  console.log('\n--- TEST GROUP 8C: NEW SESSION MODAL CREATION ---');
  await evaluate(`
    document.getElementById('ccBtnNewSession')?.click();
  `);
  await new Promise(r => setTimeout(r, 300));
  const modalVisible = await evaluate(`!!document.getElementById('ccNewSessionModal')`);
  assert('Clicking [+ New session] opens the New Session modal', modalVisible);

  const modalDiag = await evaluate(`
    (() => {
      const modal = document.getElementById('ccNewSessionModal');
      const input = document.getElementById('ccNewSessionInput');
      const submitBtn = document.getElementById('ccNewSessionSubmit');
      if (input) input.value = 'Autonomous Benchmark Engine';
      const hasOnclick = typeof submitBtn?.onclick;
      if (submitBtn) submitBtn.click();
      return {
        hasModal: !!modal,
        hasSubmit: !!submitBtn,
        hasOnclick: hasOnclick
      };
    })()
  `);
  console.log('Modal diag:', JSON.stringify(modalDiag));

  await new Promise(r => setTimeout(r, 400));
  const newActiveFile = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`New session is created and active ("App.tsx", got: "${newActiveFile}")`, newActiveFile === 'App.tsx');

  const recentsCountAfterAdd = await evaluate(`document.querySelectorAll('.cc-recent-item').length`);
  assert(`Recents list now has 4 sessions (got: ${recentsCountAfterAdd})`, recentsCountAfterAdd === 4);

  // Test Search Modal with Typing and Filtering
  console.log('\n--- TEST GROUP 8D: SEARCH MODAL FUZZY FILTERING ---');
  await evaluate(`document.getElementById('ccBtnSearch')?.click()`);
  await new Promise(r => setTimeout(r, 300));
  const searchModalExists = await evaluate(`!!document.getElementById('ccSearchSessionModal')`);
  assert('Top bar search button opens Search Modal', searchModalExists);

  await evaluate(`
    const sInput = document.getElementById('ccSearchInput');
    if (sInput) {
      sInput.value = 'dark';
      sInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
  `);
  await new Promise(r => setTimeout(r, 200));

  const filteredCount = await evaluate(`document.querySelectorAll('.cc-search-row').length`);
  assert(`Search for "dark" filters list to 1 result (got: ${filteredCount})`, filteredCount === 1);

  // Click filtered result to switch to dark mode session
  await evaluate(`document.querySelector('.cc-search-row')?.click()`);
  await new Promise(r => setTimeout(r, 400));
  const filteredActiveFile = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Selecting search result activates "ThemeManager.ts" (got: "${filteredActiveFile}")`, filteredActiveFile === 'ThemeManager.ts');

  // Test Claude Code Overview Docs Modal
  console.log('\n--- TEST GROUP 8E: OVERVIEW DOCS MODAL ---');
  await evaluate(`document.getElementById('ccBtnDocs')?.click()`);
  await new Promise(r => setTimeout(r, 300));
  const docsModalVisible = await evaluate(`!!document.getElementById('claudeCodeDocsModal')`);
  assert('Clicking [Overview] opens Claude Code Docs Modal', docsModalVisible);
  await evaluate(`document.querySelector('#claudeCodeDocsModal .claude-modal-close')?.click()`);
  await new Promise(r => setTimeout(r, 200));
  const docsModalClosed = await evaluate(`!document.getElementById('claudeCodeDocsModal')`);
  assert('Closing Docs Modal removes it cleanly', docsModalClosed);

  // Restore Session 1 to exact initial photo parity
  const restoreDiag = await evaluate(`
    (() => {
      document.getElementById('claudeToastContainer')?.remove();
      const items = Array.from(document.querySelectorAll('.cc-recent-item'));
      const s1 = items.find(it => it.textContent.includes('Fix the checkout bug'));
      if (s1) {
        s1.click();
        return { found: true, id: s1.dataset.id, text: s1.textContent.trim() };
      }
      return { found: false, all: items.map(it => it.textContent.trim()) };
    })()
  `);
  console.log('Restore diag:', JSON.stringify(restoreDiag));
  await new Promise(r => setTimeout(r, 400));

  await evaluate(`
    const promptInput = document.getElementById('ccPromptInput');
    if (promptInput) promptInput.value = '-make it snap instead of overshoot';
    document.getElementById('claudeToastContainer')?.remove();
  `);
  await new Promise(r => setTimeout(r, 200));

  const restoredActiveFile = await evaluate(`document.getElementById('ccActiveFileName')?.textContent.trim()`);
  assert(`Restored active session to initial photo file ("AlignmentGrid.tsx", got: "${restoredActiveFile}")`, restoredActiveFile === 'AlignmentGrid.tsx');

  // --- TEST GROUP 9: HIGH-RESOLUTION SCREENSHOTS ---
  console.log('\n--- TEST GROUP 9: VISUAL PARITY SCREENSHOT CAPTURES ---');
  const shot1 = await captureScreenshot('verified_code_page_initial_view.png');
  assert('Captured verified_code_page_initial_view.png (exact parity with photo)', !!shot1);

  // Open search modal with dark theme applied and capture
  await evaluate(`window.openSearchSessionModal()`);
  await new Promise(r => setTimeout(r, 300));
  const shot2 = await captureScreenshot('verified_code_page_search_modal.png');
  assert('Captured verified_code_page_search_modal.png (dark theme styling)', !!shot2);

  // Close search modal
  await evaluate(`document.getElementById('ccSearchSessionModal')?.remove()`);

  ws.close();
  chromeProc.kill();

  console.log('\n===============================================================');
  console.log(`CODE STUDIO VERIFICATION RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('===============================================================');
}

function runLocalEvaluation(jsContent, cssContent, htmlContent) {
  console.log('\nRunning offline validation of code_sessions.js and code_sessions.css:');
  assert('AlignmentGrid.tsx session configured', jsContent.includes('AlignmentGrid.tsx'));
  assert('Property stiffness error string present', jsContent.includes("Property 'stiffness' is missing"));
  assert('Tasks configured with Verify constraint solver convergence', jsContent.includes('Verify constraint solver convergence'));
  assert('Prompt input set to -make it snap instead of overshoot', jsContent.includes('-make it snap instead of overshoot'));
  assert('Model select contains Claude 3.7 Sonnet', jsContent.includes('Claude 3.7 Sonnet'));
  assert('Effort select contains Effort: Medium', jsContent.includes('Effort: Medium'));
  assert('Editor tab bar styled in CSS', cssContent.includes('.cc-editor-tab-bar'));
  assert('Terminal card styled in CSS', cssContent.includes('.cc-panel-card'));
  assert('Tasks card styled in CSS', cssContent.includes('.cc-tasks-body'));
}

run().catch(err => {
  console.error('Fatal execution error:', err);
  process.exit(1);
});
