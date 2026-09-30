<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Fleet Management System') ?> | FleetPulse</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #3b82f6;
            --body-bg: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            background-color: var(--body-bg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        }

        #sidebar .brand {
            padding: 22px 24px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 14px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        #sidebar .nav-link:hover {
            color: #f8fafc;
            background: var(--sidebar-hover);
        }

        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(59, 130, 246, 0.15);
            border-left-color: var(--sidebar-active);
            font-weight: 600;
        }

        #sidebar .nav-category {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 20px 24px 6px;
            font-weight: 700;
        }

        /* Main Content Container */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-navbar {
            background: #fff;
            height: 64px;
            border-bottom: 1px solid var(--card-border);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 990;
        }

        .content-area {
            padding: 28px;
            flex: 1;
        }

        /* Metric Cards */
        .kpi-card {
            border: 1px solid var(--card-border);
            border-radius: 12px;
            background: #fff;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.06);
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: capitalize;
        }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-in_transit { background: #dbeafe; color: #1d4ed8; }
        .badge-maintenance { background: #fef3c7; color: #b45309; }
        .badge-out_of_service { background: #fee2e2; color: #b91c1c; }
        .badge-available { background: #dcfce7; color: #15803d; }
        .badge-on_trip { background: #dbeafe; color: #1d4ed8; }
        .badge-scheduled { background: #f1f5f9; color: #475569; }
        .badge-dispatched { background: #e0e7ff; color: #4338ca; }
        .badge-completed { background: #dcfce7; color: #15803d; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }

        /* Card custom styling */
        .card-custom {
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            background: #fff;
            overflow: hidden;
        }
        .card-custom .card-header {
            background: #fff;
            border-bottom: 1px solid var(--card-border);
            padding: 16px 20px;
            font-weight: 600;
        }

        /* Leaflet custom map */
        .leaflet-container {
            border-radius: 8px;
            font-family: inherit;
        }
    </style>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside id="sidebar">
        <div class="brand">
            <i class="fa-solid fa-truck-fast text-primary"></i>
            <span>Fleet<span class="text-primary">Pulse</span> FMS</span>
        </div>

        <nav class="mt-2">
            <div class="nav-category">Main Monitoring</div>
            <a href="<?= base_url('/') ?>" class="nav-link <?= uri_string() === '' || uri_string() === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Command Center
            </a>
            <a href="<?= base_url('tracking') ?>" class="nav-link <?= uri_string() === 'tracking' ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i> Live GPS Tracking
            </a>

            <div class="nav-category">Logistics & Operations</div>
            <a href="<?= base_url('trips') ?>" class="nav-link <?= strpos(uri_string(), 'trips') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-route"></i> Trip Dispatch
            </a>
            <a href="<?= base_url('vehicles') ?>" class="nav-link <?= strpos(uri_string(), 'vehicles') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i> Vehicle Fleet
            </a>
            <a href="<?= base_url('drivers') ?>" class="nav-link <?= strpos(uri_string(), 'drivers') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-id-card"></i> Drivers & Operators
            </a>

            <div class="nav-category">Maintenance & Fuel</div>
            <a href="<?= base_url('maintenance') ?>" class="nav-link <?= strpos(uri_string(), 'maintenance') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-screwdriver-wrench"></i> Work Orders & Service
            </a>
            <a href="<?= base_url('fuel') ?>" class="nav-link <?= strpos(uri_string(), 'fuel') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-gas-pump"></i> Fuel & Energy Logs
            </a>

            <div class="nav-category">Analytics & Driver PWA</div>
            <a href="<?= base_url('reports') ?>" class="nav-link <?= strpos(uri_string(), 'reports') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Reports & Analytics
            </a>
            <a href="<?= base_url('driver/trips') ?>" target="_blank" class="nav-link">
                <i class="fa-solid fa-mobile-screen"></i> Driver Mobile PWA <i class="fa-solid fa-arrow-up-right-from-square small ms-auto text-muted"></i>
            </a>
        </nav>

        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="border-top: 1px solid rgba(255,255,255,0.08);">
            <div class="d-flex align-items-center justify-content-between text-white-50 small">
                <div>
                    <i class="fa-solid fa-server text-success me-1"></i> MySQL + CI4
                </div>
                <span class="badge bg-secondary">v4.7.4</span>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><i class="fa-regular fa-clock me-1"></i> Live Operations</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="fa-solid fa-satellite-dish me-1"></i> GPS Feed Online
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user text-primary"></i>
                        <span><?= esc(session()->get('user_name') ?? 'Admin User') ?></span>
                        <span class="badge bg-dark-subtle text-dark"><?= esc(ucfirst(session()->get('user_role') ?? 'admin')) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header"><?= esc(session()->get('user_email') ?? 'admin@fleet.com') ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="content-area">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Validation errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
