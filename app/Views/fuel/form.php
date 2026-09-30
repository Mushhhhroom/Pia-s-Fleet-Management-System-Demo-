<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
        <p class="text-muted mb-0">Record fuel receipt, liters purchased, odometer reading, and station details.</p>
    </div>
    <a href="<?= base_url('fuel') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Fuel Logs
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form action="<?= base_url('fuel') ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Receipt & Vehicle Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Select Fleet Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Choose Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= old('vehicle_id') == $v['id'] ? 'selected' : '' ?>>
                                <?= esc($v['vehicle_code']) ?> &bull; <?= esc($v['make'] . ' ' . $v['model']) ?> (<?= esc($v['plate_number']) ?>) [<?= number_format($v['odometer_km'], 1) ?> km]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Driver at Pump</label>
                    <select name="driver_id" class="form-select">
                        <option value="">-- Driver (Optional) --</option>
                        <?php foreach ($drivers as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= old('driver_id') == $d['id'] ? 'selected' : '' ?>>
                                <?= esc($d['first_name'] . ' ' . $d['last_name']) ?> (<?= esc($d['driver_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Receipt / Invoice Number</label>
                    <input type="text" name="receipt_no" class="form-control" placeholder="e.g. INV-90412" value="<?= old('receipt_no') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Transaction Date</label>
                    <input type="date" name="fuel_date" class="form-control" required value="<?= old('fuel_date', date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Volume (Liters)</label>
                    <input type="number" step="0.01" name="liters" id="liters" class="form-control" required placeholder="0.00" value="<?= old('liters') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Total Paid (₱)</label>
                    <input type="number" step="0.01" name="total_cost" id="total_cost" class="form-control" required placeholder="0.00" value="<?= old('total_cost') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Odometer at Pump (km)</label>
                    <input type="number" step="0.1" name="odometer_km" class="form-control" placeholder="Current km" value="<?= old('odometer_km') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Fuel Station / Location</label>
                    <input type="text" name="fuel_station" class="form-control" placeholder="e.g. Petron NLEX Marilao, Shell SLEX Mamplasan" value="<?= old('fuel_station') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Notes / Card Reference</label>
                    <input type="text" name="notes" class="form-control" placeholder="Fleet card #, receipt remarks..." value="<?= old('notes') ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= base_url('fuel') ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-1"></i> Save Fuel Log
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
