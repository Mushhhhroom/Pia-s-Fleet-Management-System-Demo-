<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Fleet Analytics & Intelligence Reports</h3>
        <p class="text-muted mb-0">High-level operational metrics, fuel efficiency, maintenance expenses, and CSV exports.</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-file-export me-1"></i> Export Data (CSV)
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="<?= base_url('reports/export?type=trips') ?>"><i class="fa-solid fa-route me-2 text-primary"></i> Export Trips Ledger</a></li>
            <li><a class="dropdown-item" href="<?= base_url('reports/export?type=fuel') ?>"><i class="fa-solid fa-gas-pump me-2 text-success"></i> Export Fuel Receipts</a></li>
            <li><a class="dropdown-item" href="<?= base_url('reports/export?type=maintenance') ?>"><i class="fa-solid fa-wrench me-2 text-warning"></i> Export Maintenance Records</a></li>
        </ul>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Total Distance Logged</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?= number_format($totalDistance, 1) ?> <span class="fs-6 font-normal">km</span></h3>
            <span class="small text-muted"><?= $completedTrips ?> / <?= $totalTrips ?> Dispatches Completed</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Total Fuel Expenditure</span>
            <h3 class="fw-bold text-success mt-1 mb-0">₱<?= number_format($totalFuelCost, 0) ?></h3>
            <span class="small text-muted"><?= number_format($totalLiters, 1) ?> Liters Dispensed</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Garage Maintenance Costs</span>
            <h3 class="fw-bold text-warning mt-1 mb-0">₱<?= number_format($totalMaintCost, 0) ?></h3>
            <span class="small text-muted">Parts, PMS & Service Orders</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Cost Per Kilometer</span>
            <?php 
                $totalOpCost = $totalFuelCost + $totalMaintCost;
                $costPerKm = $totalDistance > 0 ? round($totalOpCost / $totalDistance, 2) : 0;
            ?>
            <h3 class="fw-bold text-primary mt-1 mb-0">₱<?= number_format($costPerKm, 2) ?> <span class="fs-6 font-normal">/ km</span></h3>
            <span class="small text-muted">Operating Cost Index</span>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="card-header">
        <i class="fa-solid fa-chart-line text-primary me-2"></i> Fleet Asset Utilization & Efficiency Breakdown
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Vehicle Code</th>
                        <th>Make & Model</th>
                        <th>License Plate</th>
                        <th>Type / Payload</th>
                        <th>Odometer Reading</th>
                        <th>Fuel Status</th>
                        <th>Assigned Driver</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td><strong class="text-primary"><?= esc($v['vehicle_code']) ?></strong></td>
                            <td><?= esc($v['make'] . ' ' . $v['model']) ?> (<?= esc($v['year']) ?>)</td>
                            <td><span class="font-monospace fw-semibold"><?= esc($v['plate_number']) ?></span></td>
                            <td><?= ucfirst(str_replace('_', ' ', esc($v['type']))) ?> (<?= number_format($v['max_payload_kg'], 0) ?> kg)</td>
                            <td><strong><?= number_format($v['odometer_km'], 1) ?></strong> km</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                        <div class="progress-bar bg-<?= $v['current_fuel_level'] > 30 ? 'success' : 'danger' ?>" style="width: <?= $v['current_fuel_level'] ?>%"></div>
                                    </div>
                                    <span class="small"><?= $v['current_fuel_level'] ?>%</span>
                                </div>
                            </td>
                            <td><?= esc($v['driver_name'] ?: 'None') ?></td>
                            <td><span class="badge-status badge-<?= esc($v['status']) ?>"><?= ucfirst(str_replace('_', ' ', esc($v['status']))) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
