<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: var(--primary-accent); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            <i class="fa-solid fa-van-shuttle me-1"></i> Motorpool Division &bull; Heuristic Dispatch Engine
        </div>
        <h1 class="page-title mb-1">Dispatch Command & Asset Allocation</h1>
        <p class="text-muted small mb-0">Vehicle-driver matching, 5,000 km PMS safety locks, and e-DTT issuance.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle p-2 px-3 small">
            <i class="fa-solid fa-check-circle me-1"></i> Ready for Dispatch: <strong><?= count($pendingDispatch) ?></strong>
        </span>
    </div>
</div>

<!-- Pending Dispatch Queue (Priority Action) -->
<div class="card-panel border shadow-sm mb-4">
    <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                <i class="fa-solid fa-truck-fast"></i>
            </span>
            <span class="fw-bold text-dark small text-uppercase">Authorized Requests Awaiting Vehicle & Driver Assignment</span>
        </div>
        <span class="badge bg-primary rounded-pill small"><?= count($pendingDispatch) ?> Requests</span>
    </div>
    <div class="table-responsive">
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>VRS Control No</th>
                    <th>Requesting Division</th>
                    <th>Destination & Purpose</th>
                    <th>Departure Schedule</th>
                    <th>Pax / Type</th>
                    <th class="text-end">Dispatch Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingDispatch)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-calendar-check fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            No authorized requests awaiting dispatch. All cleared requests have been paired!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingDispatch as $r): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('requests/' . $r['id']) ?>" class="mono fw-bold text-decoration-none text-dark d-block">
                                    <?= esc($r['request_number']) ?>
                                </a>
                                <?php if ((int)$r['is_rush_request'] === 1): ?>
                                    <span class="badge bg-danger text-white" style="font-size: 0.65rem;">
                                        <i class="fa-solid fa-bolt"></i> RUSH (BR-03)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small"><?= esc($r['office_name'] ?? 'Central Office') ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;"><?= esc($r['requestor_name']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small text-truncate" style="max-width: 250px;">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i><?= esc($r['destination']) ?>
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 0.72rem;">
                                    <?= esc($r['purpose']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="mono small fw-semibold" style="font-size: 0.78rem;">
                                    <?= date('M d, Y h:i A', strtotime($r['departure_time'])) ?>
                                </div>
                                <div class="mono text-muted" style="font-size: 0.72rem;">
                                    Return: <?= date('M d, h:i A', strtotime($r['return_time'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-users me-1 text-muted"></i><?= esc($r['passenger_count']) ?> pax
                                </span>
                                <?php if ($r['requested_vehicle_type']): ?>
                                    <span class="badge bg-light text-primary border" style="font-size: 0.68rem;">
                                        <?= strtoupper($r['requested_vehicle_type']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('dispatch/assign/' . $r['id']) ?>" class="btn btn-sm btn-primary py-1 px-3 shadow-sm fw-semibold">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Smart Pair & Issue e-DTT
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fleet Asset Status Board -->
<div class="row g-4">
    <!-- Vehicles with PMS Health -->
    <div class="col-lg-7">
        <div class="card-panel border shadow-sm h-100">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark small text-uppercase">
                    <i class="fa-solid fa-truck me-1 text-primary"></i> Vehicle Fleet & 5,000 KM PMS Radar
                </span>
                <span class="badge bg-white text-muted border small"><?= count($vehicles) ?> Units</span>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>Unit / Plate</th>
                            <th>Current Odo</th>
                            <th>5K PMS Status</th>
                            <th>Dispatch Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td>
                                    <div class="mono fw-bold text-dark" style="font-size: 0.85rem;"><?= esc($v['plate_number']) ?></div>
                                    <div class="text-muted small" style="font-size: 0.72rem;"><?= esc($v['make'] . ' ' . $v['model']) ?></div>
                                </td>
                                <td>
                                    <div class="mono small"><?= number_format((float)$v['odometer_km'], 1) ?> km</div>
                                </td>
                                <td>
                                    <?php
                                        $curr = (float)$v['odometer_km'];
                                        $next = (float)($v['next_pms_odometer'] ?? ($curr + 5000));
                                        $remain = max(0, $next - $curr);
                                        $isLocked = (int)($v['is_locked'] ?? 0) === 1;
                                    ?>
                                    <?php if ($isLocked): ?>
                                        <span class="badge bg-danger text-white small">
                                            <i class="fa-solid fa-lock me-1"></i> LOCKED (PMS Due)
                                        </span>
                                    <?php elseif ($remain <= 500): ?>
                                        <span class="badge bg-warning text-dark small">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= number_format($remain, 0) ?> km to PMS
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">
                                            <i class="fa-solid fa-shield-check me-1"></i> <?= number_format($remain, 0) ?> km safe
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $vStatusBadges = [
                                            'active'         => 'bg-success text-white',
                                            'in_transit'     => 'bg-primary text-white',
                                            'maintenance'    => 'bg-danger text-white',
                                            'out_of_service' => 'bg-secondary text-white',
                                        ];
                                        $vClass = $vStatusBadges[$v['vehicle_status'] ?? 'active'] ?? 'bg-secondary text-white';
                                    ?>
                                    <span class="badge <?= $vClass ?> small" style="font-size: 0.68rem;">
                                        <?= strtoupper($v['vehicle_status'] ?? 'active') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Certified Drivers Pool -->
    <div class="col-lg-5">
        <div class="card-panel border shadow-sm h-100">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark small text-uppercase">
                    <i class="fa-solid fa-id-card me-1 text-primary"></i> Certified Driver Pool
                </span>
                <span class="badge bg-white text-muted border small"><?= count($drivers) ?> Drivers</span>
            </div>
            <div class="p-3">
                <?php foreach ($drivers as $d): ?>
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded-2 bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-light border p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-user-tie text-secondary small"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small"><?= esc($d['first_name'] . ' ' . $d['last_name']) ?></div>
                                <div class="mono text-muted" style="font-size: 0.7rem;"><?= esc($d['driver_code']) ?> &bull; <?= esc($d['license_number']) ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php if ($d['status'] === 'available'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">Available</span>
                            <?php elseif ($d['status'] === 'on_trip'): ?>
                                <span class="badge bg-primary text-white small">On Trip</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white small"><?= ucfirst($d['status']) ?></span>
                            <?php endif; ?>
                            <div class="mono text-muted" style="font-size: 0.68rem; margin-top: 2px;">
                                Safety: <?= $d['safety_score'] ?>%
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
