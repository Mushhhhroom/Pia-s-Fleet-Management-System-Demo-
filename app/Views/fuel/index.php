<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Fuel & Energy Expenses</h3>
        <p class="text-muted mb-0">Monitor fuel fill-up logs, consumption rates, fueling station locations, and total fleet expenditure.</p>
    </div>
    <a href="<?= base_url('fuel/new') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Log Fuel Purchase
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Fleet Fuel Spend</span>
                <h3 class="fw-bold mb-0 mt-1 text-success">₱<?= number_format($totalSpend, 2) ?></h3>
            </div>
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Liters Dispensed</span>
                <h3 class="fw-bold mb-0 mt-1 text-primary"><?= number_format($totalLiters, 1) ?> <span class="fs-6 font-normal">L</span></h3>
            </div>
            <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-gas-pump"></i>
            </div>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Date</th>
                        <th>Receipt #</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Gas Station</th>
                        <th>Volume (L)</th>
                        <th>Price / L</th>
                        <th>Total Cost</th>
                        <th>Odometer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No fuel logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $f): ?>
                            <tr>
                                <td><?= esc($f['fuel_date']) ?></td>
                                <td><span class="font-monospace fw-semibold text-dark"><?= esc($f['receipt_no']) ?></span></td>
                                <td>
                                    <strong><?= esc($f['vehicle_code']) ?></strong>
                                    <span class="small text-muted d-block"><?= esc($f['plate_number']) ?></span>
                                </td>
                                <td><?= esc($f['driver_name'] ?: 'Unassigned') ?></td>
                                <td class="small"><?= esc($f['fuel_station'] ?: 'Commercial Station') ?></td>
                                <td><strong><?= number_format($f['liters'], 2) ?></strong> L</td>
                                <td>₱<?= number_format($f['cost_per_liter'], 2) ?></td>
                                <td><strong class="text-success">₱<?= number_format($f['total_cost'], 2) ?></strong></td>
                                <td><?= number_format($f['odometer_km'], 1) ?> km</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
