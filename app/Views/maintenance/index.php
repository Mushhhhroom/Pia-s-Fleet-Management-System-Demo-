<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Work Orders & Preventative Maintenance</h3>
        <p class="text-muted mb-0">Track scheduled maintenance intervals, garage repair costs, and vehicle health.</p>
    </div>
    <a href="<?= base_url('maintenance/new') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Create Work Order
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Work Order #</th>
                        <th>Vehicle</th>
                        <th>Service / Maintenance Item</th>
                        <th>Priority</th>
                        <th>Scheduled Date</th>
                        <th>Service Facility</th>
                        <th>Repair Cost</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No maintenance work orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $m): ?>
                            <tr>
                                <td><strong class="text-dark"><?= esc($m['reference_no']) ?></strong></td>
                                <td>
                                    <strong><?= esc($m['vehicle_code']) ?></strong>
                                    <span class="small text-muted d-block"><?= esc($m['plate_number']) ?></span>
                                </td>
                                <td>
                                    <strong><?= esc($m['service_type']) ?></strong>
                                    <?php if (!empty($m['description'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;"><?= esc($m['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $m['priority'] === 'high' || $m['priority'] === 'critical' ? 'danger' : ($m['priority'] === 'medium' ? 'warning' : 'secondary') ?>-subtle text-<?= $m['priority'] === 'high' || $m['priority'] === 'critical' ? 'danger' : ($m['priority'] === 'medium' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($m['priority']) ?>
                                    </span>
                                </td>
                                <td><?= esc($m['scheduled_date']) ?></td>
                                <td>
                                    <span class="small"><?= esc($m['service_center'] ?: 'In-House Fleet Workshop') ?></span>
                                    <?php if (!empty($m['technician_name'])): ?>
                                        <span class="small text-muted d-block">Tech: <?= esc($m['technician_name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong>₱<?= number_format($m['cost'], 2) ?></strong></td>
                                <td>
                                    <span class="badge-status badge-<?= esc($m['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', esc($m['status']))) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($m['status'] !== 'completed'): ?>
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#completeModal-<?= $m['id'] ?>">
                                            <i class="fa-solid fa-check me-1"></i> Update
                                        </button>
                                        
                                        <!-- Modal for updating work order status -->
                                        <div class="modal fade text-start" id="completeModal-<?= $m['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="<?= base_url('maintenance/' . $m['id'] . '/status') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Update Work Order: <?= esc($m['reference_no']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="scheduled" <?= $m['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                                    <option value="in_progress" <?= $m['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress (Vehicle in Shop)</option>
                                                                    <option value="completed" <?= $m['status'] === 'completed' ? 'selected' : '' ?>>Completed (Ready for Road)</option>
                                                                    <option value="cancelled" <?= $m['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Final Invoiced Cost (₱)</label>
                                                                <input type="number" step="0.01" name="cost" class="form-control" value="<?= $m['cost'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Serviced</span>
                                    <?php endif; ?>
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
