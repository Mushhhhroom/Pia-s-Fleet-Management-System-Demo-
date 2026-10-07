<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: #059669; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            <i class="fa-solid fa-shield-halved me-1"></i> Compound Security &bull; Sub-2-Second Verification
        </div>
        <h1 class="page-title mb-1">Gate Security Checkpoint & QR Scanner</h1>
        <p class="text-muted small mb-0">High-speed e-DTT QR camera scanner, cryptographic verification, and strict odometer bounding.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border p-2 shadow-sm small">
            <i class="fa-solid fa-tower-broadcast text-success me-1"></i> Checkpoint Gate #1 Active
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Scanner Interface -->
    <div class="col-lg-5">
        <!-- Interactive Scanner Card -->
        <div class="card-panel border shadow-sm mb-4">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark small text-uppercase">
                    <i class="fa-solid fa-camera text-primary me-1"></i> Optical e-DTT QR Scanner
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">
                    <i class="fa-solid fa-circle text-success" style="font-size: 0.5rem;"></i> Ready
                </span>
            </div>
            <div class="p-4 text-center">
                <!-- Camera Viewport Box -->
                <div id="qrReader" style="width: 100%; min-height: 250px; background: #0b1324; border-radius: 8px; overflow: hidden; position: relative;">
                    <div id="scannerPlaceholder" class="p-5 text-white-50">
                        <i class="fa-solid fa-qrcode fa-3x mb-3 d-block text-white opacity-50"></i>
                        <button type="button" class="btn btn-sm btn-primary px-4 shadow" id="btnStartCamera">
                            <i class="fa-solid fa-video me-1"></i> Start Camera Scanner
                        </button>
                        <div class="small mt-2" style="font-size: 0.72rem;">Supports mobile rear lens & desktop webcams</div>
                    </div>
                </div>

                <div id="scannerStatus" class="mt-3 small text-muted">
                    Point camera at driver's digital or printed e-DTT QR code.
                </div>

                <!-- Divider / Manual Entry Fallback -->
                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1 my-0">
                    <span class="px-2 text-muted small mono" style="font-size: 0.7rem;">OR MANUAL ENTRY</span>
                    <hr class="flex-grow-1 my-0">
                </div>

                <div class="input-group input-group-sm">
                    <input type="text" id="manualTokenInput" class="form-control mono" placeholder="Enter DTT Serial (e.g. DTT-2026-0001)">
                    <button class="btn btn-dark" type="button" id="btnVerifyManual">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Verify
                    </button>
                </div>
            </div>
        </div>

        <!-- Compound In/Out Stats -->
        <div class="card-panel border shadow-sm p-3">
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="p-3 rounded bg-primary bg-opacity-10 border border-primary-subtle">
                        <div class="mono fs-4 fw-bold text-primary"><?= count($vehiclesOut) ?></div>
                        <div class="small text-muted" style="font-size: 0.72rem;">VEHICLES OUT ON TRIP</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning-subtle">
                        <div class="mono fs-4 fw-bold text-dark"><?= count($vehiclesPendingDeparture) ?></div>
                        <div class="small text-muted" style="font-size: 0.72rem;">AWAITING EGRESS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Verification Results & Live Compound Roster -->
    <div class="col-lg-7">
        <!-- Verification Output Card (Dynamic) -->
        <div id="verificationResultPanel" class="card-panel border shadow-sm mb-4 d-none">
            <!-- Injected dynamically via JS upon scan -->
        </div>

        <!-- Vehicles Currently on Official Transit Outside Compound -->
        <div class="card-panel border shadow-sm mb-4">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark small text-uppercase">
                    <i class="fa-solid fa-route text-primary me-1"></i> Active Missions Outside Compound (<?= count($vehiclesOut) ?>)
                </span>
                <span class="badge bg-primary small">EGRESS ACTIVE</span>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>Unit / Plate</th>
                            <th>Driver</th>
                            <th>Destination</th>
                            <th>Departed At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vehiclesOut)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    All official vehicles are currently parked safely inside the compound.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehiclesOut as $vo): ?>
                                <tr>
                                    <td>
                                        <div class="mono fw-bold text-dark"><?= esc($vo['plate_number']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= esc($vo['ticket_serial_no']) ?></div>
                                    </td>
                                    <td class="small fw-semibold"><?= esc($vo['driver_name']) ?></td>
                                    <td class="small text-truncate" style="max-width: 180px;" title="<?= esc($vo['authorized_destination']) ?>">
                                        <?= esc($vo['authorized_destination']) ?>
                                    </td>
                                    <td class="mono small text-muted">
                                        <?= $vo['departure_time'] ? date('M d, H:i', strtotime($vo['departure_time'])) : '-' ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" onclick="triggerManualVerification('<?= esc($vo['ticket_serial_no']) ?>')">
                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Ingress
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Checkpoint Log -->
        <div class="card-panel border shadow-sm">
            <div class="p-3 border-bottom bg-light">
                <span class="fw-bold text-dark small text-uppercase">
                    <i class="fa-solid fa-clock-rotate-left text-muted me-1"></i> Recent Security Gate Clearances
                </span>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>Scan Time</th>
                            <th>Event</th>
                            <th>Vehicle Plate</th>
                            <th>Driver</th>
                            <th>Odometer</th>
                            <th>Guard Officer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">No gate scans recorded today.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td class="mono small text-muted">
                                        <?= date('M d, H:i:s', strtotime($log['scanned_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $log['event_type'] === 'egress' ? 'bg-primary text-white' : 'bg-success text-white' ?> small">
                                            <?= strtoupper($log['event_type']) ?>
                                        </span>
                                    </td>
                                    <td class="mono fw-bold small"><?= esc($log['plate_number']) ?></td>
                                    <td class="small"><?= esc($log['driver_name']) ?></td>
                                    <td class="mono small fw-semibold"><?= number_format((float)$log['odometer_reading'], 1) ?> km</td>
                                    <td class="small text-muted"><?= esc($log['guard_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Clearance Action Modal -->
<div class="modal fade" id="clearanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold" id="modalTitle">
                    <i class="fa-solid fa-shield-halved me-1"></i> Gate Security Clearance
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="gateClearanceForm">
                <?= csrf_field() ?>
                <input type="hidden" name="ticket_id" id="modalTicketId">
                <input type="hidden" name="event_type" id="modalEventType">

                <div class="modal-body p-4">
                    <!-- Ticket Details Summary -->
                    <div class="p-3 rounded bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="mono fw-bold fs-6 text-dark" id="modalPlate"></span>
                            <span class="badge bg-primary mono" id="modalSerial"></span>
                        </div>
                        <div class="small text-dark mb-1" id="modalDriver"></div>
                        <div class="small text-muted mb-1" id="modalDest"></div>
                        <div class="small text-muted mono" style="font-size: 0.72rem;" id="modalPax"></div>
                    </div>

                    <!-- Event Type Banner -->
                    <div class="alert p-2 small mb-3 text-center fw-bold" id="modalEventBadge"></div>

                    <!-- Odometer Reading Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">
                            Physical Odometer Reading (KM) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.1" name="odometer_reading" id="modalOdometer" class="form-control form-control-lg mono fw-bold text-center" required>
                        <div class="form-text small" id="modalOdoValidationHint"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Security Remarks (Optional)</label>
                        <input type="text" name="remarks" id="modalRemarks" class="form-control form-control-sm" placeholder="e.g. Passenger ID badges checked, spare tire verified">
                    </div>
                </div>

                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success px-4 fw-semibold" id="btnSubmitClearance">
                        <i class="fa-solid fa-check me-1"></i> Authorize & Log Clearance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let html5QrCode = null;
    let isScanning = false;
    const btnStart = document.getElementById('btnStartCamera');
    const placeholder = document.getElementById('scannerPlaceholder');
    const statusText = document.getElementById('scannerStatus');

    btnStart.addEventListener('click', function() {
        startScanner();
    });

    function startScanner() {
        placeholder.classList.add('d-none');
        html5QrCode = new Html5Qrcode("qrReader");

        const config = { fps: 10, qrbox: { width: 220, height: 220 } };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                // Audio beep on detection
                playBeep();
                statusText.innerHTML = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Scanned successfully! Verifying...</span>`;
                verifyToken(decodedText);
            },
            (errorMessage) => {
                // scanning in progress...
            }
        ).catch((err) => {
            statusText.innerText = "Camera access error: " + err;
            placeholder.classList.remove('d-none');
        });
    }

    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.type = "sine";
            osc.frequency.setValueAtTime(800, ctx.currentTime);
            osc.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch(e) {}
    }

    // Manual Verification
    document.getElementById('btnVerifyManual').addEventListener('click', function() {
        const val = document.getElementById('manualTokenInput').value.trim();
        if (val) verifyToken(val);
    });

    window.triggerManualVerification = function(serial) {
        verifyToken(serial);
    };

    function verifyToken(token) {
        statusText.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-primary"></i> Cryptographically verifying token signature...`;

        fetch('<?= base_url('gate/verify') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ token: token })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showClearanceModal(data);
                statusText.innerText = "Verified: " + data.ticket.ticket_serial_no;
            } else {
                alert(data.message);
                statusText.innerHTML = `<span class="text-danger fw-bold"><i class="fa-solid fa-xmark"></i> ${data.message}</span>`;
            }
        })
        .catch(err => {
            alert('Verification request failed. Please check network connection.');
            statusText.innerText = "Network error.";
        });
    }

    const clearanceModal = new bootstrap.Modal(document.getElementById('clearanceModal'));
    let currentTicketData = null;

    function showClearanceModal(data) {
        currentTicketData = data;
        const t = data.ticket;
        const isEgress = data.event_type === 'egress';

        document.getElementById('modalTicketId').value = t.id;
        document.getElementById('modalEventType').value = isEgress ? 'egress' : 'ingress';
        document.getElementById('modalPlate').innerText = t.plate_number + ' (' + t.make + ' ' + t.model + ')';
        document.getElementById('modalSerial').innerText = t.ticket_serial_no;
        document.getElementById('modalDriver').innerHTML = '<strong>Driver:</strong> ' + t.driver_name + ' (' + t.driver_code + ')';
        document.getElementById('modalDest').innerHTML = '<strong>Destination:</strong> ' + t.authorized_destination;
        document.getElementById('modalPax').innerText = 'Pax: ' + (t.authorized_passengers || t.passenger_names);

        const badge = document.getElementById('modalEventBadge');
        const hint = document.getElementById('modalOdoValidationHint');
        const odoInput = document.getElementById('modalOdometer');

        if (isEgress) {
            badge.className = 'alert alert-primary p-2 small mb-3 text-center fw-bold';
            badge.innerHTML = `<i class="fa-solid fa-arrow-right-from-bracket me-1"></i> AUTHORIZE DEPARTURE (EGRESS)`;
            odoInput.value = parseFloat(t.vehicle_current_odometer || 0).toFixed(1);
            hint.innerHTML = `Must be $\ge$ vehicle's current recorded odometer: <strong>${t.vehicle_current_odometer} KM</strong>`;
        } else {
            badge.className = 'alert alert-success p-2 small mb-3 text-center fw-bold';
            badge.innerHTML = `<i class="fa-solid fa-arrow-right-to-bracket me-1"></i> AUTHORIZE RETURN (INGRESS)`;
            const startOdo = parseFloat(t.start_odometer || t.vehicle_current_odometer || 0);
            odoInput.value = (startOdo + 10).toFixed(1);
            hint.innerHTML = `Must be $\ge$ departure start odometer: <strong>${startOdo} KM</strong>`;
        }

        clearanceModal.show();
    }

    // Submit Clearance Form
    document.getElementById('gateClearanceForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('btnSubmitClearance');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i> Logging Clearance...`;

        fetch('<?= base_url('gate/record') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Authorize & Log Clearance`;

            if (data.status === 'success') {
                clearanceModal.hide();
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Authorize & Log Clearance`;
            alert('Error logging clearance. Please verify inputs.');
        });
    });
});
</script>
<?= $this->endSection() ?>
