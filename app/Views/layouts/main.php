<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FleetPulse FMS') ?> &mdash; Enterprise Logistics</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;

            --sidebar-w: 250px;
            --bg-canvas: #f8fafc;
            --bg-surface: #ffffff;
            --border-subtle: #e2e8f0;
            --border-strong: #cbd5e1;

            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;

            --primary: #1e3a8a;
            --primary-accent: #2563eb;
            --sidebar-bg: #0b1324;
            --sidebar-item-hover: rgba(255, 255, 255, 0.05);
            --sidebar-item-active: rgba(37, 99, 235, 0.15);
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-canvas);
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.011em;
            margin: 0;
            padding: 0;
        }

        /* Monospace accents for IDs, plates, numbers */
        .mono {
            font-family: var(--font-mono);
            letter-spacing: -0.03em;
        }

        /* -------------------------------------------------------------
           Corporate Minimalist Sidebar
        ------------------------------------------------------------- */
        #sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 0.95rem;
        }

        .brand-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            line-height: 1.2;
        }

        .brand-badge {
            font-size: 0.65rem;
            font-family: var(--font-mono);
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            display: block;
        }

        /* User Role Pill inside Sidebar */
        .sidebar-user-role {
            margin: 14px 16px 8px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-user-role .role-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        .sidebar-user-role .role-pill {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Nav links */
        .sidebar-nav {
            padding: 12px 10px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section-title {
            font-size: 0.65rem;
            font-family: var(--font-mono);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 16px 12px 6px;
            font-weight: 600;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 7px;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .sidebar-link i {
            font-size: 0.95rem;
            width: 18px;
            text-align: center;
            color: #64748b;
            transition: color 0.15s ease;
        }

        .sidebar-link:hover {
            color: #f8fafc;
            background: var(--sidebar-item-hover);
        }

        .sidebar-link:hover i {
            color: #38bdf8;
        }

        .sidebar-link.active {
            color: #ffffff;
            background: var(--sidebar-item-active);
            font-weight: 600;
        }

        .sidebar-link.active i {
            color: #38bdf8;
        }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #64748b;
        }

        /* -------------------------------------------------------------
           Main Content Canvas & Top Header
        ------------------------------------------------------------- */
        #main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s ease;
        }

        #sidebarBackdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(11, 19, 36, 0.65);
            backdrop-filter: blur(4px);
            z-index: 1035;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            }
            body.sidebar-open #sidebar {
                transform: translateX(0);
            }
            body.sidebar-open #sidebarBackdrop {
                display: block;
            }
            #main-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .top-header {
                padding: 0 16px !important;
            }
            .content-canvas {
                padding: 16px 14px 40px !important;
            }
        }

        .top-header {
            height: 60px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-subtle);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .telemetry-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            font-weight: 500;
            color: #059669;
            background: #ecfdf5;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #a7f3d0;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .content-canvas {
            padding: 28px 32px 48px;
            flex: 1;
        }

        /* -------------------------------------------------------------
           Corporate Minimalism Components
        ------------------------------------------------------------- */
        /* Clean Flat Cards */
        .card-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-subtle);
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-panel-header h5,
        .card-panel-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-heading);
            letter-spacing: -0.01em;
        }

        .card-panel-body {
            padding: 20px;
        }

        /* KPI Metric Blocks */
        .kpi-tile {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            transition: border-color 0.2s ease, transform 0.2s ease;
            position: relative;
        }

        .kpi-tile:hover {
            border-color: var(--border-strong);
            transform: translateY(-1px);
        }

        .kpi-tile .kpi-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .kpi-tile .kpi-value {
            font-size: 1.65rem;
            font-weight: 700;
            color: var(--text-heading);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .kpi-tile .kpi-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Minimalist Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .status-active, .status-available, .status-completed {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-in_transit, .status-on_trip, .status-dispatched {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .status-maintenance {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-scheduled {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .status-out_of_service, .status-cancelled {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Minimalist Table */
        .table-minimal {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-minimal thead th {
            background: #f8fafc;
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            padding: 12px 18px;
            border-bottom: 1px solid var(--border-subtle);
            border-top: none;
        }

        .table-minimal tbody td {
            padding: 14px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.86rem;
            color: var(--text-body);
        }

        .table-minimal tbody tr:last-child td {
            border-bottom: none;
        }

        .table-minimal tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Clean action buttons */
        .btn-corp {
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            padding: 7px 14px;
            border-radius: 7px;
            transition: all 0.15s ease;
        }

        .btn-corp-primary {
            background: var(--text-heading);
            color: #ffffff;
            border: 1px solid var(--text-heading);
        }

        .btn-corp-primary:hover {
            background: #1e293b;
            color: #ffffff;
        }

        .btn-corp-secondary {
            background: #ffffff;
            color: var(--text-body);
            border: 1px solid var(--border-subtle);
        }

        .btn-corp-secondary:hover {
            background: #f8fafc;
            border-color: var(--border-strong);
            color: var(--text-heading);
        }
    </style>
    <!-- Leaflet JS & Chart.js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php
        $userRole = session()->get('user_role') ?? 'dispatcher';
        $userName = session()->get('user_name') ?? 'System User';
        $userEmail = session()->get('user_email') ?? 'user@fleet.com';

        // In-app notification badge (BRD flows always write a notification)
        $notifUnread = 0;
        try {
            $notifUnread = (int) (new \App\Models\NotificationModel())
                ->getUnreadCount((int) session()->get('user_id'));
        } catch (\Throwable $e) {
            $notifUnread = 0;
        }

        // Role aesthetic configurations for all 7 Government RBAC roles
        $roleBadges = [
            'admin'          => ['label' => 'Super Admin / Director', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.15)'],
            'requestor'      => ['label' => 'Staff / Requestor', 'color' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.15)'],
            'approver_oic'   => ['label' => 'Tier 1 Approver (OIC)', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.15)'],
            'approver_admin' => ['label' => 'Tier 2 Approver (Admin Head)', 'color' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.15)'],
            'dispatcher'     => ['label' => 'Motorpool Dispatcher', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.15)'],
            'guard'          => ['label' => 'Gate Security Officer', 'color' => '#64748b', 'bg' => 'rgba(100, 116, 139, 0.15)'],
            'auditor'        => ['label' => 'COA Resident Auditor', 'color' => '#d97706', 'bg' => 'rgba(217, 119, 6, 0.15)'],
            'driver'         => ['label' => 'Official Driver', 'color' => '#0284c7', 'bg' => 'rgba(2, 132, 199, 0.15)'],
            'maintenance'    => ['label' => 'Safety & PMS Tech', 'color' => '#ea580c', 'bg' => 'rgba(234, 88, 12, 0.15)'],
        ];
        $currentBadge = $roleBadges[$userRole] ?? ['label' => ucfirst($userRole), 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.15)'];
    ?>

    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <!-- Brand Header -->
        <div class="sidebar-brand">
            <a href="<?= base_url('/') ?>" class="brand-logo">
                <div class="brand-icon" style="background: linear-gradient(135deg, #0A2540, #1e3a8a);">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div>
                    <div class="brand-title">PIA <span class="text-primary-accent" style="color: #38bdf8;">Motorpool</span></div>
                    <span class="brand-badge">Republic of the Philippines</span>
                </div>
            </a>
        </div>

        <!-- Role Pill -->
        <div class="sidebar-user-role">
            <span class="role-label"><i class="fa-solid fa-shield-halved me-1 text-muted"></i> Role</span>
            <span class="role-pill" style="color: <?= $currentBadge['color'] ?>; background: <?= $currentBadge['bg'] ?>;">
                <?= esc($currentBadge['label']) ?>
            </span>
        </div>

        <!-- Dynamic Role-Tailored Navigation (Eliminates Redundancy) -->
        <nav class="sidebar-nav">
            <!-- 1. OFFICIAL TRAVEL WORKFLOW (BRD & SDD) -->
            <div class="nav-section-title">Official Travel Workflow</div>

            <?php if (in_array($userRole, ['admin', 'dispatcher', 'requestor', 'approver_oic', 'approver_admin', 'auditor'])): ?>
                <a href="<?= base_url('requests') ?>" class="sidebar-link <?= strpos(uri_string(), 'requests') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-signature"></i> Vehicle Requests (VRS)
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'approver_oic', 'approver_admin', 'dispatcher'])): ?>
                <a href="<?= base_url('approvals') ?>" class="sidebar-link <?= strpos(uri_string(), 'approvals') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-stamp"></i> Approvals & 4h Escalation
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'dispatcher'])): ?>
                <a href="<?= base_url('dispatch') ?>" class="sidebar-link <?= strpos(uri_string(), 'dispatch') === 0 && strpos(uri_string(), 'calendar') === false ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck-fast"></i> Dispatch Console
                </a>
                <a href="<?= base_url('dispatch/calendar') ?>" class="sidebar-link <?= strpos(uri_string(), 'dispatch/calendar') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-calendar-days"></i> Allocation Calendar
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'dispatcher', 'driver', 'guard', 'auditor'])): ?>
                <a href="<?= base_url('tickets') ?>" class="sidebar-link <?= strpos(uri_string(), 'tickets') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-ticket"></i> Driver's Trip Tickets (e-DTT)
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'guard', 'dispatcher'])): ?>
                <a href="<?= base_url('gate') ?>" class="sidebar-link <?= strpos(uri_string(), 'gate') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-shield-halved"></i> Gate QR Checkpoint
                </a>
            <?php endif; ?>

            <!-- Module 5 — Pre-Trip Safety (BLOWBAGETS) & Mechanic PIR -->
            <?php if (in_array($userRole, ['admin', 'dispatcher', 'driver', 'auditor'])): ?>
                <a href="<?= base_url('safety') ?>" class="sidebar-link <?= strpos(uri_string(), 'safety') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-clipboard-check"></i> BLOWBAGETS Safety Checks
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'dispatcher', 'maintenance'])): ?>
                <a href="<?= base_url('pir') ?>" class="sidebar-link <?= strpos(uri_string(), 'pir') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Mechanic PIR Queue
                </a>
            <?php endif; ?>

            <!-- Module 6 — Tollway RFID -->
            <?php if (in_array($userRole, ['admin', 'dispatcher', 'auditor'])): ?>
                <a href="<?= base_url('rfid') ?>" class="sidebar-link <?= strpos(uri_string(), 'rfid') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-credit-card"></i> Tollway RFID Cards
                </a>
            <?php endif; ?>

            <?php if (in_array($userRole, ['admin', 'auditor'])): ?>
                <a href="<?= base_url('audit') ?>" class="sidebar-link <?= uri_string() === 'audit' ? 'active' : '' ?>">
                    <i class="fa-solid fa-scale-balanced"></i> COA Compliance & Audits
                </a>
                <!-- Module 7 — COA & Government Compliance Portal -->
                <a href="<?= base_url('compliance') ?>" class="sidebar-link <?= strpos(uri_string(), 'compliance') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-shield"></i> Compliance Portal (Form B)
                </a>
                <a href="<?= base_url('audit/logs') ?>" class="sidebar-link <?= strpos(uri_string(), 'audit/logs') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i> System Audit Trail
                </a>
            <?php endif; ?>

            <!-- 2. FLEET ASSETS & TELEMETRY -->
            <?php if (in_array($userRole, ['admin', 'dispatcher', 'maintenance'])): ?>
                <div class="nav-section-title">Fleet Assets & Infrastructure</div>
                <a href="<?= base_url('dashboard') ?>" class="sidebar-link <?= uri_string() === '' || uri_string() === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> Command Center
                </a>
                <a href="<?= base_url('vehicles') ?>" class="sidebar-link <?= strpos(uri_string(), 'vehicles') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck"></i> Vehicle Fleet (5K PMS)
                </a>
                <a href="<?= base_url('drivers') ?>" class="sidebar-link <?= strpos(uri_string(), 'drivers') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-id-card"></i> Driver Personnel
                </a>
                <a href="<?= base_url('tracking') ?>" class="sidebar-link <?= uri_string() === 'tracking' ? 'active' : '' ?>">
                    <i class="fa-solid fa-satellite-dish"></i> Live GPS Radar
                </a>
                <a href="<?= base_url('fuel') ?>" class="sidebar-link <?= strpos(uri_string(), 'fuel') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-gas-pump"></i> Fuel Records
                </a>
                <a href="<?= base_url('maintenance') ?>" class="sidebar-link <?= strpos(uri_string(), 'maintenance') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-wrench"></i> Service Orders & PMS
                </a>
            <?php endif; ?>

            <!-- 3. MOBILE PWA FOR DRIVER -->
            <?php if (in_array($userRole, ['admin', 'dispatcher', 'driver'])): ?>
                <div class="nav-section-title">Mobile Operations</div>
                <a href="<?= base_url('driver/trips') ?>" target="_blank" class="sidebar-link">
                    <i class="fa-solid fa-mobile-screen"></i> Driver Mobile PWA <i class="fa-solid fa-arrow-up-right-from-square ms-auto text-muted small"></i>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <span class="mono"><i class="fa-solid fa-shield-halved text-success me-1"></i> GovTech Secured</span>
            <span class="mono">v2.0</span>
        </div>
    </aside>
    <div id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <div id="main-wrapper">
        <!-- Top Header Bar -->
        <header class="top-header">
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none px-2 py-1" id="sidebarToggle" aria-label="Toggle Sidebar Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="telemetry-pulse">
                    <div class="pulse-dot"></div>
                    <span>Telematics Online</span>
                </div>
                <span class="text-muted small d-none d-md-inline">|</span>
                <span class="badge bg-white text-dark border d-none d-lg-inline-flex align-items-center gap-1 shadow-sm" style="font-size: 0.72rem; padding: 4px 8px;" title="Active Security: CSRF Enabled, Secure Headers (SAMEORIGIN, nosniff), Rate-Limited Auth, RBAC Enforced">
                    <i class="fa-solid fa-shield-halved text-success"></i> Secured System (CSRF &bull; RBAC &bull; Rate-Limit)
                </span>
            </div>

            <!-- Profile & Actions Dropdown -->
            <div class="d-flex align-items-center gap-2">
                <!-- In-App Notification Bell -->
                <a href="<?= base_url('notifications') ?>" class="btn btn-corp-secondary position-relative d-flex align-items-center gap-2 py-1 px-3 shadow-sm" title="My Notifications">
                    <i class="fa-solid fa-bell text-primary"></i>
                    <span class="small fw-semibold d-none d-sm-inline">Alerts</span>
                    <?php if ($notifUnread > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.58rem;">
                            <?= $notifUnread > 99 ? '99+' : $notifUnread ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- System Guide & User Manual Trigger -->
                <button type="button" class="btn btn-corp-secondary d-flex align-items-center gap-2 py-1 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#systemGuideModal" title="Open Complete System Operations & Security Guide">
                    <i class="fa-solid fa-book-open text-primary"></i>
                    <span class="small fw-semibold d-none d-sm-inline">System Manual</span>
                </button>

                <div class="dropdown">
                    <button class="btn btn-corp-secondary d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user text-primary-accent" style="color: #2563eb;"></i>
                        <span class="small fw-semibold"><?= esc($userName) ?></span>
                        <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-1">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <strong class="small d-block text-dark"><?= esc($userName) ?></strong>
                                <span class="small text-muted font-monospace"><?= esc($userEmail) ?></span>
                            </div>
                        </li>
                        <li>
                            <div class="px-3 py-1 text-muted small">
                                Role: <strong class="text-dark"><?= ucfirst(esc($userRole)) ?></strong>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item small py-2" href="#" data-bs-toggle="modal" data-bs-target="#systemGuideModal">
                                <i class="fa-solid fa-circle-question me-2 text-primary"></i> System Guide & Cheatsheet
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item small py-2" href="<?= base_url('mfa/setup') ?>">
                                <i class="fa-solid fa-shield-halved me-2 text-success"></i> Two-Factor Authentication (MFA)
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger small py-2" href="<?= base_url('logout') ?>">
                                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Notifications (Global) -->
        <div class="px-4 pt-3 pb-0">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show p-3 rounded-2 border-0 shadow-sm d-flex align-items-center" role="alert" style="background: #f0fdf4; color: #166534; border-left: 4px solid #16a34a !important;">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                    <div><?= session()->getFlashdata('success') ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show p-3 rounded-2 border-0 shadow-sm d-flex align-items-center" role="alert" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rendered Page Content -->
        <main class="content-canvas">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- System Operations & Security Architecture Manual Modal -->
    <div class="modal fade" id="systemGuideModal" tabindex="-1" aria-labelledby="systemGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-subtle); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <!-- Modal Header -->
                <div class="modal-header border-bottom py-3 px-4" style="background: #f8fafc;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 40px; height: 40px; border-radius: 8px; background: #0f172a; color: #ffffff; display: grid; place-items: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="systemGuideModalLabel">FleetPulse Operations & Security Manual</h5>
                            <span class="small text-muted font-monospace">Architecture, Operational Workflows & Active Security Protocols</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Navigation Tabs -->
                <div class="border-bottom px-4 pt-2" style="background: #f8fafc;">
                    <ul class="nav nav-tabs border-0" id="guideTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold small" id="workflows-tab" data-bs-toggle="tab" data-bs-target="#tab-workflows" type="button" role="tab">
                                <i class="fa-solid fa-diagram-project me-1 text-primary"></i> 1. Core Lifecycle & Modules
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold small" id="roles-tab" data-bs-toggle="tab" data-bs-target="#tab-roles" type="button" role="tab">
                                <i class="fa-solid fa-users-gear me-1 text-success"></i> 2. Roles & Access Matrix
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold small" id="security-tab" data-bs-toggle="tab" data-bs-target="#tab-security" type="button" role="tab">
                                <i class="fa-solid fa-shield-halved me-1 text-danger"></i> 3. Security Safeguards
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold small" id="radar-tab" data-bs-toggle="tab" data-bs-target="#tab-radar" type="button" role="tab">
                                <i class="fa-brands fa-google me-1 text-info"></i> 4. Google Maps & Radar
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold small" id="api-tab" data-bs-toggle="tab" data-bs-target="#tab-api" type="button" role="tab">
                                <i class="fa-solid fa-terminal me-1 text-warning"></i> 5. REST & IoT APIs
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4" style="font-size: 0.86rem; line-height: 1.6;">
                    <div class="tab-content" id="guideTabsContent">

                        <!-- TAB 1: Core Lifecycle & Modules -->
                        <div class="tab-pane fade show active" id="tab-workflows" role="tabpanel">
                            <div class="alert alert-primary border-0 rounded-2 py-2 px-3 mb-4 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info fs-5"></i>
                                <span>FleetPulse orchestrates commercial freight through a closed-loop verified workflow from asset allocation to trip arrival.</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">Module 01</span>
                                            <strong class="text-dark">Vehicle Fleet Registry</strong>
                                        </div>
                                        <p class="text-muted small mb-2">Maintains complete records of all transport assets, including VIN, plate number, fuel tank capacity, current odometer, and PMS thresholds.</p>
                                        <ul class="small text-muted ps-3 mb-0">
                                            <li><strong>Statuses:</strong> Active, In Transit, Maintenance, Out of Service.</li>
                                            <li>Tracks engine status, battery voltage, and coolant temperature.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">Module 02</span>
                                            <strong class="text-dark">Driver Personnel & Compliance</strong>
                                        </div>
                                        <p class="text-muted small mb-2">Manages certified operators, heavy-vehicle licenses, emergency contacts, and algorithmic safety scoring.</p>
                                        <ul class="small text-muted ps-3 mb-0">
                                            <li><strong>Safety Scoring:</strong> Penalizes overspeeding incidents (>80 km/h) automatically.</li>
                                            <li>Prevents dispatching drivers with expired licenses or unverified status.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">Module 03</span>
                                            <strong class="text-dark">Trip Dispatch & Waybills</strong>
                                        </div>
                                        <p class="text-muted small mb-2">Assigns truck and driver to a corridor with auto-calculated OSRM road distance, scheduled departure, and cargo tracking.</p>
                                        <ul class="small text-muted ps-3 mb-0">
                                            <li><strong>Anti-Fraud Odometer Lock:</strong> At trip completion, arrival odometer is logged and cross-verified against route distance.</li>
                                            <li>Dispatches alert drivers via simulated SMS transmission.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">Module 04</span>
                                            <strong class="text-dark">Technical Shop & Maintenance (PMS)</strong>
                                        </div>
                                        <p class="text-muted small mb-2">Automates preventive maintenance intervals (every 10,000 km) and captures real-time OBD-II vehicle diagnostic error codes.</p>
                                        <ul class="small text-muted ps-3 mb-0">
                                            <li><strong>OBD-II Alerts:</strong> Engine fault codes (e.g. P0420) instantly trigger high-priority work orders.</li>
                                            <li>Completing service recalibrates the next maintenance threshold.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">Module 05</span>
                                            <strong class="text-dark">Fuel Logging & Financial Audits</strong>
                                        </div>
                                        <p class="text-muted small mb-1">Captures fuel volume (liters), cost (â‚±), odometer readings, and receipt image uploads. Computes the executive Fleet Cost per Kilometer metric (Total Operating Expenses &divide; Total Fleet Distance) to identify operational wastage.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Roles & Permissions (RBAC) -->
                        <div class="tab-pane fade" id="tab-roles" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr class="small text-uppercase">
                                            <th>Role</th>
                                            <th>Command Center</th>
                                            <th>Vehicles & Drivers</th>
                                            <th>Trips Dispatch</th>
                                            <th>GPS Radar</th>
                                            <th>Maintenance PMS</th>
                                            <th>Financial Reports</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr>
                                            <td>
                                                <span class="badge" style="background:#3b82f6;">Administrator</span>
                                                <div class="text-muted" style="font-size:0.7rem;">Chief Fleet Director</div>
                                            </td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Full</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background:#10b981;">Dispatcher</span>
                                                <div class="text-muted" style="font-size:0.7rem;">Logistics Coordinator</div>
                                            </td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> View</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Manage</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Create / Complete</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Live Stream</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> No Access</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> No Access</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background:#f59e0b;">Maintenance</span>
                                                <div class="text-muted" style="font-size:0.7rem;">Workshop Supervisor</div>
                                            </td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Telematics</td>
                                            <td class="text-primary text-center"><i class="fa-solid fa-eye"></i> View Only</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> No Access</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Telematics</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Work Orders & OBD</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> No Access</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background:#06b6d4;">Driver</span>
                                                <div class="text-muted" style="font-size:0.7rem;">Commercial Operator</div>
                                            </td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> PWA Portal</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> Own Truck</td>
                                            <td class="text-success text-center"><i class="fa-solid fa-check"></i> Start / End</td>
                                            <td class="text-primary text-center"><i class="fa-solid fa-location-arrow"></i> GPS Ping</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> Report Incident</td>
                                            <td class="text-muted text-center"><i class="fa-solid fa-minus"></i> Submit Receipt</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 3: Security Safeguards -->
                        <div class="tab-pane fade" id="tab-security" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-shield-halved text-success fs-5"></i>
                                            <strong class="text-dark">CSRF Defense Filter</strong>
                                        </div>
                                        <p class="text-muted small mb-0">Every web form generates a cryptographic, randomized one-time token. Submissions without a valid token are rejected, preventing Cross-Site Request Forgery attacks.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-stopwatch text-danger fs-5"></i>
                                            <strong class="text-dark">Brute-Force Rate Limiting</strong>
                                        </div>
                                        <p class="text-muted small mb-0">Login endpoints are throttled to a maximum of 5 attempts per minute per IP address. Exceeding attempts triggers a timed lockout countdown to prevent dictionary attacks.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-cookie-bite text-warning fs-5"></i>
                                            <strong class="text-dark">Session Fixation & Cookie Hardening</strong>
                                        </div>
                                        <p class="text-muted small mb-0">Session IDs are automatically regenerated upon login (`session->regenerate(true)`). Session cookies are marked <code>HttpOnly</code> and <code>SameSite=Lax</code> to prevent XSS session hijacking.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa-solid fa-server text-primary fs-5"></i>
                                            <strong class="text-dark">Secure HTTP Headers</strong>
                                        </div>
                                        <p class="text-muted small mb-0">Active responses include <code>X-Frame-Options: SAMEORIGIN</code> to prevent Clickjacking, and <code>X-Content-Type-Options: nosniff</code> to block MIME-type sniffing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: Google Maps & Radar -->
                        <div class="tab-pane fade" id="tab-radar" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <strong class="text-dark d-block mb-1"><i class="fa-solid fa-traffic-light text-warning me-1"></i> Google Live Traffic</strong>
                                        <p class="text-muted small mb-0">Clicking <strong>Traffic: ON</strong> displays Google's real-time color-coded congestion overlay across major corridors, allowing dispatchers to reroute drivers around traffic jams.</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <strong class="text-dark d-block mb-1"><i class="fa-solid fa-route text-primary me-1"></i> GPS Breadcrumb Trails</strong>
                                        <p class="text-muted small mb-0">Selecting any vehicle draws its historical movement trajectory line and waypoint nodes, revealing recent speed, heading, and corridor adherence.</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 border rounded-2 bg-white">
                                        <strong class="text-dark d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-1"></i> Geofence Resolution</strong>
                                        <p class="text-muted small mb-0">Geographic circular fences (e.g. Manila Harbor, Clark Distribution Hub) automatically detect vehicle presence and record arrival and departure times.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: REST & IoT APIs -->
                        <div class="tab-pane fade" id="tab-api" role="tabpanel">
                            <p class="text-muted small mb-3">External GPS transponders, mobile tracking devices, and third-party enterprise tools can query the FleetPulse REST API:</p>

                            <div class="bg-dark text-light p-3 rounded font-monospace small mb-2" style="font-size:0.75rem;">
                                <div class="text-info mb-1"># 1. Fetch Real-Time Fleet Map with GeoJSON Collection:</div>
                                <div>GET http://localhost:8090/api/v1/tracking</div>

                                <div class="text-info mt-3 mb-1"># 2. Ingest GPS Transponder Coordinate:</div>
                                <div>POST http://localhost:8090/api/v1/gps/ping</div>
                                <div class="text-muted">{ "vehicle_id": 1, "latitude": 14.5995, "longitude": 120.9842, "speed_kmh": 65.0, "fuel_level": 88.0 }</div>

                                <div class="text-info mt-3 mb-1"># 3. Calculate Road Route Corridor:</div>
                                <div>GET http://localhost:8090/api/v1/route?origin_lat=14.58&origin_lng=120.96&dest_lat=15.18&dest_lng=120.54</div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top py-2 px-4" style="background: #f8fafc;">
                    <span class="small text-muted me-auto"><i class="fa-solid fa-circle-check text-success me-1"></i> System Status: All 5 Security Layers & Telematics Active</span>
                    <button type="button" class="btn btn-sm btn-corp-primary px-3" data-bs-dismiss="modal">Close Manual</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                document.body.classList.toggle('sidebar-open');
            });
        }
        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function () {
                document.body.classList.remove('sidebar-open');
            });
        }
    });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
