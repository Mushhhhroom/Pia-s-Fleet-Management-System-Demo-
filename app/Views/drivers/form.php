<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
        <p class="text-muted mb-0">Enter driver licensing, contact details, and emergency contacts.</p>
    </div>
    <a href="<?= base_url('drivers') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Drivers
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form action="<?= $driver ? base_url('drivers/' . $driver['id']) : base_url('drivers') ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Personal Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">First Name</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="John" value="<?= old('first_name', $driver['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required placeholder="Doe" value="<?= old('last_name', $driver['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="john.doe@fleet.com" value="<?= old('email', $driver['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Primary Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="+63 917 000 0000" value="<?= old('phone', $driver['phone'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Residential Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Complete Street, City, Province" value="<?= old('address', $driver['address'] ?? '') ?>">
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Driver Licensing & Certification</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Professional License Number</label>
                    <input type="text" name="license_number" class="form-control font-monospace" required placeholder="N01-XX-XXXXXX" value="<?= old('license_number', $driver['license_number'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">License Classification / Type</label>
                    <input type="text" name="license_type" class="form-control" required placeholder="e.g. Heavy Articulated, Light Commercial" value="<?= old('license_type', $driver['license_type'] ?? 'Professional Driver (Heavy Articulated)') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">License Expiration Date</label>
                    <input type="date" name="license_expiry" class="form-control" required value="<?= old('license_expiry', $driver['license_expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Operations & Safety</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Current Duty Status</label>
                    <select name="status" class="form-select">
                        <?php
                        $statuses = ['available' => 'Available (On Standby)', 'on_trip' => 'On Active Trip', 'off_duty' => 'Off Duty / Rest', 'suspended' => 'Suspended'];
                        $curStat = old('status', $driver['status'] ?? 'available');
                        foreach ($statuses as $k => $lbl): ?>
                            <option value="<?= $k ?>" <?= $curStat === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Safety Score (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="safety_score" class="form-control" value="<?= old('safety_score', $driver['safety_score'] ?? 100) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Emergency Contact Person & Phone</label>
                    <input type="text" name="emergency_contact" class="form-control" placeholder="Contact Name & Number" value="<?= old('emergency_contact', $driver['emergency_contact'] ?? '') ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= base_url('drivers') ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-1"></i> Save Driver Record
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
