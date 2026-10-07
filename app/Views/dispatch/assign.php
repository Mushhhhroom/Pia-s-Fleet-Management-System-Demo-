<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Breadcrumb & Back -->
<div class="mb-3">
    <a href="<?= base_url('dispatch') ?>" class="text-decoration-none text-muted small">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Dispatch Queue
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header Panel -->
        <div class="card-panel border shadow-sm mb-4">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <div>
                    <div class="mono" style="font-size: 0.68rem; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.08em;">
                        Heuristic Allocation Engine &bull; SDD §4.2
                    </div>
                    <h4 class="mb-0 fw-bold text-dark fs-5">Dispatch Assignment: <?= esc($request['request_number']) ?></h4>
                    <div class="text-muted small">Destination: <strong><?= esc($request['destination']) ?></strong></div>
                </div>
                <span class="badge bg-success text-white small px-3 py-2">
                    <i class="fa-solid fa-stamp me-1"></i> Authorized by Admin Division Head
                </span>
            </div>

            <!-- Request Quick Overview -->
            <div class="p-3 bg-white border-bottom">
                <div class="row g-2 small">
                    <div class="col-md-3">
                        <span class="text-muted d-block">Originating Division:</span>
                        <strong><?= esc($request['office_name'] ?? 'PIA Central Office') ?></strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">Departure Schedule:</span>
                        <strong class="mono"><?= date('M d, Y h:i A', strtotime($request['departure_time'])) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">Passenger Manifest:</span>
                        <strong><?= esc($request['passenger_count']) ?> official passengers</strong>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted d-block">Preferred Type:</span>
                        <strong class="text-uppercase"><?= esc($request['requested_vehicle_type'] ?: 'Any Unit') ?></strong>
                    </div>
                </div>
            </div>

            <!-- Dispatch Allocation Form -->
            <form action="<?= base_url('dispatch/assign/' . $request['id']) ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <div class="row g-4">
                    <!-- 1. Vehicle Selection (Ranked by Heuristic Score) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="fw-bold text-dark small text-uppercase">
                                <i class="fa-solid fa-truck text-primary me-1"></i> Select Vehicle Asset <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-light text-muted border small" style="font-size: 0.65rem;">
                                Ranked by PMS & Capacity
                            </span>
                        </div>

                        <?php if (empty($recommendations['recommended_vehicles'])): ?>
                            <div class="alert alert-danger p-3 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> No vehicles currently available or all units are locked for PMS.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recommendations['recommended_vehicles'] as $idx => $veh): ?>
                                    <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded-2 mb-2">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="vehicle_id" value="<?= $veh['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?> required>
                                            <div>
                                                <div class="mono fw-bold text-dark fs-6"><?= esc($veh['plate_number']) ?></div>
                                                <div class="text-muted small"><?= esc($veh['make'] . ' ' . $veh['model']) ?> (<?= esc($veh['year']) ?>)</div>
                                                <div class="d-flex gap-2 mt-1">
                                                    <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">
                                                        <?= number_format((float)$veh['odometer_km'], 1) ?> km
                                                    </span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size: 0.65rem;">
                                                        PMS Safe: <?= number_format($veh['km_to_pms'], 0) ?> km
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary rounded-pill small" title="Heuristic Match Index">
                                                Score: <?= $veh['calculated_score'] ?>%
                                            </span>
                                            <div class="small text-muted" style="font-size: 0.68rem; margin-top: 4px;">
                                                Fuel: <?= $veh['current_fuel_level'] ?>%
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 2. Driver Selection (Ranked by Heuristic Score) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="fw-bold text-dark small text-uppercase">
                                <i class="fa-solid fa-user-shield text-primary me-1"></i> Select Certified Driver <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-light text-muted border small" style="font-size: 0.65rem;">
                                Ranked by Rest & Safety
                            </span>
                        </div>

                        <?php if (empty($recommendations['recommended_drivers'])): ?>
                            <div class="alert alert-danger p-3 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> No drivers currently available or all drivers are on active assignment.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recommendations['recommended_drivers'] as $idx => $drv): ?>
                                    <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded-2 mb-2">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="driver_id" value="<?= $drv['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?> required>
                                            <div>
                                                <div class="fw-bold text-dark small"><?= esc($drv['first_name'] . ' ' . $drv['last_name']) ?></div>
                                                <div class="mono text-muted" style="font-size: 0.72rem;"><?= esc($drv['driver_code']) ?> &bull; <?= esc($drv['license_number']) ?></div>
                                                <div class="d-flex gap-2 mt-1">
                                                    <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                                                        <?= esc($drv['license_type']) ?>
                                                    </span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-size: 0.65rem;">
                                                        Safety: <?= $drv['safety_score'] ?>%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary rounded-pill small" title="Heuristic Match Index">
                                                Score: <?= $drv['calculated_score'] ?>%
                                            </span>
                                            <div class="small text-muted" style="font-size: 0.68rem; margin-top: 4px;">
                                                Trips: <?= $drv['total_trips'] ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Bar & Security Cryptographic Token Confirmation -->
                <div class="pt-4 mt-3 border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="small text-muted d-flex align-items-center gap-2">
                        <i class="fa-solid fa-qrcode text-primary fa-lg"></i>
                        <span>Confirming dispatch generates the official e-DTT (ADMIN-F-001 rev1) and HMAC-SHA256 QR security code.</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('dispatch') ?>" class="btn-corp btn-corp-secondary text-decoration-none">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" <?= !$recommendations['has_available_match'] ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-paper-plane me-1"></i> Finalize Dispatch & Generate e-DTT
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
