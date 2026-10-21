<?php
/**
 * CARIB SOLUTIONS ARCHITECTURE - ENTERPRISE MODULE DOCUMENTATION & CONFIGURATION PORTAL
 * Proprietary Documentation & Configuration Hub for Carib Group of Companies
 * Test Environment: https://testcrm.caribliveagency.info/
 * Zero production touch - Strict Rule 5 compliance.
 */

// Define comprehensive knowledge repository for all Carib Solutions
$modulesData = [
    'ITS4YouMultiCompany' => [
        'name' => 'Carib MultiCompany Enterprise Suite',
        'code' => 'ITS4YouMultiCompany',
        'slug' => 'multicompany',
        'category' => 'Core Enterprise & Multi-Entity',
        'icon' => '🏢',
        'version' => '4.19',
        'settings_url' => 'index.php?module=ITS4YouMultiCompany&parent=Settings&view=List',
        'badge' => 'Enterprise Architecture',
        'summary' => 'Multi-entity corporate segregation and subsidiary administration within a single sovereign CRM instance. Enables managing distinct Caribbean legal entities, territorial branches, and operating divisions under unified governance.',
        'why_needed' => [
            'Do you manage multiple operating companies or regional subsidiaries across the Caribbean?',
            'Do you need isolated document numbering sequences (Invoices, Quotes, SO, PO) for each legal entity?',
            'Do you require distinct legal company details, registration numbers, tax IDs, and bank wire instructions per branch?',
            'Do you need different corporate logos and brand headers on customer-facing PDF contracts and billing receipts?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_multicompany4you' => 'Primary entity table storing subsidiary profiles, corporate addresses, registration numbers, tax IDs, and banking data.',
                'its4you_multicompany4you_cn' => 'Custom numbering sequence definitions mapped to each subsidiary company.',
                'its4you_multicompany4you_cn_modules' => 'Module associations linking numbering sequences to Invoices, Quotes, Sales Orders, and Purchase Orders.',
                'vtiger_its4youmulticompany_user_field' => 'User-to-company relational mapping defining default subsidiary ownership and user access scopes.'
            ],
            'integration_points' => [
                'vtiger_invoice / vtiger_quotes' => 'Dynamically injects subsidiary company ID into transaction records for segregated financial reporting.',
                'PDFMaker Integration' => 'Injects company-specific variables ($MULTICOMPANY_COMPANYNAME$, $MULTICOMPANY_LOGO$, $MULTICOMPANY_BANKNAME$) into PDF templates.',
                'User Profiles' => 'Restricts record visibility and entity selection based on user branch assignments.'
            ],
            'governance_notes' => 'Crucial Architecture Rule: MultiCompany supports direct User assignment but does NOT support "Assigned to - Groups". Records must be assigned to individual operational agents to preserve strict legal segregation.'
        ],
        'configuration_guide' => [
            'Phase 1: Subsidiary Profile Registration' => 'Navigate to Settings -> Carib Solutions Architecture -> MultiCompany. Click "Add Company". Enter the legal corporate name, official physical address, tax registration number, SWIFT/IBAN wire instructions, and upload the high-resolution subsidiary logo.',
            'Phase 2: Numbering Sequence Masking' => 'Under Numbering tab, establish distinct sequence masks per company (e.g. "CARIB-SKN-2026-####" for St. Kitts vs "CARIB-MIA-2026-####" for Florida operations) across Invoices, Quotes, and Purchase Orders.',
            'Phase 3: User Assignment & Scoping' => 'Assign operational users to their primary corporate entity. Toggle whether users have multi-entity visibility or are restricted to their local branch.',
            'Phase 4: PDF Template Binding' => 'In PDF Maker, update invoice and quote templates to utilize $MULTICOMPANY_* dynamic variables so printed documents automatically brand with the issuing subsidiary\'s logo and banking details.'
        ]
    ],

    'ITS4YouCreator' => [
        'name' => 'Carib Record Creator & Action Engine',
        'code' => 'ITS4YouCreator',
        'slug' => 'creator',
        'category' => 'Core Enterprise & Multi-Entity',
        'icon' => '⚡',
        'version' => '0.9',
        'settings_url' => 'index.php?module=ITS4YouCreator&parent=Settings&view=Index',
        'badge' => 'Rapid Productivity',
        'summary' => 'High-velocity modal creation engine for instant record authoring across CRM modules without page navigation or context switching. Drastically reduces turnaround time for sales, triage, and live support.',
        'why_needed' => [
            'Do sales reps lose their flow when navigating through 4 different screens to create a deal or quote?',
            'Do you need parent-to-child data pre-population (e.g., pulling client address, tax code, and account manager into new records)?',
            'Do you want unified quick-action buttons directly on Organization, Contact, and Project detail views?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_settings_field (Creator Entry)' => 'Registered in system settings table under Carib Solutions Architecture block.',
                'its4you_installer_version' => 'Tracks deployed schema version (v0.9 active).'
            ],
            'integration_points' => [
                'Vtiger DetailView Actions' => 'Injects custom action buttons into record headers and related list action bars.',
                'Modal Overlay Engine' => 'Renders lightweight Bootstrap modals carrying full validation schemas without reloading the underlying page DOM.'
            ],
            'governance_notes' => 'Ensures zero field mapping leakage between test and live environments by using system-standard vtlib field definitions.'
        ],
        'configuration_guide' => [
            'Phase 1: Target Module Activation' => 'Open Settings -> Carib Solutions Architecture -> Creator. Select source module (e.g. Accounts) and destination module (e.g. Opportunities).',
            'Phase 2: Field Inheritance Rules' => 'Map source fields to target fields (e.g. Account Name -> Related To, Billing Address -> Shipping Address).',
            'Phase 3: UI Layout & Permissions' => 'Design the modal dialog layout, choose mandatory fields, and set role-based visibility.'
        ]
    ],

    'ITS4YouKanbanView' => [
        'name' => 'Carib Kanban Visual Pipeline',
        'code' => 'ITS4YouKanbanView',
        'slug' => 'kanban',
        'category' => 'Core Enterprise & Multi-Entity',
        'icon' => '📊',
        'version' => '4.15',
        'settings_url' => 'index.php?module=ITS4YouKanbanView&parent=Settings&view=Settings',
        'badge' => 'Visual Pipeline',
        'summary' => 'Interactive cybernetic drag-and-drop Kanban visual board replacing static tabular list views. Provides instant pipeline visibility, revenue forecasting, and rapid status triage across sales stages and support queues.',
        'why_needed' => [
            'Need an agile board view for Opportunities, Project Tasks, and HelpDesk Tickets?',
            'Want reps to advance sales stages by simply dragging cards across columns?',
            'Need column aggregate calculations displaying total deal count and dollar revenue in each stage?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_kanban_view_settings' => 'Stores module configurations, column-to-picklist mappings, and visible card fields.',
                'vtiger_crmentity' => 'Real-time modified time and stage update upon card drop event.'
            ],
            'integration_points' => [
                'Workflow Triggers' => 'Dragging a card across columns updates the underlying picklist, immediately firing automated workflow alerts.',
                'ListView Toggle' => 'Adds a dedicated Kanban icon toggle to standard module list views.'
            ],
            'governance_notes' => 'Maintains full permission parity: users only see cards for records they have role-based read/write access to.'
        ],
        'configuration_guide' => [
            'Phase 1: Module Enablement' => 'Go to Settings -> Carib Solutions Architecture -> KanbanView. Enable Kanban for Opportunities, Leads, or ProjectMilestones.',
            'Phase 2: Stage Column Mapping' => 'Select the controlling picklist field (e.g., Sales Stage). Define column order and hide inactive stages.',
            'Phase 3: Card Field Customization' => 'Pick up to 5 vital fields to display on each card (e.g. Amount, Expected Close Date, Contact Name, User Avatar).'
        ]
    ],

    'ITS4YouProcessFlow' => [
        'name' => 'Carib Process Flow & State Machine Engine',
        'code' => 'ITS4YouProcessFlow',
        'slug' => 'processflow',
        'category' => 'Core Enterprise & Multi-Entity',
        'icon' => '🔄',
        'version' => '0.27',
        'settings_url' => 'index.php?module=ITS4YouProcessFlow&parent=Settings&view=Index',
        'badge' => 'BPM & Governance',
        'summary' => 'Strict Business Process Model (BPM) state transition enforcement. Dictates valid stage paths, blocks illegal skips, and enforces mandatory field completion before permitting status progression.',
        'why_needed' => [
            'Do sales reps skip crucial discovery or contract phases and jump prematurely to Closed Won?',
            'Need to enforce manager approvals before an Invoice or Quote moves from Draft to Approved?',
            'Want required fields to appear dynamically based on the specific stage transition being executed?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_processflow' => 'Stores defined process rules, source stages, allowed target stages, and permission masks.',
                'its4you_processflowrel' => 'Relational mapping linking specific module records to active process flow chains.'
            ],
            'integration_points' => [
                'EditView & DetailView Hooks' => 'Disables unapproved picklist options in UI dropdowns, restricting choices to valid next steps.',
                'API & Web Service Enforcement' => 'Rejects illegal stage transitions made via REST/SOAP APIs with clear validation errors.'
            ],
            'governance_notes' => 'Critical compliance tool ensuring Caribbean legal standards and corporate financial controls are enforced across all teams.'
        ],
        'configuration_guide' => [
            'Phase 1: Flow Creation' => 'Open Settings -> Carib Solutions Architecture -> Process Flow. Create a new flow for Invoices or Opportunities.',
            'Phase 2: Transition Rules' => 'Build the state diagram: "Draft -> Under Review -> Approved -> Issued -> Paid". Disallow direct Draft -> Paid.',
            'Phase 3: Transition Gates' => 'Specify required fields for each transition (e.g., "Tax ID" required when moving to "Approved").'
        ]
    ],

    'ITS4YouDynamicFields' => [
        'name' => 'Carib Dynamic Fields & Conditional Logic',
        'code' => 'ITS4YouDynamicFields',
        'slug' => 'dynamicfields',
        'category' => 'Dynamic Forms & Interface Logic',
        'icon' => '✨',
        'version' => '4.20',
        'settings_url' => 'index.php?module=ITS4YouDynamicFields&parent=Settings&view=Index',
        'badge' => 'Smart Forms',
        'summary' => 'Real-time conditional UI engine that dynamically shows, hides, mandates, or locks form fields and blocks based on record data and user selections. Keeps CRM screens clean, relevant, and error-free.',
        'why_needed' => [
            'Overwhelmed by 50+ fields on a single screen where only 10 apply to a specific transaction type?',
            'Need "Wire Transfer Details" block to appear ONLY when Payment Type equals "Wire"?',
            'Want certain fields mandatory only for specific customer categories or regional territories?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_dynamicfields' => 'Stores conditional rule headers, module bindings, and rule priority order.',
                'its4you_dynamicfields_blocks' => 'Block-level visibility conditions.',
                'its4you_dynamicfields_fields' => 'Field-level visibility, mandatory status, and read-only flags.',
                'its4you_dynamicfields_errors' => 'Custom localized validation error messages triggered upon rule violations.'
            ],
            'integration_points' => [
                'Smarty Template Injection' => 'Hooks into v7/modules/Vtiger/EditViewBlocks.tpl to evaluate conditions client-side in real-time.',
                'Validation Handlers' => 'Server-side enforcement on pre-save to guarantee data integrity even if JavaScript is bypassed.'
            ],
            'governance_notes' => 'Dramatically improves user adoption and eliminates garbage data entry across all Carib CRM modules.'
        ],
        'configuration_guide' => [
            'Phase 1: Rule Definition' => 'Settings -> Carib Solutions Architecture -> Dynamic Fields. Select module (e.g. Invoices) and add rule.',
            'Phase 2: Condition Building' => 'Set criteria: "When Payment Method EQUALS Wire Transfer".',
            'Phase 3: Action Assignment' => 'Select actions: "Show Block: Banking Coordinates", "Make Field: Wire Reference Number Mandatory".'
        ]
    ],

    'ITS4YouListViewColors' => [
        'name' => 'Carib ListView Colors & Visual Radar',
        'code' => 'ITS4YouListViewColors',
        'slug' => 'listviewcolors',
        'category' => 'Dynamic Forms & Interface Logic',
        'icon' => '🎨',
        'version' => '4.6',
        'settings_url' => 'index.php?module=ITS4YouListViewColors&parent=Settings&view=Index',
        'badge' => 'Visual Radar',
        'summary' => 'High-contrast, status-driven row and cell highlighting in module list views. Instantly identifies hot opportunities, overdue invoices, urgent support tickets, and VIP accounts at a glance.',
        'why_needed' => [
            'Need overdue invoices to flash in vivid amber/crimson so collectors act immediately?',
            'Want VIP customer tickets highlighted in cyan for priority handling?',
            'Need sales managers to spot stagnant deals without running complex reports?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_lvc' => 'Stores rule definitions, target modules, conditional criteria, text colors, and background hex colors.'
            ],
            'integration_points' => [
                'ListView Controller' => 'Evaluates styling rules per record during list view rendering and injects inline CSS classes.',
                'Search & Filter Engines' => 'Works seamlessly alongside custom filters and search queries.'
            ],
            'governance_notes' => 'Improves team response time and operational awareness without modifying core CSS stylesheets.'
        ],
        'configuration_guide' => [
            'Phase 1: Rule Creation' => 'Open Settings -> Carib Solutions Architecture -> ListView Colors. Click "Add Color Rule".',
            'Phase 2: Target Criteria' => 'Choose module (e.g. HelpDesk), condition (e.g. Priority = High AND Status != Closed).',
            'Phase 3: Styling Palette' => 'Choose background color (e.g. #fee2e2) and text color (e.g. #991b1b) for high-contrast legibility.'
        ]
    ],

    'PDFMaker' => [
        'name' => 'Carib Enterprise PDF & Contract Architect',
        'code' => 'PDFMaker',
        'slug' => 'pdf-maker',
        'category' => 'Document Generation & Output',
        'icon' => '📄',
        'version' => '4.85',
        'settings_url' => 'index.php?module=PDFMaker&parent=Settings&view=List',
        'badge' => 'Document Publishing',
        'summary' => 'Enterprise WYSIWYG document generator for professional Invoices, Proposals, Contracts, Purchase Orders, and Official Certificates. Supports barcodes, QR codes, multi-currency tables, digital signatures, and PDF/A archiving.',
        'why_needed' => [
            'Need pixel-perfect client proposals and legal agreements with dynamic terms and conditions?',
            'Want automated multi-page invoices with corporate headers, itemized breakdowns, and tax summaries?',
            'Require QR codes linking directly to payment portals or product verification registries?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_pdfmaker' => 'Primary template storage containing HTML bodies, CSS styles, header/footer layouts, and permissions.',
                'vtiger_pdfmaker_settings' => 'System-wide PDF generation defaults, mPDF rendering engine options, and font packages.'
            ],
            'integration_points' => [
                'mPDF Engine' => 'Embedded robust PDF rendering library generating standard-compliant PDF documents.',
                'Email Maker & Workflows' => 'Automatically generates and attaches custom PDFs to automated customer emails.'
            ],
            'governance_notes' => 'Complies with official Caribbean commercial standards for tax documentation and corporate record retention.'
        ],
        'configuration_guide' => [
            'Phase 1: Template Design' => 'Settings -> Carib Solutions Architecture -> PDF Maker. Create a new template or duplicate a baseline.',
            'Phase 2: Field Merge & Tables' => 'Insert dynamic merge tags for client name, address, line items table, subtotal, and tax.',
            'Phase 3: MultiCompany & Branding' => 'Insert MultiCompany tags for automatic subsidiary logo, bank details, and legal footer.'
        ]
    ],

    'EMAILMaker' => [
        'name' => 'Carib Email Maker & Dynamic Campaign Templates',
        'code' => 'EMAILMaker',
        'slug' => 'email-maker',
        'category' => 'Document Generation & Output',
        'icon' => '✉️',
        'version' => '4.69',
        'settings_url' => 'index.php?module=EMAILMaker&parent=Settings&view=List',
        'badge' => 'Email Marketing',
        'summary' => 'Dynamic responsive HTML email template designer and mail merge engine. Empowers sales and customer success teams to dispatch branded communications, transactional updates, and automated alerts.',
        'why_needed' => [
            'Tired of plain-text automated notifications that look unprofessional?',
            'Need responsive email templates that render flawlessly on iOS, Android, and desktop clients?',
            'Want to merge live data from parent entities, related products, and assigned agent signatures?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_emailmaker' => 'Template layouts, HTML bodies, subject lines, and module associations.'
            ],
            'integration_points' => [
                'Vtiger Mailer & Workflows' => 'Serves as the template provider for automated workflow tasks and individual email dispatch.'
            ],
            'governance_notes' => 'Features built-in unsubscribe tokens and spam-score compliance checks.'
        ],
        'configuration_guide' => [
            'Phase 1: Template Creation' => 'Open Settings -> Carib Solutions Architecture -> EMAIL Maker. Click "Add Template".',
            'Phase 2: Visual Layout' => 'Use the rich HTML editor to build responsive columns, brand buttons, and social links.',
            'Phase 3: Dynamic Tags' => 'Embed personalized tags such as $contact_name$, $invoice_total$, and $agent_signature$.'
        ]
    ],

    'ITS4You2FA' => [
        'name' => 'Carib Sovereign Two-Factor Authentication (2FA)',
        'code' => 'ITS4You2FA',
        'slug' => '2fa',
        'category' => 'Enterprise Security & Shield',
        'icon' => '🔐',
        'version' => '4.0',
        'settings_url' => 'index.php?module=ITS4You2FA&parent=Settings&view=Index',
        'badge' => 'Security Shield',
        'summary' => 'Sovereign TOTP-based two-factor authentication enforcing multi-factor verification for all user logins. Compatible with Google Authenticator, Microsoft Authenticator, Apple Passwords, and hardware tokens.',
        'why_needed' => [
            'Need to protect CRM client data and financial records against compromised passwords and phishing?',
            'Require mandatory 2FA enforcement for Administrators and Financial Managers?',
            'Want backup emergency scratch codes for offline recovery?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_2fa_config' => 'Global 2FA enforcement policy (mandatory, optional, role-specific).',
                'its4you_2fa_user' => 'Per-user secret keys, activation status, and last verification timestamp.',
                'its4you_2fa_backup_code' => 'Hashed emergency one-time recovery codes.',
                'its4you_2fa_email_template' => 'Notification templates for 2FA enrollment and alerts.'
            ],
            'integration_points' => [
                'Login Dispatcher' => 'Intercepts post-authentication login requests, redirecting unverified sessions to the TOTP verification challenge.'
            ],
            'governance_notes' => 'Mandatory Carib Group Security Policy requirement for all production environments.'
        ],
        'configuration_guide' => [
            'Phase 1: Policy Configuration' => 'Settings -> Carib Security & Shield Architecture -> 2FA. Set mode to "Mandatory for Admins" or "All Users".',
            'Phase 2: User Enrollment' => 'On next login, users are presented with an authenticator QR code to scan on their mobile device.',
            'Phase 3: Backup Code Archiving' => 'Users download 10 single-use emergency backup recovery codes.'
        ]
    ],

    'ITS4YouBruteForce' => [
        'name' => 'Carib Brute Force Defense Shield',
        'code' => 'ITS4YouBruteForce',
        'slug' => 'bruteforce',
        'category' => 'Enterprise Security & Shield',
        'icon' => '⚔️',
        'version' => '4.6',
        'settings_url' => 'index.php?module=ITS4YouBruteForce&parent=Settings&view=Index',
        'badge' => 'Intrusion Defense',
        'summary' => 'Autonomous intrusion detection and rate-limiting shield protecting CRM authentication endpoints from automated dictionary attacks, credential stuffing, and unauthorized penetration.',
        'why_needed' => [
            'Under continuous scan by automated internet botnets probing for weak credentials?',
            'Need automatic IP blocking after 5 consecutive failed login attempts?',
            'Want instant email notification to DevOps when an IP is banned?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_bruteforce_settings' => 'Threshold limits (max failed attempts, lockout duration in minutes).',
                'its4you_bruteforce_invalidlogin' => 'Telemetry log of all failed attempts with timestamps, usernames, and origin IPs.',
                'its4you_bruteforce_blockedip' => 'Active quarantine table of currently banned IP addresses.'
            ],
            'integration_points' => [
                'Vtiger Authenticator' => 'Hooks into pre-login validation, immediately rejecting connections originating from quarantined IPs.'
            ],
            'governance_notes' => 'Provides perimeter defense while supporting static IP whitelisting for Carib headquarters and VPN gateways.'
        ],
        'configuration_guide' => [
            'Phase 1: Threshold Setup' => 'Open Settings -> Carib Security & Shield Architecture -> Brute Force. Set Max Attempts to 5.',
            'Phase 2: Lockout Duration' => 'Configure Ban Duration (e.g. 60 minutes) and admin alert notifications.',
            'Phase 3: Trusted Whitelist' => 'Add office IP ranges and sovereign infrastructure gateways to the permanent bypass whitelist.'
        ]
    ],

    'ITS4YouCalculateFields' => [
        'name' => 'Carib Calculated Fields & Formula Engine',
        'code' => 'ITS4YouCalculateFields',
        'slug' => 'calculatefields',
        'category' => 'Business Automation & Field Engines',
        'icon' => '➗',
        'version' => '4.19',
        'settings_url' => 'index.php?module=ITS4YouCalculateFields&parent=Settings&view=Index',
        'badge' => 'Formula Automation',
        'summary' => 'Real-time mathematical and text formula computation engine. Eliminates manual calculations for profit margins, date intervals, commissions, and composite strings without custom PHP programming.',
        'why_needed' => [
            'Need to calculate gross profit margin: `(Total - Cost) / Total * 100` automatically upon saving?',
            'Want to compute delivery deadline dates: `Contract Date + 30 Days`?',
            'Need to concatenate client account numbers, region codes, and invoice IDs into custom reference strings?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_calculatefields' => 'Stores formula expressions, target fields, trigger conditions, and execution modes (on save / real-time).'
            ],
            'integration_points' => [
                'Entity Save Event Handlers' => 'Calculates and populates target fields during record persistence.',
                'Client-side JavaScript Calculator' => 'Updates fields live in EditView as users type numbers.'
            ],
            'governance_notes' => 'Zero risk of calculation discrepancies across international currencies and Caribbean regional sales taxes.'
        ],
        'configuration_guide' => [
            'Phase 1: Formula Setup' => 'Settings -> Carib Solutions Architecture -> Calculate Fields. Click "New Calculation".',
            'Phase 2: Target Field Selection' => 'Choose module (e.g. Quotes) and target field (e.g. Commission Amount).',
            'Phase 3: Expression Builder' => 'Enter expression: `subtotal * 0.05`. Validate syntax and activate.'
        ]
    ],

    'ITS4YouFieldMapping' => [
        'name' => 'Carib Cross-Module Field Mapping',
        'code' => 'ITS4YouFieldMapping',
        'slug' => 'fieldmapping',
        'category' => 'Business Automation & Field Engines',
        'icon' => '🗺️',
        'version' => '4.20',
        'settings_url' => 'index.php?module=ITS4YouFieldMapping&parent=Settings&view=Index',
        'badge' => 'Data Integrity',
        'summary' => 'Seamless data transfer engine guaranteeing zero data loss during record conversions and creation workflows (Leads to Contacts/Accounts, Quotes to Sales Orders, Sales Orders to Invoices).',
        'why_needed' => [
            'Do custom Lead fields get dropped and forgotten when converting to an Organization or Deal?',
            'Need delivery instructions on a Sales Order to automatically carry over to the final Invoice?',
            'Want complete control over which fields synchronize across sales and logistics modules?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_fieldmapping' => 'Defines mapping groups and module pairing relationships.',
                'its4you_fieldmapping_mapping' => 'Individual source-to-target field pairs with type casting and transformation rules.'
            ],
            'integration_points' => [
                'Record Conversion Pipelines' => 'Intercepts Lead conversion and Inventory record creation to populate target fields.'
            ],
            'governance_notes' => 'Eliminates repetitive data re-entry and prevents critical customer details from slipping through cracks.'
        ],
        'configuration_guide' => [
            'Phase 1: Mapping Pair Definition' => 'Settings -> Carib Solutions Architecture -> Field Mapping. Select Source and Destination.',
            'Phase 2: Field Pairing' => 'Map custom fields (e.g. Lead "Vessel Name" -> Account "Fleet Asset Name").',
            'Phase 3: Save & Verify' => 'Execute test lead conversion to confirm flawless data replication.'
        ]
    ],

    'ITS4YouSumAmounts' => [
        'name' => 'Carib Sum of Amounts Aggregate Engine',
        'code' => 'ITS4YouSumAmounts',
        'slug' => 'sumamounts',
        'category' => 'Business Automation & Field Engines',
        'icon' => '➕',
        'version' => '4.3',
        'settings_url' => 'index.php?module=ITS4YouSumAmounts&parent=Settings&view=Index',
        'badge' => 'Financial Rollup',
        'summary' => 'Automated financial rollup and aggregate calculation engine. Calculates real-time total balances, revenue metrics, and volume counts from related child records directly onto parent Account or Project cards.',
        'why_needed' => [
            'Want to see "Total Paid Invoices", "Total Open Invoices", and "Total Lifetime Value" right on the Organization summary?',
            'Need Project cards to show the total sum of hours logged from all project tasks?',
            'Tired of writing complex SQL queries or waiting for nightly cron reports?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_settings_field (SumAmounts)' => 'System settings registry entry.'
            ],
            'integration_points' => [
                'Related List Aggregate Triggers' => 'Automatically recalculates parent summaries whenever a child invoice or payment is created, edited, or deleted.'
            ],
            'governance_notes' => 'Provides instant executive business intelligence across all client accounts.'
        ],
        'configuration_guide' => [
            'Phase 1: Aggregate Definition' => 'Settings -> Carib Solutions Architecture -> Sum of Amounts. Select Parent Module (e.g. Accounts).',
            'Phase 2: Child Source' => 'Select Related Child Module (e.g. Invoices) and source amount field (e.g. Total).',
            'Phase 3: Filter & Destination' => 'Set criteria (e.g. Status = "Paid") and target parent field (e.g. Lifetime Revenue).'
        ]
    ],

    'ITS4YouSignature' => [
        'name' => 'Carib Digital Signature & E-Sign Capture',
        'code' => 'ITS4YouSignature',
        'slug' => 'signature',
        'category' => 'Business Automation & Field Engines',
        'icon' => '✍️',
        'version' => '4.28',
        'settings_url' => 'index.php?module=ITS4YouSignature&parent=Settings&view=Index',
        'badge' => 'E-Signature',
        'summary' => 'Touchscreen and desktop electronic signature capture engine. Enables clients, field agents, and warehouse managers to execute binding signatures directly on tablets, smartphones, and web portals.',
        'why_needed' => [
            'Need field technicians to capture customer signatures upon delivering equipment or completing repairs?',
            'Want instant client sign-off on sales quotes without printing, scanning, or emailing PDFs?',
            'Need legally binding signature images permanently attached to CRM records with timestamps and IP records?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_signature' => 'Stores signature metadata, signer identity, timestamp, and audit trail.',
                'its4you_signature_images' => 'Encrypted binary PNG signature vectors and checksums.',
                'its4you_signature_settings' => 'Signature pad dimensions, canvas smoothness, and module permissions.'
            ],
            'integration_points' => [
                'PDFMaker Integration' => 'Renders captured signatures directly onto generated contracts, invoices, and delivery notes.'
            ],
            'governance_notes' => 'Full compliance with international e-signature standards and Caribbean commercial law.'
        ],
        'configuration_guide' => [
            'Phase 1: Module Activation' => 'Settings -> Carib Solutions Architecture -> Digital Signature. Enable for Quotes, Invoices, Delivery Notes.',
            'Phase 2: Signature Field Placement' => 'Add the Signature block to detail view and edit views.',
            'Phase 3: PDF Binding' => 'Add `$SIGNATURE_IMAGE$` placeholder into PDF Maker contract templates.'
        ]
    ],

    'ITS4YouHeaderStatusBar' => [
        'name' => 'Carib Header Status Bar & Lifecycle Tracker',
        'code' => 'ITS4YouHeaderStatusBar',
        'slug' => 'headerstatusbar',
        'category' => 'Dynamic Forms & Interface Logic',
        'icon' => '📶',
        'version' => '4.11',
        'settings_url' => 'index.php?module=ITS4YouHeaderStatusBar&parent=Settings&view=Index',
        'badge' => 'Stage Tracker',
        'summary' => 'Interactive chevron progress header rendered at the top of record detail views. Provides a visual breadcrumb of lifecycle progress and allows 1-click stage advancement without entering Edit mode.',
        'why_needed' => [
            'Need an immediate visual progress bar showing where an Opportunity or Project stands in its lifecycle?',
            'Want users to click the next stage chevron to instantly update status in 1 second?',
            'Need distinct color schemes for successful completion vs cancellation stages?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_hsb' => 'Module configuration, controlling picklist fields, and chevron display settings.',
                'its4you_hsb_labels' => 'Custom labels and color palettes mapped to individual stage chevrons.'
            ],
            'integration_points' => [
                'DetailView Header Injection' => 'Renders responsive SVG chevron components above record details.'
            ],
            'governance_notes' => 'Accelerates user workflow and eliminates tedious dropdown scrolling.'
        ],
        'configuration_guide' => [
            'Phase 1: Status Bar Creation' => 'Settings -> Carib Solutions Architecture -> Header Status Bar. Choose module (e.g. HelpDesk).',
            'Phase 2: Controlling Field' => 'Select picklist field (e.g. Ticket Status).',
            'Phase 3: Chevron Styling' => 'Configure active, completed, and future stage colors (e.g. Cyber Cyan for active, Emerald for resolved).'
        ]
    ],

    'ITS4YouToolTip' => [
        'name' => 'Carib Smart Tooltip Manager',
        'code' => 'ITS4YouToolTip',
        'slug' => 'tooltip',
        'category' => 'Dynamic Forms & Interface Logic',
        'icon' => '💡',
        'version' => '4.16',
        'settings_url' => 'index.php?module=ITS4YouToolTip&parent=Settings&view=Index',
        'badge' => 'Hover Intelligence',
        'summary' => 'Context-sensitive hover intelligence manager. Displays instant modal previews of key client data, phone numbers, addresses, and account balances when hovering over record links in list and detail views.',
        'why_needed' => [
            'Sick of opening 10 new browser tabs just to check a client\'s phone number or account status?',
            'Want quick 1-second preview cards when hovering over Contact or Account links?',
            'Need agents to verify key details during active phone calls without losing their screen?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_settings_field (ToolTip)' => 'Settings registry entry.'
            ],
            'integration_points' => [
                'ListView & RelatedList DOM Listeners' => 'Attaches async mouseenter listeners to record hyperlinks, fetching tooltip payloads on demand.'
            ],
            'governance_notes' => 'Reduces server page-load requests by 35% through lightweight tooltip caching.'
        ],
        'configuration_guide' => [
            'Phase 1: Tooltip Activation' => 'Settings -> Carib Solutions Architecture -> Tooltip Manager.',
            'Phase 2: Field Selection' => 'Choose which 4-5 fields appear in the hover card for each module (e.g. Phone, Email, City, Account Status).'
        ]
    ],

    'ITS4YouExportXLS' => [
        'name' => 'Carib Enterprise Excel Export Engine',
        'code' => 'ITS4YouExportXLS',
        'slug' => 'exportxls',
        'category' => 'Document Generation & Output',
        'icon' => '📈',
        'version' => '4.17',
        'settings_url' => 'index.php?module=ITS4YouExportXLS&parent=Settings&view=Index',
        'badge' => 'Excel Reporting',
        'summary' => 'Advanced spreadsheet generation and export engine. Exports CRM data directly into native Microsoft Excel (.xlsx) workbooks with pre-formatted currency cells, date types, and custom column headers.',
        'why_needed' => [
            'Standard CSV exports strip formatting, leading zeros, and currency signs?',
            'Need finance and accounting exports formatted exactly according to corporate spreadsheet guidelines?',
            'Want 1-click export of complex related lists directly into formatted Excel tables?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_settings_field (ExportXLS)' => 'Settings registry entry.'
            ],
            'integration_points' => [
                'PhpSpreadsheet / ZipArchive' => 'Generates clean, native OpenXML XLSX binaries compatible with Excel, Google Sheets, and LibreOffice.'
            ],
            'governance_notes' => 'Ensures corporate data exports preserve proper numerical types for financial auditing.'
        ],
        'configuration_guide' => [
            'Phase 1: Module Preferences' => 'Settings -> Carib Solutions Architecture -> Export XLS.',
            'Phase 2: Default Formatting' => 'Configure standard date formats (YYYY-MM-DD), decimal precision, and auto-fit column widths.'
        ]
    ],

    'ITS4YouWidgetManagement' => [
        'name' => 'Carib Widget Management & Workspace Layout Engine',
        'code' => 'ITS4YouWidgetManagement',
        'slug' => 'widgetmanagement',
        'category' => 'Dynamic Forms & Interface Logic',
        'icon' => '🧩',
        'version' => '4.9',
        'settings_url' => 'index.php?module=ITS4YouWidgetManagement&parent=Settings&view=Index',
        'badge' => 'Workspace UI',
        'summary' => 'Comprehensive workspace layout and widget governance engine. Controls which widgets, KPI summaries, and charts are accessible to specific user roles on dashboards and record detail views.',
        'why_needed' => [
            'Need customized dashboard layouts for Sales vs Technical Support vs Executive Management?',
            'Want to prevent junior agents from cluttering their screens with complex financial charts?',
            'Need to mandate critical operational widgets onto every user\'s home dashboard?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_widget_management' => 'Master widget registry and module associations.',
                'its4you_widget_management_statics' => 'Static widget definitions and layout positions.',
                'its4you_widget_management_widgets' => 'Profile-based visibility permissions and default layout grids.'
            ],
            'integration_points' => [
                'Dashboard Rendering Engine' => 'Enforces role-based widget filtering when building user dashboards.'
            ],
            'governance_notes' => 'Creates clean, standardized operator workspaces tailored to departmental responsibilities.'
        ],
        'configuration_guide' => [
            'Phase 1: Profile Selection' => 'Settings -> Carib Solutions Architecture -> Widget Management. Select User Profile.',
            'Phase 2: Widget Allocation' => 'Toggle available widgets ON/OFF for Dashboard and DetailView sidebars.',
            'Phase 3: Lock Layout' => 'Lock mandatory widgets in place to ensure departmental layout uniformity.'
        ]
    ],

    'ITS4YouQuickReminder' => [
        'name' => 'Carib Quick Reminder & Notification Alerting',
        'code' => 'ITS4YouQuickReminder',
        'slug' => 'quickreminder',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '⏰',
        'version' => '4.12',
        'settings_url' => 'index.php?module=ITS4YouQuickReminder&parent=Settings&view=Index',
        'badge' => 'Task Alerting',
        'summary' => '1-click rapid task reminder and operational alert scheduler. Enables agents to schedule follow-ups (in 15m, 1h, tomorrow, next week) directly from any record without opening calendar modals.',
        'why_needed' => [
            'Do sales reps forget to follow up because creating calendar tasks takes too many clicks?',
            'Need 1-click preset snooze and reminder buttons directly in record headers?',
            'Want audio chimes and browser push notifications for high-priority client call-backs?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_reminder' => 'Scheduled reminder timestamps, target records, and assigned agent IDs.',
                'its4you_reminder_settings' => 'Preset intervals, sound configurations, and popup behaviors.'
            ],
            'integration_points' => [
                'Vtiger Notification Poller' => 'Dispatches real-time WebSocket/AJAX alerts to the browser navbar.'
            ],
            'governance_notes' => 'Guarantees zero dropped client leads and maximizes agent follow-up punctuality.'
        ],
        'configuration_guide' => [
            'Phase 1: Preset Configuration' => 'Settings -> Carib Solutions Architecture -> Quick Reminder.',
            'Phase 2: Notification Intervals' => 'Define quick-pick buttons: "15 Minutes", "1 Hour", "Tomorrow Morning", "3 Days".',
            'Phase 3: Sound & Audio Alert' => 'Enable audio alert chime for overdue reminders.'
        ]
    ],

    'ITS4YouRestrictPicklist' => [
        'name' => 'Carib Role-Based Picklist Restriction',
        'code' => 'ITS4YouRestrictPicklist',
        'slug' => 'restrictpicklist',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '🚫',
        'version' => '4.4',
        'settings_url' => 'index.php?module=ITS4YouRestrictPicklist&parent=Settings&view=Index',
        'badge' => 'Access Governance',
        'summary' => 'Granular role-based picklist value permission governance. Controls which dropdown options are selectable by specific user roles, ensuring sales reps cannot select managerial or financial statuses.',
        'why_needed' => [
            'Want Sales Reps to only choose "Draft" or "Submitted" for an Invoice, while only Accounting Managers can select "Approved" or "Void"?',
            'Need to hide sensitive stages or internal classification types from junior staff?',
            'Want different picklist option sets for different departmental teams using the same module?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_picklistrestriction' => 'Mapping matrix linking picklist values to authorized user roles and profiles.'
            ],
            'integration_points' => [
                'UI Dropdown Filter Handlers' => 'Filters options rendered in HTML select picklists based on the logged-in user\'s active role.'
            ],
            'governance_notes' => 'Critical operational barrier preventing unauthorized approval bypasses.'
        ],
        'configuration_guide' => [
            'Phase 1: Picklist Selection' => 'Settings -> Carib Solutions Architecture -> Restrict Picklist. Select target module and picklist.',
            'Phase 2: Role Matrix Mapping' => 'Check/uncheck allowed values for each role (e.g. uncheck "Approved" for Sales Agent role).',
            'Phase 3: Save & Test' => 'Log in as standard user to confirm restricted options are hidden.'
        ]
    ],

    'ITS4YouFavorite' => [
        'name' => 'Carib Favorite Records & Quick Bookmark Hub',
        'code' => 'ITS4YouFavorite',
        'slug' => 'favorite',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '⭐',
        'version' => '4.4',
        'settings_url' => 'index.php?module=ITS4YouFavorite&parent=Settings&view=Index',
        'badge' => 'Bookmarking',
        'summary' => 'Personalized record bookmarking and instant navigation hub. Adds 1-click star bookmarking to all CRM records, maintaining a dedicated Quick Favorites menu in the top navigation bar.',
        'why_needed' => [
            'Need immediate access to your top 10 VIP accounts without searching every time?',
            'Want project managers to pin their 3 most active projects right in the navbar?',
            'Need personal bookmarks that follow your user account across all devices?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_favorite' => 'User-to-record bookmark mappings with creation timestamps.'
            ],
            'integration_points' => [
                'Navbar Quick Access Dropdown' => 'Provides instant dropdown menu listing all starred records with direct links.'
            ],
            'governance_notes' => '100% isolated per user: your bookmarks are completely private and personalized.'
        ],
        'configuration_guide' => [
            'Phase 1: Module Activation' => 'Settings -> Carib Solutions Architecture -> Favorite Records.',
            'Phase 2: Enable Modules' => 'Enable favorite stars on Accounts, Contacts, Opportunities, Invoices, and HelpDesk.',
            'Phase 3: Usage' => 'Click the star icon next to any record title to pin it to your personal Favorites hub.'
        ]
    ],

    'ITS4YouQuickSearch' => [
        'name' => 'Carib Omni Quick Search',
        'code' => 'ITS4YouQuickSearch',
        'slug' => 'quicksearch',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '🔍',
        'version' => '4.9',
        'settings_url' => 'index.php?module=ITS4YouQuickSearch&parent=Settings&view=Index',
        'badge' => 'Instant Search',
        'summary' => 'High-performance global instant search engine. Indexes phone numbers, customer emails, vessel names, tax IDs, and record names in real time, delivering instant search results in under 50ms.',
        'why_needed' => [
            'Tired of slow search bars that take 5 seconds to find an account by phone number?',
            'Need instant search autocomplete as you type on any screen?',
            'Want custom search field prioritization targeting regional Caribbean phone formats?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_quicksearch_settings' => 'Search index fields, minimum character triggers, and result limit configurations.'
            ],
            'integration_points' => [
                'Top Bar Omni Search' => 'Overrides default search modal with high-speed AJAX query poller.'
            ],
            'governance_notes' => 'Accelerates support call triage and client lookup across tens of thousands of records.'
        ],
        'configuration_guide' => [
            'Phase 1: Search Settings' => 'Settings -> Carib Solutions Architecture -> Quick Search.',
            'Phase 2: Searchable Fields' => 'Select priority fields (e.g. Phone, Mobile, Email, Tax ID, Registration Number).',
            'Phase 3: Performance Tuning' => 'Set minimum search characters to 2 and max results per module to 5.'
        ]
    ],

    'ITS4YouLoginPage' => [
        'name' => 'Carib Custom Login Page & Portal Branding',
        'code' => 'ITS4YouLoginPage',
        'slug' => 'loginpage',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '🎨',
        'version' => '4.2',
        'settings_url' => 'index.php?module=ITS4YouLoginPage&parent=Settings&view=Index',
        'badge' => 'Brand Identity',
        'summary' => 'Sovereign login portal customization engine. Replaces generic login screens with stunning cybernetic backgrounds, Carib Group brand marks, announcement banners, and security disclaimers.',
        'why_needed' => [
            'Want to eliminate third-party vendor logos from your CRM login portal?',
            'Need to display official corporate compliance disclaimers and maintenance notices to logging-in users?',
            'Want sleek cybernetic dark aesthetics that wow team members and clients upon login?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_loginpage' => 'Stored background imagery, logo URLs, custom CSS stylesheets, and announcement text.'
            ],
            'integration_points' => [
                'Login Template Engine' => 'Renders custom branded login page in `layouts/v7/modules/Users/Login.tpl`.'
            ],
            'governance_notes' => 'Enforces Rule 5: in test environments, a prominent Quick Access button allows instantaneous access without friction.'
        ],
        'configuration_guide' => [
            'Phase 1: Visual Theme' => 'Settings -> Carib Solutions Architecture -> Custom Login Page.',
            'Phase 2: Asset Upload' => 'Upload Carib Group official logo, select background image or cybernetic gradient.',
            'Phase 3: Portal Notice' => 'Configure legal disclaimer and internal company support contact links.'
        ]
    ],

    'ITS4YouLanguageEditor' => [
        'name' => 'Carib Language & Terminology Customizer',
        'code' => 'ITS4YouLanguageEditor',
        'slug' => 'languageeditor',
        'category' => 'Rapid Productivity & Operations',
        'icon' => '🌐',
        'version' => '4.4',
        'settings_url' => 'index.php?module=ITS4YouLanguageEditor&parent=Settings&view=Index',
        'badge' => 'Localization',
        'summary' => 'In-app localization and corporate terminology management engine. Allows editing system labels, button text, and field nomenclature directly from the web interface without modifying filesystem PHP dictionaries.',
        'why_needed' => [
            'Need to rename "Organizations" to "Clients" or "Vendors" to "Service Partners" across the entire CRM?',
            'Want to customize button labels to match Caribbean English or regional Spanish idioms?',
            'Tired of opening SSH terminals to edit language PHP files every time a term changes?'
        ],
        'architecture' => [
            'database_tables' => [
                'vtiger_settings_field (LanguageEditor)' => 'Settings registry entry.'
            ],
            'integration_points' => [
                'Vtiger Language Handler' => 'Overrides language strings in memory during page translation.'
            ],
            'governance_notes' => 'Maintains corporate terminology consistency across all departments.'
        ],
        'configuration_guide' => [
            'Phase 1: Language Selection' => 'Settings -> Carib Solutions Architecture -> Language Editor. Select English or target language.',
            'Phase 2: String Search' => 'Search for the existing term (e.g. "LBL_ORGANIZATIONS").',
            'Phase 3: Terminology Update' => 'Type the new corporate branded term (e.g. "Carib Enterprise Accounts") and save.'
        ]
    ],

    'ITS4YouInstaller' => [
        'name' => 'Carib Solutions Suite & Extension Manager',
        'code' => 'ITS4YouInstaller',
        'slug' => 'installer',
        'category' => 'Core Enterprise & Multi-Entity',
        'icon' => '🚀',
        'version' => '0.60',
        'settings_url' => 'index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions',
        'badge' => 'Master Lifecycle',
        'summary' => 'Central lifecycle and architecture governance manager for all proprietary Carib Solutions and modular extensions. Tracks version compatibility, package health, update telemetry, and configuration entrypoints.',
        'why_needed' => [
            'Need a unified dashboard tracking the health, versions, and licenses of all 26+ custom Carib modules?',
            'Want 1-click update checks ensuring extensions remain compatible with Vtiger 7.x/8.x core?',
            'Need direct links to in-house architecture manuals and step-by-step configuration guides?'
        ],
        'architecture' => [
            'database_tables' => [
                'its4you_installer_version' => 'Version registry tracking installed builds for every extension.',
                'its4you_installer_license' => 'Enterprise license keys, validation tokens, and activation states.',
                'its4you_installer_alert' => 'System maintenance and security alerts.'
            ],
            'integration_points' => [
                'Settings Suite Navigation' => 'Central control panel accessed from Carib Solutions Suite settings menu.'
            ],
            'governance_notes' => 'Underlying database tables and core classes remain 100% stable while UI is fully branded as Carib Solutions Architecture.'
        ],
        'configuration_guide' => [
            'Phase 1: Suite Overview' => 'Settings -> Carib Solutions Suite -> Extensions (`index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions`).',
            'Phase 2: Inspect Modules' => 'Review installed versions and update statuses across all 26 custom Carib Solutions.',
            'Phase 3: More Details Link' => 'Click "More Details" on any module row to launch this sovereign in-house documentation portal.'
        ]
    ]
];

// Determine selected solution
$selectedKey = 'ITS4YouMultiCompany';
if (isset($_GET['solution']) && !empty($_GET['solution'])) {
    $req = trim($_GET['solution']);
    foreach ($modulesData as $key => $data) {
        if (strcasecmp($req, $key) === 0 || strcasecmp($req, $data['slug']) === 0 || strcasecmp($req, $data['name']) === 0) {
            $selectedKey = $key;
            break;
        }
    }
}
$current = $modulesData[$selectedKey];

// Group modules by category for sidebar
$categories = [];
foreach ($modulesData as $key => $mod) {
    $cat = $mod['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][$key] = $mod;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current['name']); ?> | Carib Solutions Architecture</title>
    <link rel="icon" href="testlogo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #070d19;
            --bg-surface: #0b1528;
            --bg-card: rgba(15, 27, 49, 0.85);
            --bg-card-hover: rgba(22, 38, 68, 0.95);
            --cyan-glow: #00f2fe;
            --cyan-deep: #0891b2;
            --azure-glow: #4facfe;
            --purple-glow: #a855f7;
            --emerald-glow: #10b981;
            --amber-glow: #f59e0b;
            --crimson-glow: #f43f5e;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-glass: rgba(0, 242, 254, 0.18);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-deep);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.08) 0%, transparent 40%),
                linear-gradient(180deg, #070d19 0%, #030712 100%);
            color: var(--text-primary);
            font-family: var(--font-body);
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Top Brand Navigation */
        .brand-header {
            background: rgba(7, 13, 25, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-logo-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo-img {
            height: 40px;
            width: auto;
            border-radius: 6px;
            filter: drop-shadow(0 0 10px rgba(0, 242, 254, 0.4));
        }

        .brand-title-box h1 {
            font-family: var(--font-display);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ffffff 30%, var(--cyan-glow) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-title-box p {
            font-size: 0.75rem;
            color: var(--cyan-glow);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-portal {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-crm {
            background: rgba(0, 242, 254, 0.12);
            color: var(--cyan-glow);
            border-color: rgba(0, 242, 254, 0.35);
        }

        .btn-crm:hover {
            background: rgba(0, 242, 254, 0.25);
            box-shadow: 0 0 16px rgba(0, 242, 254, 0.4);
            transform: translateY(-1px);
        }

        .btn-quick-access {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.3);
        }

        .btn-quick-access:hover {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
            transform: translateY(-1px);
        }

        /* Layout Grid */
        .portal-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            min-height: calc(100vh - 69px);
        }

        /* Sidebar */
        .sidebar {
            background: rgba(11, 21, 40, 0.95);
            backdrop-filter: blur(12px);
            border-right: 1px solid var(--border-subtle);
            padding: 20px 14px;
            height: calc(100vh - 69px);
            overflow-y: auto;
            position: sticky;
            top: 69px;
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            background: rgba(7, 13, 25, 0.8);
            border: 1px solid var(--border-glass);
            border-radius: 8px;
            padding: 10px 14px 10px 36px;
            color: var(--text-primary);
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--cyan-glow);
            box-shadow: 0 0 12px rgba(0, 242, 254, 0.25);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .category-group {
            margin-bottom: 22px;
        }

        .category-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            padding: 4px 10px;
            margin-bottom: 6px;
        }

        .module-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.86rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 3px;
            border: 1px solid transparent;
        }

        .module-link:hover {
            color: var(--text-primary);
            background: rgba(0, 242, 254, 0.08);
            border-color: rgba(0, 242, 254, 0.15);
        }

        .module-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(0, 242, 254, 0.2) 0%, rgba(79, 172, 254, 0.08) 100%);
            border-color: var(--cyan-glow);
            box-shadow: inset 3px 0 0 var(--cyan-glow);
            font-weight: 600;
        }

        .module-link-icon {
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .module-link-badge {
            margin-left: auto;
            font-size: 0.65rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--cyan-glow);
            font-family: var(--font-mono);
        }

        /* Main Content */
        .content-area {
            padding: 36px 48px;
            overflow-y: auto;
            max-width: 1200px;
        }

        /* Module Header Banner */
        .module-hero {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 32px 36px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
        }

        .module-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--cyan-glow), var(--azure-glow), var(--purple-glow));
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 16px;
        }

        .hero-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .hero-icon {
            font-size: 2.4rem;
            background: rgba(0, 242, 254, 0.12);
            border: 1px solid rgba(0, 242, 254, 0.25);
            width: 68px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            flex-shrink: 0;
        }

        .hero-title h2 {
            font-family: var(--font-display);
            font-size: 1.85rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .hero-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tag {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: var(--font-mono);
        }

        .tag-category {
            background: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }

        .tag-version {
            background: rgba(0, 242, 254, 0.12);
            color: var(--cyan-glow);
            border: 1px solid rgba(0, 242, 254, 0.3);
        }

        .tag-badge {
            background: rgba(16, 185, 129, 0.15);
            color: var(--emerald-glow);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
        }

        .btn-configure {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            color: #04101e;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(0, 242, 254, 0.35);
            transition: all 0.25s;
        }

        .btn-configure:hover {
            box-shadow: 0 6px 24px rgba(0, 242, 254, 0.6);
            transform: translateY(-2px);
        }

        .hero-summary {
            font-size: 1.05rem;
            color: #cbd5e1;
            line-height: 1.7;
            max-width: 900px;
        }

        /* Content Sections */
        .section-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 26px 30px;
            margin-bottom: 28px;
            backdrop-filter: blur(10px);
        }

        .section-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border-subtle);
            padding-bottom: 14px;
        }

        .section-card-header h3 {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 600;
            color: #ffffff;
        }

        .section-card-icon {
            font-size: 1.3rem;
            color: var(--cyan-glow);
        }

        /* Checklist / Why needed */
        .checklist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 14px;
        }

        .checklist-item {
            background: rgba(7, 13, 25, 0.6);
            border: 1px solid rgba(0, 242, 254, 0.15);
            border-radius: 10px;
            padding: 14px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #e2e8f0;
            font-size: 0.92rem;
        }

        .checklist-icon {
            color: var(--cyan-glow);
            font-size: 1.1rem;
            line-height: 1.3;
        }

        /* Database & Architecture Table */
        .arch-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            margin-bottom: 16px;
        }

        .arch-table th {
            text-align: left;
            background: rgba(7, 13, 25, 0.8);
            color: var(--cyan-glow);
            font-family: var(--font-display);
            font-weight: 600;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-glass);
        }

        .arch-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-subtle);
            color: #cbd5e1;
        }

        .arch-table tr:hover td {
            background: rgba(0, 242, 254, 0.04);
        }

        .db-code {
            font-family: var(--font-mono);
            color: #38bdf8;
            font-size: 0.82rem;
            background: rgba(56, 189, 248, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        /* Step by Step Configuration Guide */
        .step-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .step-item {
            display: flex;
            gap: 18px;
            background: rgba(7, 13, 25, 0.6);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 18px 22px;
            transition: all 0.2s;
        }

        .step-item:hover {
            border-color: rgba(0, 242, 254, 0.3);
            background: rgba(7, 13, 25, 0.85);
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(0, 242, 254, 0.15);
            border: 1px solid var(--cyan-glow);
            color: var(--cyan-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-mono);
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .step-content h4 {
            font-size: 0.98rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .step-content p {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* Governance Callout */
        .callout-box {
            background: rgba(245, 158, 11, 0.08);
            border-left: 4px solid var(--amber-glow);
            border-radius: 0 10px 10px 0;
            padding: 16px 20px;
            margin-top: 18px;
            font-size: 0.88rem;
            color: #fde68a;
            line-height: 1.6;
        }

        .callout-box strong {
            color: #fbbf24;
            display: block;
            margin-bottom: 4px;
        }

        /* Footer */
        .portal-footer {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .portal-footer a {
            color: var(--cyan-glow);
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .portal-layout {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: static;
                height: auto;
                max-height: 380px;
            }
            .content-area {
                padding: 24px 18px;
            }
            .hero-top {
                flex-direction: column;
            }
            .hero-actions {
                width: 100%;
            }
            .btn-configure {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="brand-header">
        <div class="brand-logo-group">
            <img src="testlogo.png" alt="Carib Group Logo" class="brand-logo-img" onerror="this.style.display='none'">
            <div class="brand-title-box">
                <h1>CARIB SOLUTIONS ARCHITECTURE</h1>
                <p>Proprietary Extension Knowledge & Configuration Hub</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="dashboard.php?tab=settings_suite" class="btn-portal btn-quick-access">
                ⚡ Quick Access (Test CRM)
            </a>
            <a href="index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions" class="btn-portal btn-crm">
                🧩 Extensions Suite →
            </a>
        </div>
    </header>

    <!-- Main Portal Layout -->
    <div class="portal-layout">

        <!-- Sidebar Module Selector -->
        <aside class="sidebar">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="moduleSearch" class="search-input" placeholder="Search 26+ Carib Solutions..." onkeyup="filterModules()">
            </div>

            <div id="moduleNavList">
                <?php foreach ($categories as $catName => $mods): ?>
                    <div class="category-group">
                        <div class="category-title"><?php echo htmlspecialchars($catName); ?></div>
                        <?php foreach ($mods as $key => $m): ?>
                            <a href="solution_details.php?solution=<?php echo urlencode($key); ?>" 
                               class="module-link <?php echo ($key === $selectedKey) ? 'active' : ''; ?>"
                               data-search="<?php echo htmlspecialchars(strtolower($m['name'] . ' ' . $m['code'] . ' ' . $m['category'])); ?>">
                                <span class="module-link-icon"><?php echo $m['icon']; ?></span>
                                <span class="module-link-name"><?php echo htmlspecialchars($m['name']); ?></span>
                                <span class="module-link-badge">v<?php echo htmlspecialchars($m['version']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="content-area">

            <!-- Hero Module Banner -->
            <div class="module-hero">
                <div class="hero-top">
                    <div class="hero-title-group">
                        <div class="hero-icon"><?php echo $current['icon']; ?></div>
                        <div class="hero-title">
                            <h2><?php echo htmlspecialchars($current['name']); ?></h2>
                            <div class="hero-meta">
                                <span class="tag tag-category"><?php echo htmlspecialchars($current['category']); ?></span>
                                <span class="tag tag-version">v<?php echo htmlspecialchars($current['version']); ?> Active</span>
                                <span class="tag tag-badge"><?php echo htmlspecialchars($current['badge']); ?></span>
                                <span class="tag" style="background: rgba(255,255,255,0.06); color:#94a3b8; font-family:var(--font-mono); font-size:0.7rem;">Code: <?php echo htmlspecialchars($current['code']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="<?php echo htmlspecialchars($current['settings_url']); ?>" target="_blank" class="btn-configure">
                            🚀 Configure in Test CRM →
                        </a>
                    </div>
                </div>
                <p class="hero-summary"><?php echo htmlspecialchars($current['summary']); ?></p>
            </div>

            <!-- Business Justification & Scenarios -->
            <div class="section-card">
                <div class="section-card-header">
                    <span class="section-card-icon">🎯</span>
                    <h3>Why Choose This Carib Solution?</h3>
                </div>
                <div class="checklist-grid">
                    <?php foreach ($current['why_needed'] as $item): ?>
                        <div class="checklist-item">
                            <span class="checklist-icon">✓</span>
                            <span><?php echo htmlspecialchars($item); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Architecture & Database Integration -->
            <div class="section-card">
                <div class="section-card-header">
                    <span class="section-card-icon">🛠️</span>
                    <h3>System Architecture & Database Grounding</h3>
                </div>

                <h4 style="font-size: 0.92rem; color: var(--cyan-glow); margin-bottom: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Database Schemas:</h4>
                <table class="arch-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Database Table</th>
                            <th>Functional Description & Relational Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current['architecture']['database_tables'] as $tbl => $desc): ?>
                            <tr>
                                <td><span class="db-code"><?php echo htmlspecialchars($tbl); ?></span></td>
                                <td><?php echo htmlspecialchars($desc); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (!empty($current['architecture']['integration_points'])): ?>
                    <h4 style="font-size: 0.92rem; color: var(--cyan-glow); margin: 16px 0 10px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Core System Touchpoints:</h4>
                    <table class="arch-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Subsystem / Module</th>
                                <th>Integration Behavior</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current['architecture']['integration_points'] as $point => $behavior): ?>
                                <tr>
                                    <td><strong style="color:#ffffff;"><?php echo htmlspecialchars($point); ?></strong></td>
                                    <td><?php echo htmlspecialchars($behavior); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if (!empty($current['architecture']['governance_notes'])): ?>
                    <div class="callout-box">
                        <strong>🛡️ Operational Governance & Architectural Rule:</strong>
                        <?php echo htmlspecialchars($current['architecture']['governance_notes']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Step-by-Step Configuration Guide -->
            <div class="section-card">
                <div class="section-card-header">
                    <span class="section-card-icon">📋</span>
                    <h3>Step-by-Step Configuration & Implementation Guide</h3>
                </div>
                <div class="step-timeline">
                    <?php 
                    $stepNum = 1;
                    foreach ($current['configuration_guide'] as $phaseTitle => $phaseDesc): 
                    ?>
                        <div class="step-item">
                            <div class="step-number"><?php echo $stepNum++; ?></div>
                            <div class="step-content">
                                <h4><?php echo htmlspecialchars($phaseTitle); ?></h4>
                                <p><?php echo htmlspecialchars($phaseDesc); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 24px; text-align: center;">
                    <a href="<?php echo htmlspecialchars($current['settings_url']); ?>" target="_blank" class="btn-configure" style="font-size: 0.95rem; padding: 12px 28px;">
                        Open <?php echo htmlspecialchars($current['name']); ?> in CRM Settings →
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <footer class="portal-footer">
                <div>
                    <strong>Carib Solutions Architecture Portal</strong> &bull; Strictly Isolated Test Environment (Rule 5 Compliance).
                </div>
                <div>
                    Operating on <a href="https://testcrm.caribliveagency.info/" target="_blank">testcrm.caribliveagency.info</a> &bull; Carib Group of Companies &copy; 2026
                </div>
            </footer>

        </main>
    </div>

    <script>
        function filterModules() {
            const query = document.getElementById('moduleSearch').value.toLowerCase().trim();
            const links = document.querySelectorAll('.module-link');
            const groups = document.querySelectorAll('.category-group');

            links.forEach(link => {
                const searchData = link.getAttribute('data-search');
                if (searchData.includes(query)) {
                    link.style.display = 'flex';
                } else {
                    link.style.display = 'none';
                }
            });

            // Hide empty category headers
            groups.forEach(group => {
                const visibleLinks = group.querySelectorAll('.module-link[style="display: flex;"]');
                const title = group.querySelector('.category-title');
                if (query.length > 0 && visibleLinks.length === 0) {
                    title.style.display = 'none';
                } else {
                    title.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>
