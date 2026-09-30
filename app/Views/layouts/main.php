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

        // Role aesthetic configurations
        $roleBadges = [
            'admin'       => ['label' => 'Administrator', 'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.15)'],
            'dispatcher'  => ['label' => 'Dispatch Lead', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.15)'],
            'maintenance' => ['label' => 'Technical Shop', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.15)'],
            'driver'      => ['label' => 'Driver Operator', 'color' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.15)'],
        ];
        $currentBadge = $roleBadges[$userRole] ?? $roleBadges['dispatcher'];
    ?>

    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <!-- Brand Header -->
        <div class="sidebar-brand">
            <a href="<?= base_url('/') ?>" class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <div>
                    <div class="brand-title">Fleet<span class="text-primary-accent" style="color: #38bdf8;">Pulse</span></div>
                    <span class="brand-badge">Enterprise Logistics</span>
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
            <?php if ($userRole === 'admin' || $userRole === 'dispatcher'): ?>
                <div class="nav-section-title">Fleet Monitoring</div>
                <a href="<?= base_url('dashboard') ?>" class="sidebar-link <?= uri_string() === '' || uri_string() === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-line"></i> Command Center
                </a>
                <a href="<?= base_url('tracking') ?>" class="sidebar-link <?= uri_string() === 'tracking' ? 'active' : '' ?>">
                    <i class="fa-solid fa-satellite-dish"></i> Live GPS Radar
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'admin' || $userRole === 'dispatcher'): ?>
                <div class="nav-section-title">Logistics & Dispatches</div>
                <a href="<?= base_url('trips') ?>" class="sidebar-link <?= strpos(uri_string(), 'trips') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-route"></i> Trip Dispatches
                </a>
                <a href="<?= base_url('vehicles') ?>" class="sidebar-link <?= strpos(uri_string(), 'vehicles') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck"></i> Vehicle Fleet
                </a>
                <a href="<?= base_url('drivers') ?>" class="sidebar-link <?= strpos(uri_string(), 'drivers') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-id-card"></i> Driver Personnel
                </a>
                <a href="<?= base_url('fuel') ?>" class="sidebar-link <?= strpos(uri_string(), 'fuel') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-gas-pump"></i> Fuel Records
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'maintenance'): ?>
                <div class="nav-section-title">Technical Service</div>
                <a href="<?= base_url('maintenance') ?>" class="sidebar-link <?= strpos(uri_string(), 'maintenance') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-wrench"></i> Service Orders & PMS
                </a>
                <a href="<?= base_url('vehicles') ?>" class="sidebar-link <?= strpos(uri_string(), 'vehicles') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck"></i> Vehicle Asset Health
                </a>
                <a href="<?= base_url('tracking') ?>" class="sidebar-link <?= uri_string() === 'tracking' ? 'active' : '' ?>">
                    <i class="fa-solid fa-satellite-dish"></i> Telematics & Diagnostics
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'admin'): ?>
                <div class="nav-section-title">Garage & Intelligence</div>
                <a href="<?= base_url('maintenance') ?>" class="sidebar-link <?= strpos(uri_string(), 'maintenance') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-wrench"></i> Work Orders & PMS
                </a>
                <a href="<?= base_url('reports') ?>" class="sidebar-link <?= strpos(uri_string(), 'reports') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice"></i> Cost & Analytics
                </a>
            <?php endif; ?>

            <?php if ($userRole === 'admin' || $userRole === 'dispatcher'): ?>
                <div class="nav-section-title">Mobile Portal</div>
                <a href="<?= base_url('driver/trips') ?>" target="_blank" class="sidebar-link">
                    <i class="fa-solid fa-mobile-screen"></i> Driver Mobile PWA <i class="fa-solid fa-arrow-up-right-from-square ms-auto text-muted small"></i>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <span class="mono"><i class="fa-solid fa-database text-success me-1"></i> MySQL 8</span>
            <span class="mono">v1.1</span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="main-wrapper">
        <!-- Top Header Bar -->
        <header class="top-header">
            <div class="d-flex align-items-center gap-3">
                <div class="telemetry-pulse">
                    <div class="pulse-dot"></div>
                    <span>Telematics Online</span>
                </div>
                <span class="text-muted small d-none d-md-inline">|</span>
                <span class="small text-muted font-monospace d-none d-md-inline">SLA: 99.98% Available</span>
            </div>

            <!-- Profile & Session Dropdown -->
            <div class="d-flex align-items-center gap-3">
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

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
