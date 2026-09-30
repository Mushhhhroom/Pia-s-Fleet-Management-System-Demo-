<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Logistics & Dispatch Operations</h3>
        <p class="text-muted mb-0">Schedule cargo deliveries, assign transport assets, and manage active freight journeys.</p>
    </div>
    <a href="<?= base_url('trips/new') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> New Trip Dispatch
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Waybill / Trip #</th>
                        <th>Vehicle & Plate</th>
                        <th>Assigned Driver</th>
                        <th>Origin &rarr; Destination</th>
                        <th>Cargo & Weight</th>
                        <th>Departure</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trips)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No trips found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($trips as $t): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('trips/' . $t['id']) ?>" class="fw-bold text-decoration-none">
                                        <?= esc($t['trip_number']) ?>
                                    </a>
                                    <?php if ($t['priority'] === 'urgent' || $t['priority'] === 'critical'): ?>
                                        <span class="badge bg-danger-subtle text-danger ms-1"><?= ucfirst($t['priority']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= esc($t['vehicle_code']) ?></strong>
                                    <span class="small text-muted d-block"><?= esc($t['plate_number']) ?></span>
                                </td>
                                <td>
                                    <span><?= esc($t['driver_name']) ?></span>
                                    <span class="small text-muted d-block"><?= esc($t['driver_phone']) ?></span>
                                </td>
                                <td style="max-width: 280px;">
                                    <div class="text-truncate small"><strong>From:</strong> <?= esc($t['origin_address']) ?></div>
                                    <div class="text-truncate small"><strong>To:</strong> <?= esc($t['destination_address']) ?></div>
                                    <span class="badge bg-light text-secondary border small mt-1"><?= $t['distance_km'] ?> km</span>
                                </td>
                                <td class="small">
                                    <strong><?= esc($t['cargo_type']) ?></strong>
                                    <span class="text-muted d-block"><?= number_format($t['cargo_weight_kg'], 0) ?> kg</span>
                                </td>
                                <td class="small">
                                    <?= esc($t['scheduled_departure']) ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= esc($t['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', esc($t['status']))) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('trips/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        Manage &rarr;
                                    </a>
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
