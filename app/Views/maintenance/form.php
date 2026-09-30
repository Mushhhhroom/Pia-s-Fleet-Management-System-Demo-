<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
        <p class="text-muted mb-0">Record preventative servicing, scheduled repairs, or safety inspections.</p>
    </div>
    <a href="<?= base_url('maintenance') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Work Orders
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form action="<?= base_url('maintenance') ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Vehicle & Work Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Select Fleet Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Choose Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= old('vehicle_id') == $v['id'] ? 'selected' : '' ?>>
                                <?= esc($v['vehicle_code']) ?> &bull; <?= esc($v['make'] . ' ' . $v['model']) ?> (Plate: <?= esc($v['plate_number']) ?>) [<?= number_format($v['odometer_km'], 1) ?> km]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Service Type / Work Item</label>
                    <input type="text" name="service_type" class="form-control" required placeholder="e.g. Engine Oil Flush & Filters, Brake Pads Replacement, 50,000km PMS" value="<?= old('service_type') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low (Routine Check)</option>
                        <option value="medium" selected>Medium (Standard Maintenance)</option>
                        <option value="high">High (Urgent Repair)</option>
                        <option value="critical">Critical (Safety Hazard / Grounded)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Scheduled Date</label>
                    <input type="date" name="scheduled_date" class="form-control" required value="<?= old('scheduled_date', date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Current Odometer (km)</label>
                    <input type="number" step="0.1" name="odometer_at_service" class="form-control" placeholder="Odometer reading" value="<?= old('odometer_at_service', 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Estimated Cost (₱)</label>
                    <input type="number" step="0.01" name="cost" class="form-control" placeholder="0.00" value="<?= old('cost', 0) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Service Facility / Garage</label>
                    <input type="text" name="service_center" class="form-control" placeholder="e.g. In-House Fleet Shop, Isuzu QC Service" value="<?= old('service_center', 'In-House Fleet Shop') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Assigned Technician</label>
                    <input type="text" name="technician_name" class="form-control" placeholder="Technician Name" value="<?= old('technician_name') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Initial Status</label>
                    <select name="status" class="form-select">
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress (Move vehicle to Maintenance)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Description of Defect / Parts Needed</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Symptoms reported by driver, specific components to inspect, OEM part numbers..."><?= old('description') ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= base_url('maintenance') ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-1"></i> Create Work Order
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
