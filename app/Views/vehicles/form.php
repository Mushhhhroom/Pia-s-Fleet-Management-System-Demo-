<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
        <p class="text-muted mb-0">Fill in vehicle specifications, telemetry coordinates, and driver assignment.</p>
    </div>
    <a href="<?= base_url('vehicles') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Fleet
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form action="<?= $vehicle ? base_url('vehicles/' . $vehicle['id']) : base_url('vehicles') ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Vehicle Identification</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vehicle Code (Internal ID)</label>
                    <input type="text" name="vehicle_code" class="form-control" required placeholder="e.g. FLT-06" value="<?= old('vehicle_code', $vehicle['vehicle_code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">License Plate Number</label>
                    <input type="text" name="plate_number" class="form-control" required placeholder="e.g. NHA-9921" value="<?= old('plate_number', $vehicle['plate_number'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">VIN (Vehicle Identification Number)</label>
                    <input type="text" name="vin" class="form-control font-monospace" required placeholder="17-character VIN" value="<?= old('vin', $vehicle['vin'] ?? '') ?>">
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Make, Model & Specifications</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Manufacturer / Make</label>
                    <input type="text" name="make" class="form-control" required placeholder="e.g. Freightliner, Volvo, Isuzu" value="<?= old('make', $vehicle['make'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Model Name</label>
                    <input type="text" name="model" class="form-control" required placeholder="e.g. Cascadia 126" value="<?= old('model', $vehicle['model'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Model Year</label>
                    <input type="number" name="year" class="form-control" required min="1990" max="2030" value="<?= old('year', $vehicle['year'] ?? date('Y')) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Body / Vehicle Type</label>
                    <select name="type" class="form-select" required>
                        <?php
                        $types = ['semi_truck' => 'Semi-Truck / Tractor', 'box_truck' => 'Box Truck (Medium)', 'delivery_van' => 'Commercial Delivery Van', 'pickup' => 'Light Pickup', 'trailer' => 'Trailer Chassis', 'suv' => 'Fleet Utility SUV'];
                        $curType = old('type', $vehicle['type'] ?? 'semi_truck');
                        foreach ($types as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $curType === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fuel / Powertrain Type</label>
                    <select name="fuel_type" class="form-select" required>
                        <?php
                        $fuels = ['diesel' => 'Diesel', 'gasoline' => 'Gasoline', 'electric' => 'Electric (EV)', 'hybrid' => 'Hybrid'];
                        $curFuel = old('fuel_type', $vehicle['fuel_type'] ?? 'diesel');
                        foreach ($fuels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $curFuel === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Max Payload Capacity (kg)</label>
                    <input type="number" step="0.01" name="max_payload_kg" class="form-control" value="<?= old('max_payload_kg', $vehicle['max_payload_kg'] ?? 5000) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Fuel Tank (Liters) / Battery (kWh)</label>
                    <input type="number" step="0.01" name="fuel_capacity_liters" class="form-control" value="<?= old('fuel_capacity_liters', $vehicle['fuel_capacity_liters'] ?? 200) ?>">
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Status, Odometer & Driver Assignment</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Current Odometer (km)</label>
                    <input type="number" step="0.1" name="odometer_km" class="form-control" required value="<?= old('odometer_km', $vehicle['odometer_km'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Current Fuel Level (%)</label>
                    <input type="number" step="0.1" max="100" min="0" name="current_fuel_level" class="form-control" value="<?= old('current_fuel_level', $vehicle['current_fuel_level'] ?? 100) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Operational Status</label>
                    <select name="status" class="form-select">
                        <?php
                        $statuses = ['active' => 'Active / Ready', 'in_transit' => 'In Transit', 'maintenance' => 'In Maintenance', 'out_of_service' => 'Out of Service'];
                        $curStat = old('status', $vehicle['status'] ?? 'active');
                        foreach ($statuses as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $curStat === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Assigned Driver</label>
                    <select name="current_driver_id" class="form-select">
                        <option value="">-- No Assigned Driver --</option>
                        <?php
                        $curDriver = old('current_driver_id', $vehicle['current_driver_id'] ?? '');
                        foreach ($drivers as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= (string)$curDriver === (string)$d['id'] ? 'selected' : '' ?>>
                                <?= esc($d['first_name'] . ' ' . $d['last_name']) ?> (<?= esc($d['driver_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= base_url('vehicles') ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-1"></i> Save Vehicle Record
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
