<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Fleet Drivers & Operators</h3>
        <p class="text-muted mb-0">Monitor licensed drivers, availability, safety ratings, and assigned vehicles.</p>
    </div>
    <a href="<?= base_url('drivers/new') ?>" class="btn btn-primary">
        <i class="fa-solid fa-user-plus me-1"></i> Register Driver
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Driver Code</th>
                        <th>Name & Contact</th>
                        <th>License Details</th>
                        <th>Expiry Date</th>
                        <th>Total Trips</th>
                        <th>Safety Score</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($drivers)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No drivers registered.</td></tr>
                    <?php else: ?>
                        <?php foreach ($drivers as $d): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('drivers/' . $d['id']) ?>" class="fw-bold text-decoration-none">
                                        <?= esc($d['driver_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= esc($d['first_name'] . ' ' . $d['last_name']) ?></strong>
                                    <span class="small text-muted d-block"><i class="fa-solid fa-phone me-1"></i> <?= esc($d['phone']) ?></span>
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold"><?= esc($d['license_number']) ?></span>
                                    <span class="small text-muted d-block"><?= esc($d['license_type']) ?></span>
                                </td>
                                <td>
                                    <span class="small"><?= esc($d['license_expiry']) ?></span>
                                </td>
                                <td>
                                    <strong><?= $d['total_trips'] ?></strong> trips
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success fs-6 fw-bold">
                                        <?= number_format($d['safety_score'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= esc($d['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', esc($d['status']))) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('drivers/' . $d['id']) ?>" class="btn btn-outline-secondary" title="View Profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('drivers/' . $d['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </div>
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
