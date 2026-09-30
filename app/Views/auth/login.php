<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FleetPulse FMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .login-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: #0f172a;
            color: #fff;
            padding: 32px 30px;
            text-align: center;
        }
        .login-body {
            padding: 32px 30px;
        }
        .btn-quick-login {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            font-size: 0.82rem;
            font-weight: 500;
            color: #334155;
            transition: all 0.2s;
        }
        .btn-quick-login:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-25 mb-3 text-primary fs-3">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h4 class="fw-bold mb-1">Fleet<span class="text-primary">Pulse</span> FMS</h4>
            <p class="text-white-50 small mb-0">Enterprise Logistics & Telematics Portal</p>
        </div>

        <div class="login-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small" role="alert">
                    <i class="fa-solid fa-circle-check me-1"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="name@fleet.com" required value="admin@fleet.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required value="admin123">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Portal
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <div class="text-muted small text-center mb-2 fw-semibold">Demo 1-Click Fast Login:</div>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-quick-login py-1 px-2 text-start d-flex justify-content-between align-items-center" onclick="setCreds('admin@fleet.com', 'admin123')">
                        <span><i class="fa-solid fa-user-shield me-2 text-primary"></i> <strong>Admin</strong> (admin@fleet.com)</span>
                        <span class="badge bg-primary">admin123</span>
                    </button>
                    <button type="button" class="btn btn-quick-login py-1 px-2 text-start d-flex justify-content-between align-items-center" onclick="setCreds('dispatcher@fleet.com', 'dispatch123')">
                        <span><i class="fa-solid fa-route me-2 text-success"></i> <strong>Dispatcher</strong> (dispatcher@fleet.com)</span>
                        <span class="badge bg-secondary">dispatch123</span>
                    </button>
                    <button type="button" class="btn btn-quick-login py-1 px-2 text-start d-flex justify-content-between align-items-center" onclick="setCreds('maintenance@fleet.com', 'maint123')">
                        <span><i class="fa-solid fa-wrench me-2 text-warning"></i> <strong>Maintenance</strong> (maintenance@fleet.com)</span>
                        <span class="badge bg-secondary">maint123</span>
                    </button>
                    <button type="button" class="btn btn-quick-login py-1 px-2 text-start d-flex justify-content-between align-items-center" onclick="setCreds('driver@fleet.com', 'driver123')">
                        <span><i class="fa-solid fa-id-badge me-2 text-info"></i> <strong>Driver (PWA)</strong> (driver@fleet.com)</span>
                        <span class="badge bg-info text-dark">driver123</span>
                    </button>
                </div>
            </div>
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
