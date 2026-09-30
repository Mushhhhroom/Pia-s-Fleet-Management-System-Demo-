<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
        <p class="text-muted mb-0">Create and dispatch freight cargo trips to vehicles and qualified drivers.</p>
    </div>
    <a href="<?= base_url('trips') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Trips
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form action="<?= base_url('trips') ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Asset & Driver Allocation</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Select Transport Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">-- Choose Commercial Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= old('vehicle_id') == $v['id'] ? 'selected' : '' ?>>
                                <?= esc($v['vehicle_code']) ?> &bull; <?= esc($v['make']) ?> <?= esc($v['model']) ?> (Plate: <?= esc($v['plate_number']) ?>) [<?= ucfirst(str_replace('_', ' ', $v['status'])) ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Assign Qualified Driver</label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">-- Choose Operator --</option>
                        <?php foreach ($drivers as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= old('driver_id') == $d['id'] ? 'selected' : '' ?>>
                                <?= esc($d['first_name'] . ' ' . $d['last_name']) ?> (<?= esc($d['driver_code']) ?>) &bull; <?= esc($d['license_type']) ?> [Score: <?= $d['safety_score'] ?>%]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Origin & Destination Route Coordinates</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Origin Pickup Hub / Address</label>
                    <input type="text" name="origin_address" id="origin_address" class="form-control mb-2" required placeholder="e.g. Manila North Harbor, Port Area, Manila" value="<?= old('origin_address', 'Manila North Harbor, Port Area, Manila') ?>">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" step="0.000001" name="origin_lat" id="origin_lat" class="form-control form-control-sm font-monospace" placeholder="Origin Lat" value="<?= old('origin_lat', '14.583333') ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" step="0.000001" name="origin_lng" id="origin_lng" class="form-control form-control-sm font-monospace" placeholder="Origin Lng" value="<?= old('origin_lng', '120.966667') ?>">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Destination Delivery Hub / Address</label>
                    <input type="text" name="destination_address" id="destination_address" class="form-control mb-2" required placeholder="e.g. Clark Global City Distribution Hub, Pampanga" value="<?= old('destination_address', 'Clark Global City Distribution Hub, Pampanga') ?>">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" step="0.000001" name="destination_lat" id="destination_lat" class="form-control form-control-sm font-monospace" placeholder="Dest Lat" value="<?= old('destination_lat', '15.185500') ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" step="0.000001" name="destination_lng" id="destination_lng" class="form-control form-control-sm font-monospace" placeholder="Dest Lng" value="<?= old('destination_lng', '120.540600') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Cargo Specifications & Schedule</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Cargo Description / Commodity</label>
                    <input type="text" name="cargo_type" class="form-control" required placeholder="e.g. Commercial Electronics, Dry Goods" value="<?= old('cargo_type', 'General Merchandise') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Cargo Weight (kg)</label>
                    <input type="number" step="0.01" name="cargo_weight_kg" class="form-control" required placeholder="Weight in kg" value="<?= old('cargo_weight_kg', 12500) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Estimated Distance (km)</label>
                    <input type="number" step="0.1" name="distance_km" class="form-control" placeholder="Auto-calculated if blank" value="<?= old('distance_km', 95.0) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Scheduled Departure</label>
                    <input type="datetime-local" name="scheduled_departure" class="form-control" required value="<?= old('scheduled_departure', date('Y-m-d\TH:i')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Estimated Arrival</label>
                    <input type="datetime-local" name="scheduled_arrival" class="form-control" value="<?= old('scheduled_arrival', date('Y-m-d\TH:i', strtotime('+4 hours'))) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Dispatch Priority</label>
                    <select name="priority" class="form-select">
                        <option value="normal" <?= old('priority') === 'normal' ? 'selected' : '' ?>>Normal Freight</option>
                        <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Urgent Priority</option>
                        <option value="critical" <?= old('priority') === 'critical' ? 'selected' : '' ?>>Critical Express</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Initial Status</label>
                    <select name="status" class="form-select">
                        <option value="scheduled">Scheduled</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="in_transit" selected>In Transit (Active Now)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Special Instructions / Dispatch Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Gate pass requirements, seal numbers, handling precautions..."><?= old('notes') ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= base_url('trips') ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-paper-plane me-1"></i> Confirm & Dispatch Trip
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
