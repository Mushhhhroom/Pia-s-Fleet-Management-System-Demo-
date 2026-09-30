<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold mb-0"><?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></h3>
            <span class="badge-status badge-<?= esc($driver['status']) ?>"><?= ucfirst(str_replace('_', ' ', esc($driver['status']))) ?></span>
        </div>
        <p class="text-muted mb-0">Driver Code: <strong><?= esc($driver['driver_code']) ?></strong> | License: <span class="font-monospace"><?= esc($driver['license_number']) ?></span> (<?= esc($driver['license_type']) ?>)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('drivers/' . $driver['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
        </a>
        <form action="<?= base_url('drivers/' . $driver['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to remove this driver?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="fa-solid fa-trash me-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Safety Score</span>
            <h3 class="fw-bold text-success mt-1 mb-0"><?= number_format($driver['safety_score'], 1) ?>%</h3>
            <span class="small text-muted">Exemplary Driving Record</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Completed Trips</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?= $driver['total_trips'] ?></h3>
            <span class="small text-muted">Lifetime Dispatches</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">License Expiry</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= esc($driver['license_expiry']) ?></h4>
            <span class="small text-success"><i class="fa-solid fa-circle-check me-1"></i> Valid</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card text-center">
            <span class="text-muted small text-uppercase fw-semibold">Assigned Truck</span>
            <?php if ($assignedVehicle): ?>
                <h5 class="fw-bold text-primary mt-1 mb-0"><?= esc($assignedVehicle['vehicle_code']) ?></h5>
                <span class="small text-muted"><?= esc($assignedVehicle['plate_number']) ?></span>
            <?php else: ?>
                <h5 class="text-muted mt-1 mb-0 fs-6">No Vehicle Assigned</h5>
                <span class="small text-muted">Ready for dispatch</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card-custom">
    <div class="card-header">
        <i class="fa-solid fa-route text-primary me-2"></i> Assigned Trip Log (<?= count($trips) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Trip #</th>
                        <th>Origin &rarr; Destination</th>
                        <th>Distance</th>
                        <th>Scheduled Departure</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trips)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No trips recorded for this driver.</td></tr>
                    <?php else: ?>
                        <?php foreach ($trips as $t): ?>
                            <tr>
                                <td><strong><?= esc($t['trip_number']) ?></strong></td>
                                <td class="small">
                                    <div><strong>From:</strong> <?= esc($t['origin_address']) ?></div>
                                    <div><strong>To:</strong> <?= esc($t['destination_address']) ?></div>
                                </td>
                                <td><?= $t['distance_km'] ?> km</td>
                                <td><?= esc($t['scheduled_departure']) ?></td>
                                <td><span class="badge-status badge-<?= esc($t['status']) ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= base_url('trips/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">View &rarr;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
