<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Driver Mobile Portal | FleetPulse PWA</title>
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0b1324;
            color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding-bottom: 80px;
            -webkit-font-smoothing: antialiased;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .driver-header {
            background: #1e293b;
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .trip-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
        }
        .bottom-nav button {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .bottom-nav button.active, .bottom-nav button:hover {
            color: #38bdf8;
        }
        .route-stop {
            position: relative;
            padding-left: 24px;
        }
        .route-stop::before {
            content: '';
            position: absolute;
            left: 6px;
            top: 6px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #38bdf8;
        }
        .route-stop.dest::before {
            background: #ef4444;
        }
        .route-stop::after {
            content: '';
            position: absolute;
            left: 10px;
            top: 18px;
            width: 2px;
            height: calc(100% - 6px);
            background: rgba(255,255,255,0.2);
        }
        .route-stop:last-child::after {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Top Header -->
    <div class="driver-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary p-2 text-white">
                <i class="fa-solid fa-truck-steering-wheel"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-white"><?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></h6>
                <span class="small text-muted font-monospace"><?= esc($driver['driver_code']) ?> &bull; Score: <?= $driver['safety_score'] ?>%</span>
            </div>
        </div>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>

    <!-- Main Container -->
    <div class="container py-3">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show p-3 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show p-3 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Assigned Truck Card -->
        <?php if ($vehicle): ?>
            <div class="alert bg-primary bg-opacity-20 border border-primary border-opacity-25 text-white mb-3 p-3 rounded-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="small text-info text-uppercase fw-bold">Assigned Vehicle</span>
                        <h5 class="fw-bold mb-0"><?= esc($vehicle['vehicle_code']) ?> &bull; <?= esc($vehicle['plate_number']) ?></h5>
                        <span class="small text-white-50"><?= esc($vehicle['make'] . ' ' . $vehicle['model']) ?></span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success fs-6"><?= $vehicle['current_fuel_level'] ?>% Fuel</span>
                        <div class="small text-white-50 mt-1"><?= number_format($vehicle['odometer_km'], 1) ?> km</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Action Buttons -->
        <div class="d-grid gap-2 d-flex mb-4">
            <button class="btn btn-outline-info flex-grow-1 py-2" data-bs-toggle="modal" data-bs-target="#fuelModal">
                <i class="fa-solid fa-gas-pump me-1"></i> Log Fuel Receipt
            </button>
            <button class="btn btn-outline-warning flex-grow-1 py-2" data-bs-toggle="modal" data-bs-target="#incidentModal">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Report Issue
            </button>
        </div>

        <h6 class="text-uppercase small text-muted fw-bold mb-3">Assigned Trip Waybills (<?= count($trips) ?>)</h6>

        <?php if (empty($trips)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-calendar-check fs-1 mb-2"></i>
                <p>No active trips assigned at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($trips as $t): ?>
                <div class="trip-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-info"><?= esc($t['trip_number']) ?></span>
                        <span class="badge bg-<?= $t['status'] === 'in_transit' ? 'success' : ($t['status'] === 'completed' ? 'secondary' : 'primary') ?>">
                            <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="route-stop pb-2">
                            <span class="small text-muted d-block">Origin (Pickup)</span>
                            <strong class="small text-white"><?= esc($t['origin_address']) ?></strong>
                        </div>
                        <div class="route-stop dest">
                            <span class="small text-muted d-block">Destination (Delivery)</span>
                            <strong class="small text-white"><?= esc($t['destination_address']) ?></strong>
                        </div>
                    </div>

                    <div class="bg-black bg-opacity-25 p-2 rounded small text-white-50 mb-3 d-flex justify-content-between">
                        <span><strong>Cargo:</strong> <?= esc($t['cargo_type']) ?></span>
                        <span><strong>Weight:</strong> <?= number_format($t['cargo_weight_kg'], 0) ?> kg</span>
                        <span><strong>Distance:</strong> <?= $t['distance_km'] ?> km</span>
                    </div>

                    <!-- Action buttons based on status -->
                    <div class="d-grid gap-2">
                        <?php if ($t['status'] === 'scheduled' || $t['status'] === 'dispatched'): ?>
                            <form action="<?= base_url('trips/' . $t['id'] . '/status') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="in_transit">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                                    <i class="fa-solid fa-play me-1"></i> Start Trip (Depart)
                                </button>
                            </form>
                        <?php elseif ($t['status'] === 'in_transit'): ?>
                            <button class="btn btn-primary w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#completeModal-<?= $t['id'] ?>">
                                <i class="fa-solid fa-circle-check me-1"></i> Arrived / Complete Trip
                            </button>

                            <!-- Complete Trip Modal -->
                            <div class="modal fade text-dark" id="completeModal-<?= $t['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="<?= base_url('trips/' . $t['id'] . '/status') ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="completed">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Complete Trip: <?= esc($t['trip_number']) ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Arrival Odometer Reading (km)</label>
                                                    <input type="number" step="0.1" name="end_odometer" class="form-control" required value="<?= $vehicle['odometer_km'] ?? 0 ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Confirm Arrival</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-center text-muted small py-1"><i class="fa-solid fa-check-double text-success me-1"></i> Completed</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Fuel Receipt Modal -->
    <div class="modal fade text-dark" id="fuelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= base_url('api/v1/driver/fuel') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?? 1 ?>">
                    <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold"><i class="fa-solid fa-gas-pump text-primary me-2"></i> Log Fuel & Upload Receipt</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Upload Photo of Receipt</label>
                            <input type="file" name="receipt_image" accept="image/*" class="form-control" capture="environment">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Volume (Liters)</label>
                                <input type="number" step="0.01" name="liters" class="form-control" required placeholder="0.00">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Total Cost (₱)</label>
                                <input type="number" step="0.01" name="total_cost" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Current Odometer (km)</label>
                            <input type="number" step="0.1" name="odometer_km" class="form-control" value="<?= $vehicle['odometer_km'] ?? 0 ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Gas Station Name</label>
                            <input type="text" name="fuel_station" class="form-control" placeholder="e.g. Petron, Shell SLEX">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Incident Modal -->
    <div class="modal fade text-dark" id="incidentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= base_url('driver/incident') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?? 1 ?>">
                    <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Report Incident / Breakdown</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Incident Category</label>
                            <select name="type" class="form-select" required>
                                <option value="Mechanical Breakdown">Mechanical Breakdown</option>
                                <option value="Tire Puncture / Flat">Tire Puncture / Flat</option>
                                <option value="Accident / Collision">Minor Collision / Accident</option>
                                <option value="Traffic Gridlock / Delay">Severe Traffic / Route Blockage</option>
                                <option value="Cargo Damage">Cargo Damage / Shift</option>
                                <option value="Medical Emergency">Driver Medical Issue</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Severity</label>
                            <select name="severity" class="form-select" required>
                                <option value="minor">Minor (Can proceed slowly)</option>
                                <option value="moderate" selected>Moderate (Needs road assistance)</option>
                                <option value="severe">Severe (Immobilized / Tow needed)</option>
                                <option value="critical">Critical (Immediate Emergency)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Current Location / Landmark</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. SLEX KM 34 Northbound near Calamba Exit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Detailed Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Describe what happened, vehicle condition, and immediate assistance required..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fw-bold"><i class="fa-solid fa-paper-plane me-1"></i> Transmit Urgent Alert</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <button class="active">
            <i class="fa-solid fa-route fs-5"></i>
            <span>Trips</span>
        </button>
        <button onclick="window.location.href='<?= base_url('dashboard') ?>'">
            <i class="fa-solid fa-desktop fs-5"></i>
            <span>Portal</span>
        </button>
        <button onclick="window.location.href='<?= base_url('tracking') ?>'">
            <i class="fa-solid fa-map-location-dot fs-5"></i>
            <span>Map</span>
        </button>
    </nav>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
