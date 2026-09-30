<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold mb-0"><?= esc($vehicle['vehicle_code']) ?> &bull; <?= esc($vehicle['make']) ?> <?= esc($vehicle['model']) ?></h3>
            <span class="badge-status badge-<?= esc($vehicle['status']) ?>"><?= ucfirst(str_replace('_', ' ', esc($vehicle['status']))) ?></span>
        </div>
        <p class="text-muted mb-0">License Plate: <strong><?= esc($vehicle['plate_number']) ?></strong> | VIN: <span class="font-monospace"><?= esc($vehicle['vin']) ?></span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('vehicles/' . $vehicle['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Vehicle
        </a>
        <form action="<?= base_url('vehicles/' . $vehicle['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to remove this vehicle from the fleet?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash me-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Quick Specs -->
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Odometer</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= number_format($vehicle['odometer_km'], 1) ?> <span class="fs-6 font-normal">km</span></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Fuel / Energy</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $vehicle['current_fuel_level'] ?>%</h4>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-<?= $vehicle['current_fuel_level'] > 30 ? 'success' : 'danger' ?>" style="width: <?= $vehicle['current_fuel_level'] ?>%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Current Speed</span>
            <h4 class="fw-bold text-primary mt-1 mb-0"><?= $vehicle['current_speed'] ?> <span class="fs-6 font-normal">km/h</span></h4>
            <span class="small text-muted text-capitalize">Engine: <?= esc($vehicle['engine_status']) ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Assigned Driver</span>
            <h5 class="fw-bold text-dark mt-1 mb-0"><?= esc($vehicle['driver_name'] ?: 'None') ?></h5>
            <span class="small text-muted"><?= esc($vehicle['driver_phone'] ?: 'N/A') ?></span>
        </div>
    </div>
</div>

<!-- Tabs for History -->
<div class="card-custom">
    <div class="card-header border-bottom-0 pb-0">
        <ul class="nav nav-tabs border-bottom-0" id="vehicleTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tripsTab">
                    <i class="fa-solid fa-route me-1"></i> Trip History (<?= count($trips) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#maintenanceTab">
                    <i class="fa-solid fa-screwdriver-wrench me-1"></i> Maintenance Records (<?= count($maintenance) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#fuelTab">
                    <i class="fa-solid fa-gas-pump me-1"></i> Fuel Logs (<?= count($fuelLogs) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#telemetryTab">
                    <i class="fa-solid fa-satellite-dish me-1"></i> Recent Telemetry
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">
            <!-- Trips Tab -->
            <div class="tab-pane fade show active" id="tripsTab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>Trip #</th>
                                <th>Origin &rarr; Destination</th>
                                <th>Distance</th>
                                <th>Cargo</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trips)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No trips recorded for this vehicle.</td></tr>
                            <?php else: ?>
                                <?php foreach ($trips as $t): ?>
                                    <tr>
                                        <td><strong><?= esc($t['trip_number']) ?></strong></td>
                                        <td class="small">
                                            <div><strong>From:</strong> <?= esc($t['origin_address']) ?></div>
                                            <div><strong>To:</strong> <?= esc($t['destination_address']) ?></div>
                                        </td>
                                        <td><?= $t['distance_km'] ?> km</td>
                                        <td class="small"><?= esc($t['cargo_type']) ?> (<?= number_format($t['cargo_weight_kg'], 0) ?> kg)</td>
                                        <td><span class="badge-status badge-<?= esc($t['status']) ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('trips/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Maintenance Tab -->
            <div class="tab-pane fade" id="maintenanceTab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>Work Order #</th>
                                <th>Service Type</th>
                                <th>Scheduled Date</th>
                                <th>Service Center</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($maintenance)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No maintenance records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($maintenance as $m): ?>
                                    <tr>
                                        <td><strong><?= esc($m['reference_no']) ?></strong></td>
                                        <td><?= esc($m['service_type']) ?></td>
                                        <td><?= esc($m['scheduled_date']) ?></td>
                                        <td><?= esc($m['service_center'] ?: 'In-house Fleet Shop') ?></td>
                                        <td><strong>₱<?= number_format($m['cost'], 2) ?></strong></td>
                                        <td><span class="badge-status badge-<?= esc($m['status']) ?>"><?= ucfirst(str_replace('_', ' ', $m['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fuel Tab -->
            <div class="tab-pane fade" id="fuelTab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>Date</th>
                                <th>Receipt #</th>
                                <th>Station</th>
                                <th>Liters</th>
                                <th>Price/L</th>
                                <th>Total Cost</th>
                                <th>Odometer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fuelLogs)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No fuel logs for this vehicle.</td></tr>
                            <?php else: ?>
                                <?php foreach ($fuelLogs as $f): ?>
                                    <tr>
                                        <td><?= esc($f['fuel_date']) ?></td>
                                        <td><?= esc($f['receipt_no']) ?></td>
                                        <td><?= esc($f['fuel_station']) ?></td>
                                        <td><?= number_format($f['liters'], 2) ?> L</td>
                                        <td>₱<?= number_format($f['cost_per_liter'], 2) ?></td>
                                        <td><strong>₱<?= number_format($f['total_cost'], 2) ?></strong></td>
                                        <td><?= number_format($f['odometer_km'], 1) ?> km</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Telemetry Tab -->
            <div class="tab-pane fade" id="telemetryTab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-monospace small">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Speed (km/h)</th>
                                <th>Fuel Level</th>
                                <th>Engine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($telemetry)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted font-sans-serif">No telemetry log entries recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($telemetry as $tel): ?>
                                    <tr>
                                        <td><?= esc($tel['created_at']) ?></td>
                                        <td><?= esc($tel['latitude']) ?></td>
                                        <td><?= esc($tel['longitude']) ?></td>
                                        <td><?= esc($tel['speed_kmh']) ?></td>
                                        <td><?= esc($tel['fuel_level']) ?>%</td>
                                        <td><?= esc($tel['engine_status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
