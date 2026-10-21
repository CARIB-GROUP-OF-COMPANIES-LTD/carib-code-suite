import Cocoa
import WebKit

class AppDelegate: NSObject, NSApplicationDelegate, WKNavigationDelegate, WKUIDelegate, NSWindowDelegate {
    var window: NSWindow!
    var webView: WKWebView!
    var toolbarView: NSView!
    var statusItem: NSStatusItem!
    var urlField: NSTextField!
    var segmentedDestinations: NSSegmentedControl!
    let toolbarHeight: CGFloat = 52

    // Core URLs
    let urlJarvisHud = URL(string: "https://jarvis.caribgroupofcompanies.ltd")!
    let urlTestDashboard = URL(string: "https://test.carib.core.caribgroupofcompanies.ltd/dashboard.php")!
    let urlVtigerQuickAccess = URL(string: "https://test.carib.core.caribgroupofcompanies.ltd/sso.php?sso_token=quick_unlock&user=admin")!
    let urlWhatsAppGateway = URL(string: "http://localhost:8099")!
    let urlCaribResults = URL(string: "https://caribliveresults.info")!
    let urlTestCore = URL(string: "https://test.carib.core.caribgroupofcompanies.ltd/core_test/")!
    let urlCrmCockpit = URL(string: "https://jarvis.caribgroupofcompanies.ltd/crm_cockpit.php")!
    let urlUnifiMsp = URL(string: "https://carib.unifi.caribgroupofcompanies.ltd")!
    let urlAgentApproval = URL(string: "https://caribliveresults.info/agent_approval.html")!
    let urlBrahmaEvo = URL(string: "https://jarvis.caribgroupofcompanies.ltd/api.php?action=brahma_evo_benchmark")!
    let urlGlobalAiBenchmark = URL(string: "https://jarvis.caribgroupofcompanies.ltd/api.php?action=global_ai_benchmark")!

    func applicationDidFinishLaunching(_ notification: Notification) {
        // 1. Configure WebKit with Media Capture & Developer Tools
        let config = WKWebViewConfiguration()
        config.preferences.setValue(true, forKey: "developerExtrasEnabled")
        config.defaultWebpagePreferences.allowsContentJavaScript = true
        config.applicationNameForUserAgent = "JarvisCockpit/2.2 (Macintosh; Mac OS X 15; NativeWebKit)"

        // 2. Setup Responsive Screen & Window Dimensions
        let screenSize = NSScreen.main?.visibleFrame ?? NSRect(x: 0, y: 0, width: 1440, height: 900)
        let windowWidth: CGFloat = min(max(screenSize.width * 0.96, 1200), screenSize.width)
        let windowHeight: CGFloat = min(max(screenSize.height * 0.95, 780), screenSize.height)
        let windowRect = NSRect(
            x: (screenSize.width - windowWidth) / 2 + screenSize.origin.x,
            y: (screenSize.height - windowHeight) / 2 + screenSize.origin.y,
            width: windowWidth,
            height: windowHeight
        )

        window = NSWindow(
            contentRect: windowRect,
            styleMask: [.titled, .closable, .miniaturizable, .resizable, .fullSizeContentView],
            backing: .buffered,
            defer: false
        )
        window.title = "Jarvis OS 3.0 • Carib Group Sovereign Cockpit"
        window.titlebarAppearsTransparent = true
        window.titleVisibility = .hidden
        window.backgroundColor = NSColor(red: 0.03, green: 0.04, blue: 0.07, alpha: 1.0)
        window.isReleasedWhenClosed = false
        window.minSize = NSSize(width: 1024, height: 680)
        window.collectionBehavior = [.fullScreenPrimary]
        window.delegate = self

        // 3. Setup Native Cybernetic Toolbar
        toolbarView = NSView(frame: NSRect(x: 0, y: windowHeight - toolbarHeight, width: windowWidth, height: toolbarHeight))
        toolbarView.autoresizingMask = [.width, .minYMargin]
        toolbarView.wantsLayer = true
        toolbarView.layer?.backgroundColor = NSColor(red: 0.05, green: 0.07, blue: 0.12, alpha: 0.95).cgColor

        // Window control padding spacer
        let leftOffset: CGFloat = 84

        // Back / Forward / Refresh Buttons
        let navStack = NSStackView(frame: NSRect(x: leftOffset, y: 11, width: 100, height: 30))
        navStack.orientation = .horizontal
        navStack.spacing = 6

        let btnBack = createIconButton(symbol: "◀", action: #selector(goBack))
        let btnForward = createIconButton(symbol: "▶", action: #selector(goForward))
        let btnRefresh = createIconButton(symbol: "🔄", action: #selector(reloadWebView))

        navStack.addArrangedSubview(btnBack)
        navStack.addArrangedSubview(btnForward)
        navStack.addArrangedSubview(btnRefresh)
        toolbarView.addSubview(navStack)

        // Segmented Destination Switcher (8 Composable Fleet Destinations)
        segmentedDestinations = NSSegmentedControl(labels: [
            "⚡ Jarvis HUD",
            "🏛️ Central Matrix",
            "🚀 Vtiger Quick Access",
            "💬 WhatsApp QR",
            "🏃 Live Results",
            "🌐 UniFi MSP",
            "📊 CRM Cockpit",
            "🧬 Brahma Evo"
        ], trackingMode: .selectOne, target: self, action: #selector(destinationChanged(_:)))
        segmentedDestinations.frame = NSRect(x: leftOffset + 105, y: 11, width: 880, height: 30)
        segmentedDestinations.selectedSegment = 0
        segmentedDestinations.segmentStyle = .capsule
        toolbarView.addSubview(segmentedDestinations)

        // URL Status Pill
        urlField = NSTextField(frame: NSRect(x: leftOffset + 905, y: 13, width: max(windowWidth - (leftOffset + 925), 200), height: 26))
        urlField.autoresizingMask = [.width]
        urlField.isEditable = false
        urlField.isSelectable = true
        urlField.isBordered = false
        urlField.backgroundColor = NSColor(red: 0.08, green: 0.11, blue: 0.18, alpha: 0.8)
        urlField.textColor = NSColor(red: 0.22, green: 0.85, blue: 0.65, alpha: 1.0)
        urlField.font = NSFont.monospacedSystemFont(ofSize: 11.5, weight: .medium)
        urlField.alignment = .left
        urlField.wantsLayer = true
        urlField.layer?.cornerRadius = 6
        urlField.stringValue = "  https://jarvis.caribgroupofcompanies.ltd"
        toolbarView.addSubview(urlField)

        // 4. Setup WebKit View
        let webViewFrame = NSRect(x: 0, y: 0, width: windowWidth, height: windowHeight - toolbarHeight)
        webView = WKWebView(frame: webViewFrame, configuration: config)
        webView.autoresizingMask = [.width, .height]
        webView.navigationDelegate = self
        webView.uiDelegate = self
        webView.setValue(false, forKey: "drawsBackground")

        // Add subviews
        window.contentView?.addSubview(webView)
        window.contentView?.addSubview(toolbarView)

        // Load Initial URL (Jarvis Live HUD)
        loadDestination(url: urlJarvisHud)

        window.makeKeyAndOrderFront(nil)
        NSApp.activate(ignoringOtherApps: true)

        // 5. Setup Menu Bar & Shortcuts
        setupMainMenu()
        setupStatusItem()
    }

    func createIconButton(symbol: String, action: Selector) -> NSButton {
        let btn = NSButton(frame: NSRect(x: 0, y: 0, width: 28, height: 28))
        btn.title = symbol
        btn.bezelStyle = .texturedRounded
        btn.target = self
        btn.action = action
        btn.font = NSFont.systemFont(ofSize: 12, weight: .bold)
        btn.wantsLayer = true
        btn.layer?.backgroundColor = NSColor(red: 0.1, green: 0.13, blue: 0.2, alpha: 0.8).cgColor
        return btn
    }

    @objc func destinationChanged(_ sender: NSSegmentedControl) {
        switch sender.selectedSegment {
        case 0:
            loadDestination(url: urlJarvisHud)
        case 1:
            loadDestination(url: urlTestDashboard)
        case 2:
            loadDestination(url: urlVtigerQuickAccess)
        case 3:
            loadDestination(url: urlWhatsAppGateway)
        case 4:
            loadDestination(url: urlCaribResults)
        case 5:
            loadDestination(url: urlUnifiMsp)
        case 6:
            loadDestination(url: urlCrmCockpit)
        case 7:
            loadDestination(url: urlBrahmaEvo)
        default:
            break
        }
    }

    func loadDestination(url: URL) {
        urlField.stringValue = "  \(url.absoluteString)"
        let request = URLRequest(url: url, cachePolicy: .useProtocolCachePolicy, timeoutInterval: 30)
        webView.load(request)
    }

    func setupMainMenu() {
        let mainMenu = NSMenu()

        // App Menu
        let appMenuItem = NSMenuItem()
        mainMenu.addItem(appMenuItem)
        let appMenu = NSMenu()
        appMenu.addItem(NSMenuItem(title: "About Jarvis Cockpit", action: #selector(NSApplication.orderFrontStandardAboutPanel(_:)), keyEquivalent: ""))
        appMenu.addItem(NSMenuItem.separator())
        appMenu.addItem(NSMenuItem(title: "Quit Jarvis Cockpit", action: #selector(quitApp), keyEquivalent: "q"))
        appMenuItem.submenu = appMenu

        // Navigation Menu (Cmd+1..8)
        let navMenuItem = NSMenuItem()
        mainMenu.addItem(navMenuItem)
        let navMenu = NSMenu(title: "Navigate")
        navMenu.addItem(NSMenuItem(title: "Jarvis HUD", action: #selector(selectJarvisHud), keyEquivalent: "1"))
        navMenu.addItem(NSMenuItem(title: "Central Matrix & Treasury", action: #selector(selectTestDashboard), keyEquivalent: "2"))
        navMenu.addItem(NSMenuItem(title: "Vtiger Quick Access", action: #selector(selectVtigerQuickAccess), keyEquivalent: "3"))
        navMenu.addItem(NSMenuItem(title: "WhatsApp Companion QR", action: #selector(selectWhatsAppGateway), keyEquivalent: "4"))
        navMenu.addItem(NSMenuItem(title: "Carib Live Results", action: #selector(selectCaribResults), keyEquivalent: "5"))
        navMenu.addItem(NSMenuItem(title: "UniFi MSP Console", action: #selector(selectUnifiMsp), keyEquivalent: "6"))
        navMenu.addItem(NSMenuItem(title: "CRM Cockpit", action: #selector(selectCrmCockpit), keyEquivalent: "7"))
        navMenu.addItem(NSMenuItem(title: "Brahma Evo Protocol", action: #selector(selectBrahmaEvo), keyEquivalent: "8"))
        navMenu.addItem(NSMenuItem.separator())
        navMenu.addItem(NSMenuItem(title: "🎙️ Talk to Jarvis", action: #selector(triggerVoiceCommand), keyEquivalent: "j"))
        navMenu.addItem(NSMenuItem.separator())
        navMenu.addItem(NSMenuItem(title: "Back", action: #selector(goBack), keyEquivalent: "["))
        navMenu.addItem(NSMenuItem(title: "Forward", action: #selector(goForward), keyEquivalent: "]"))
        navMenu.addItem(NSMenuItem(title: "Reload", action: #selector(reloadWebView), keyEquivalent: "r"))
        navMenuItem.submenu = navMenu

        NSApp.mainMenu = mainMenu
    }

    func setupStatusItem() {
        statusItem = NSStatusBar.system.statusItem(withLength: NSStatusItem.variableLength)
        if let button = statusItem.button {
            button.title = "⚡ Jarvis"
            button.action = #selector(toggleWindow)
            button.target = self
        }

        let menu = NSMenu()
        menu.addItem(NSMenuItem(title: "⚡ Jarvis HUD (Cmd+1)", action: #selector(selectJarvisHud), keyEquivalent: "1"))
        menu.addItem(NSMenuItem(title: "🏛️ Central Matrix (Cmd+2)", action: #selector(selectTestDashboard), keyEquivalent: "2"))
        menu.addItem(NSMenuItem(title: "🚀 Vtiger Quick Access (Cmd+3)", action: #selector(selectVtigerQuickAccess), keyEquivalent: "3"))
        menu.addItem(NSMenuItem(title: "💬 WhatsApp QR (Cmd+4)", action: #selector(selectWhatsAppGateway), keyEquivalent: "4"))
        menu.addItem(NSMenuItem(title: "🏃 Live Results (Cmd+5)", action: #selector(selectCaribResults), keyEquivalent: "5"))
        menu.addItem(NSMenuItem(title: "🌐 UniFi MSP (Cmd+6)", action: #selector(selectUnifiMsp), keyEquivalent: "6"))
        menu.addItem(NSMenuItem(title: "📊 CRM Cockpit (Cmd+7)", action: #selector(selectCrmCockpit), keyEquivalent: "7"))
        menu.addItem(NSMenuItem(title: "🧬 Brahma Evo Core (Cmd+8)", action: #selector(selectBrahmaEvo), keyEquivalent: "8"))
        menu.addItem(NSMenuItem.separator())
        menu.addItem(NSMenuItem(title: "Show / Hide Cockpit", action: #selector(toggleWindow), keyEquivalent: "j"))
        menu.addItem(NSMenuItem(title: "Reload Active Page", action: #selector(reloadWebView), keyEquivalent: "r"))
        menu.addItem(NSMenuItem.separator())
        menu.addItem(NSMenuItem(title: "Quit Jarvis Cockpit", action: #selector(quitApp), keyEquivalent: "q"))
        statusItem.menu = menu
    }

    @objc func selectJarvisHud() {
        segmentedDestinations.selectedSegment = 0
        loadDestination(url: urlJarvisHud)
    }

    @objc func selectTestDashboard() {
        segmentedDestinations.selectedSegment = 1
        loadDestination(url: urlTestDashboard)
    }

    @objc func selectVtigerQuickAccess() {
        segmentedDestinations.selectedSegment = 2
        loadDestination(url: urlVtigerQuickAccess)
    }

    @objc func selectWhatsAppGateway() {
        segmentedDestinations.selectedSegment = 3
        loadDestination(url: urlWhatsAppGateway)
    }

    @objc func selectCaribResults() {
        segmentedDestinations.selectedSegment = 4
        loadDestination(url: urlCaribResults)
    }

    @objc func selectUnifiMsp() {
        segmentedDestinations.selectedSegment = 5
        loadDestination(url: urlUnifiMsp)
    }

    @objc func selectCrmCockpit() {
        segmentedDestinations.selectedSegment = 6
        loadDestination(url: urlCrmCockpit)
    }

    @objc func selectBrahmaEvo() {
        segmentedDestinations.selectedSegment = 7
        loadDestination(url: urlBrahmaEvo)
    }

    @objc func selectGlobalAiBenchmark() {
        loadDestination(url: urlGlobalAiBenchmark)
    }

    @objc func goBack() {
        if webView.canGoBack { webView.goBack() }
    }

    @objc func goForward() {
        if webView.canGoForward { webView.goForward() }
    }

    @objc func toggleWindow() {
        if window.isVisible {
            window.orderOut(nil)
        } else {
            window.makeKeyAndOrderFront(nil)
        }
    }

    @objc func reloadWebView() {
        webView.reload()
    }

    @objc func triggerVoiceCommand() {
        webView.evaluateJavaScript("if (typeof window.toggleVoiceCommand === 'function') { window.toggleVoiceCommand(); } else { const b = document.getElementById('talkMicBtn') || document.querySelector('.voice-tap-btn'); if (b) b.click(); }", completionHandler: nil)
    }

    @objc func quitApp() {
        NSApp.terminate(nil)
    }

    // WebKit Delegates
    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        if let current = webView.url?.absoluteString {
            urlField.stringValue = "  \(current)"
            if current.contains("agent_approval") {
                segmentedDestinations.selectedSegment = 6
            } else if current.contains("caribliveresults.info") {
                segmentedDestinations.selectedSegment = 2
            } else if current.contains("test.carib.core") {
                segmentedDestinations.selectedSegment = 3
            } else if current.contains("crm_cockpit") {
                segmentedDestinations.selectedSegment = 4
            } else if current.contains("carib.unifi") {
                segmentedDestinations.selectedSegment = 5
            } else if current.contains("view=ai-suite") {
                segmentedDestinations.selectedSegment = 1
            } else if current.contains("jarvis.caribgroupofcompanies.ltd") || current.contains("jarvis.caribgroupofcompanies.ltd") {
                segmentedDestinations.selectedSegment = 0
            }
        }
    }

    func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
        print("Navigation error: \(error.localizedDescription)")
    }

    // Gracefully handle WebKit process terminations (prevents blank/frozen screen)
    func webViewWebContentProcessDidTerminate(_ webView: WKWebView) {
        print("[JarvisCockpit] WebKit process terminated, reloading smoothly...")
        webView.reload()
    }

    // Dynamic resize handler to ensure 100% fluid view fill
    func windowDidResize(_ notification: Notification) {
        guard let cv = window.contentView, let tb = toolbarView, let wv = webView else { return }
        let b = cv.bounds
        tb.frame = NSRect(x: 0, y: b.height - toolbarHeight, width: b.width, height: toolbarHeight)
        wv.frame = NSRect(x: 0, y: 0, width: b.width, height: b.height - toolbarHeight)
    }

    // Automatic Media Capture Grant (Enables Native Speech & Microphone for Jarvis Voice)
    @available(macOS 12.0, *)
    func webView(_ webView: WKWebView, requestMediaCapturePermissionFor origin: WKSecurityOrigin, initiatedByFrame frame: WKFrameInfo, type: WKMediaCaptureType, decisionHandler: @escaping (WKPermissionDecision) -> Void) {
        decisionHandler(.grant)
    }
}

let app = NSApplication.shared
let delegate = AppDelegate()
app.delegate = delegate
app.setActivationPolicy(.regular)
app.run()
