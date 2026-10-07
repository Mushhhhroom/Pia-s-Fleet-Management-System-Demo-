<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: var(--primary-accent); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            <i class="fa-solid fa-stamp me-1"></i> Multi-Tier Governance &bull; BR-02 SLA Enforcer
        </div>
        <h1 class="page-title mb-1">Approval Portal & 24h SLA Monitor</h1>
        <p class="text-muted small mb-0">Official review queues for Division Directors, OICs, and Administrative Division Head.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border p-2 shadow-sm small">
            <i class="fa-solid fa-stopwatch text-danger me-1"></i> SLA Window: <strong>24 Hours</strong>
        </span>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="approvalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-3 py-2 small" id="tier1-tab" data-bs-toggle="pill" data-bs-target="#tier1-pane" type="button" role="tab">
            <i class="fa-solid fa-user-check me-1"></i> Tier 1: OIC Endorsements
            <span class="badge bg-warning text-dark ms-1"><?= count($pendingOic) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2 small" id="tier2-tab" data-bs-toggle="pill" data-bs-target="#tier2-pane" type="button" role="tab">
            <i class="fa-solid fa-signature me-1"></i> Tier 2: Admin Head Authorization
            <span class="badge bg-info text-white ms-1"><?= count($pendingAdmin) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2 small" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-pane" type="button" role="tab">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> Recently Authorized
            <span class="badge bg-secondary text-white ms-1"><?= count($recentlyApproved) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="approvalTabsContent">
    <!-- Tier 1 Pane (OIC Endorsements) -->
    <div class="tab-pane fade show active" id="tier1-pane" role="tabpanel">
        <div class="card-panel border shadow-sm">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark small text-uppercase">Tier 1: Staff / Regional Director / OIC Review Queue</h6>
                    <div class="text-muted" style="font-size: 0.72rem;">Division chiefs must endorse or reject within the 24-hour SLA window.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>VRS No / Control</th>
                            <th>Requesting Division & Official</th>
                            <th>Destination & Purpose</th>
                            <th>Schedule</th>
                            <th>24h SLA Countdown</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingOic)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success opacity-50"></i>
                                    No pending Tier 1 requests. All division requests have been acted upon!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingOic as $r): ?>
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
                                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 240px;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i><?= esc($r['destination']) ?>
                                        </div>
                                        <div class="text-muted small text-truncate" style="max-width: 240px; font-size: 0.72rem;">
                                            <?= esc($r['purpose']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mono small" style="font-size: 0.76rem;">
                                            <?= date('M d, h:i A', strtotime($r['departure_time'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $r['sla_info']['class'] ?> small py-1 px-2">
                                            <i class="fa-solid fa-stopwatch me-1"></i><?= $r['sla_info']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary py-1 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#actionModal<?= $r['id'] ?>">
                                            <i class="fa-solid fa-gavel me-1"></i> Review & Act
                                        </button>

                                        <!-- Review & Act Modal -->
                                        <div class="modal fade text-start" id="actionModal<?= $r['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light">
                                                        <h6 class="modal-title fw-bold text-dark">
                                                            Review VRS: <?= esc($r['request_number']) ?>
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="<?= base_url('approvals/' . $r['id'] . '/action') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-body p-4">
                                                            <div class="mb-3">
                                                                <div class="small text-muted">Destination:</div>
                                                                <div class="fw-bold text-dark"><?= esc($r['destination']) ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="small text-muted">Purpose:</div>
                                                                <div class="small text-dark p-2 rounded bg-light border"><?= nl2br(esc($r['purpose'])) ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="small text-muted">Passengers:</div>
                                                                <div class="mono small text-dark"><?= nl2br(esc($r['passenger_names'])) ?></div>
                                                            </div>
                                                            <?php if ((int)$r['is_rush_request'] === 1 && !empty($r['justification_file'])): ?>
                                                                <div class="alert alert-danger p-2 small mb-3">
                                                                    <strong>Emergency Memo Attached:</strong><br>
                                                                    <?= esc($r['justification_notes']) ?><br>
                                                                    <a href="<?= base_url($r['justification_file']) ?>" target="_blank" class="fw-bold text-danger text-decoration-underline">
                                                                        <i class="fa-solid fa-file-pdf me-1"></i> View Justification Document
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-dark">Endorsement Remarks</label>
                                                                <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. Endorsed. Essential state coverage."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light p-3">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger">
                                                                Disapprove
                                                            </button>
                                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success px-3">
                                                                <i class="fa-solid fa-check me-1"></i> Endorse for Admin Clearance
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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

    <!-- Tier 2 Pane (Admin Head Authorization) -->
    <div class="tab-pane fade" id="tier2-pane" role="tabpanel">
        <div class="card-panel border shadow-sm">
            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark small text-uppercase">Tier 2: Administrative Division Head Authorization Queue</h6>
                    <div class="text-muted" style="font-size: 0.72rem;">Final travel authorization by Atty. Julius S. De Peralta. Approval automatically generates Section A of Driver's Trip Ticket.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>VRS No</th>
                            <th>Requesting Office & OIC Endorser</th>
                            <th>Destination & Purpose</th>
                            <th>Schedule</th>
                            <th>24h SLA Countdown</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingAdmin)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success opacity-50"></i>
                                    No pending Tier 2 requests. All endorsed requests have been authorized!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingAdmin as $r): ?>
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
                                        <div class="text-muted small" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-check-double text-success me-1"></i>Endorsed by: <?= esc($r['oic_approver_name'] ?? 'OIC') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 240px;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i><?= esc($r['destination']) ?>
                                        </div>
                                        <div class="text-muted small text-truncate" style="max-width: 240px; font-size: 0.72rem;">
                                            <?= esc($r['purpose']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mono small" style="font-size: 0.76rem;">
                                            <?= date('M d, h:i A', strtotime($r['departure_time'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $r['sla_info']['class'] ?> small py-1 px-2">
                                            <i class="fa-solid fa-stopwatch me-1"></i><?= $r['sla_info']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-success py-1 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#adminModal<?= $r['id'] ?>">
                                            <i class="fa-solid fa-stamp me-1"></i> Authorize Trip
                                        </button>

                                        <!-- Tier 2 Admin Modal -->
                                        <div class="modal fade text-start" id="adminModal<?= $r['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light">
                                                        <h6 class="modal-title fw-bold text-dark">
                                                            Authorize VRS: <?= esc($r['request_number']) ?>
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="<?= base_url('approvals/' . $r['id'] . '/action') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-body p-4">
                                                            <div class="alert alert-success border-success-subtle p-2 px-3 rounded small mb-3">
                                                                <i class="fa-solid fa-circle-check text-success me-1"></i>
                                                                <strong>Tier 1 Endorsement Verified:</strong> <?= esc($r['oic_approver_name']) ?> approved this trip on <?= date('M d, Y', strtotime($r['oic_action_at'])) ?>.
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="small text-muted">Destination:</div>
                                                                <div class="fw-bold text-dark"><?= esc($r['destination']) ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="small text-muted">Passengers (<?= esc($r['passenger_count']) ?>):</div>
                                                                <div class="mono small text-dark p-2 rounded bg-light border"><?= nl2br(esc($r['passenger_names'])) ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-dark">Administrative Division Authorization Notes</label>
                                                                <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. Authorized for official dispatch. Motorpool to assign unit."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light p-3">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger">
                                                                Disapprove
                                                            </button>
                                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success px-4 fw-semibold">
                                                                <i class="fa-solid fa-stamp me-1"></i> Officially Authorize & Send to Dispatch
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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

    <!-- Recently Approved Pane -->
    <div class="tab-pane fade" id="history-pane" role="tabpanel">
        <div class="card-panel border shadow-sm">
            <div class="p-3 border-bottom bg-light">
                <h6 class="mb-0 fw-bold text-dark small text-uppercase">Recently Authorized Requests</h6>
            </div>
            <div class="table-responsive">
                <table class="table-minimal">
                    <thead>
                        <tr>
                            <th>VRS No</th>
                            <th>Division</th>
                            <th>Destination</th>
                            <th>Authorized By</th>
                            <th>Action Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentlyApproved)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No recently authorized requests.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentlyApproved as $r): ?>
                                <tr>
                                    <td class="mono fw-bold"><?= esc($r['request_number']) ?></td>
                                    <td class="small"><?= esc($r['office_name'] ?? 'Central Office') ?></td>
                                    <td class="small fw-medium"><?= esc($r['destination']) ?></td>
                                    <td class="small"><?= esc($r['admin_approver_name'] ?? 'Admin Head') ?></td>
                                    <td class="mono small text-muted"><?= $r['admin_action_at'] ? date('M d, Y h:i A', strtotime($r['admin_action_at'])) : date('M d, Y', strtotime($r['updated_at'])) ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
