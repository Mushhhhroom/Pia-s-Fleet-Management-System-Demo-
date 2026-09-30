<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | FleetPulse Enterprise Logistics</title>
    <!-- Inter & JetBrains Mono Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --bg-canvas: #f8fafc;
            --border-subtle: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-canvas);
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-sans);
            color: var(--text-body);
            padding: 24px 16px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .mono {
            font-family: var(--font-mono);
        }

        .login-card {
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 440px;
            padding: 36px 32px;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-heading);
            line-height: 1.15;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            font-family: var(--font-mono);
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .page-heading {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-heading);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .page-subtext {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }

        .input-group-corporate {
            position: relative;
        }

        .input-group-corporate .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
            pointer-events: none;
            z-index: 10;
        }

        .form-control-corporate {
            width: 100%;
            padding: 9px 12px 9px 36px;
            font-size: 0.88rem;
            font-family: var(--font-sans);
            color: var(--text-heading);
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: 7px;
            transition: all 0.15s ease;
        }

        .form-control-corporate:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .btn-corporate-primary {
            width: 100%;
            padding: 10px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            color: #ffffff;
            background: #0f172a;
            border: 1px solid #0f172a;
            border-radius: 7px;
            transition: all 0.15s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-corporate-primary:hover {
            background: #1e293b;
            border-color: #1e293b;
            color: #ffffff;
        }

        .quick-role-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--text-body);
            text-align: left;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .quick-role-btn:hover {
            background: #ffffff;
            border-color: var(--border-strong);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            color: var(--text-heading);
        }

        .role-chip {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 4px;
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
        }

        .quick-role-btn:hover .role-chip {
            border-color: var(--border-strong);
            color: var(--text-heading);
        }

        .alert-corporate {
            padding: 10px 14px;
            border-radius: 7px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-corporate-danger {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
        }

        .alert-corporate-success {
            background: #f0fdf4;
            border: 1px solid #dcfce7;
            color: #166534;
        }

        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 0.74rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="brand-icon">
                <i class="fa-solid fa-shapes"></i>
            </div>
            <div>
                <div class="brand-title">FleetPulse <span style="color: #2563eb; font-weight: 500;">FMS</span></div>
                <div class="brand-subtitle">Enterprise Telematics Portal</div>
            </div>
        </div>

        <div class="page-heading">Sign in to your account</div>
        <div class="page-subtext">Access your role-tailored workspace and real-time fleet operations.</div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-corporate alert-corporate-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-corporate alert-corporate-success" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="email">Work Email</label>
                <div class="input-group-corporate">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" class="form-control-corporate" placeholder="corporate@fleet.com" required value="admin@fleet.com">
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0" for="password">Password</label>
                    <span class="mono" style="font-size: 0.7rem; color: var(--text-muted);">Encrypted</span>
                </div>
                <div class="input-group-corporate">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control-corporate" placeholder="••••••••" required value="admin123">
                </div>
            </div>

            <button type="submit" class="btn-corporate-primary">
                <span>Authenticate Session</span>
                <i class="fa-solid fa-arrow-right" style="font-size: 0.8rem;"></i>
            </button>
        </form>

        <!-- Fast Role Access Chips -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="mono" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); font-weight: 600;">1-Click Role Access</span>
                <span class="badge bg-light text-secondary border mono" style="font-size: 0.65rem;">DEMO PROFILES</span>
            </div>

            <div class="d-flex flex-column gap-2">
                <button type="button" class="quick-role-btn" onclick="setCreds('admin@fleet.com', 'admin123')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-primary" style="font-size: 0.85rem; width: 16px;"></i>
                        <span class="fw-semibold">Administrator</span>
                        <span class="text-muted small">&bull; admin@fleet.com</span>
                    </div>
                    <span class="role-chip">admin123</span>
                </button>

                <button type="button" class="quick-role-btn" onclick="setCreds('dispatcher@fleet.com', 'dispatch123')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-route text-success" style="font-size: 0.85rem; width: 16px;"></i>
                        <span class="fw-semibold">Dispatcher</span>
                        <span class="text-muted small">&bull; dispatcher@fleet.com</span>
                    </div>
                    <span class="role-chip">dispatch123</span>
                </button>

                <button type="button" class="quick-role-btn" onclick="setCreds('maintenance@fleet.com', 'maint123')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-wrench text-warning" style="font-size: 0.85rem; width: 16px;"></i>
                        <span class="fw-semibold">Maintenance</span>
                        <span class="text-muted small">&bull; maintenance@fleet.com</span>
                    </div>
                    <span class="role-chip">maint123</span>
                </button>

                <button type="button" class="quick-role-btn" onclick="setCreds('driver@fleet.com', 'driver123')">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-mobile-screen text-info" style="font-size: 0.85rem; width: 16px;"></i>
                        <span class="fw-semibold">Driver Operator</span>
                        <span class="text-muted small">&bull; driver@fleet.com</span>
                    </div>
                    <span class="role-chip">driver123</span>
                </button>
            </div>
        </div>

        <div class="footer-note">
            <span class="mono">FleetPulse SDD Architecture &bull; CI4 + MySQL 8</span>
        </div>
    </div>

    <script>
        function setCreds(email, pwd) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pwd;
        }
    </script>
</body>
</html>
