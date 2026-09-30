<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            Asset Management &bull; Fleet Inventory
        </div>
        <h1 class="page-title mb-1">Vehicle Fleet Registry</h1>
        <p class="text-muted small mb-0">Commercial transport units, VIN registrations, telematics status, and driver assignments.</p>
    </div>
    <a href="<?= base_url('vehicles/new') ?>" class="btn-corp btn-corp-primary text-decoration-none">
        <i class="fa-solid fa-plus me-1"></i> Register Transport Unit
    </a>
</div>

<!-- Vehicles Ledger Table Panel -->
<div class="card-panel">
    <div class="table-responsive">
        <table class="table-minimal">
            <thead>
                <tr>
                    <th>Unit Code</th>
                    <th>Plate & Identification</th>
                    <th>Make & Model</th>
                    <th>Powertrain / Type</th>
                    <th>Cumulative Odometer</th>
                    <th>Assigned Operator</th>
                    <th>Telemetry State</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-truck fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            No commercial vehicles enrolled in registry.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('vehicles/' . $v['id']) ?>" class="mono fw-bold text-decoration-none" style="color: #0f172a; font-size: 0.85rem;">
                                    <?= esc($v['vehicle_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="mono fw-semibold" style="font-size: 0.82rem; color: var(--text-heading);"><?= esc($v['plate_number']) ?></div>
                                <div class="mono text-muted" style="font-size: 0.7rem;"><?= esc($v['vin']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.84rem; color: var(--text-heading);">
                                    <?= esc($v['make']) ?> <?= esc($v['model']) ?>
                                </div>
                                <span class="mono text-muted" style="font-size: 0.72rem;"><?= esc($v['year']) ?> Model</span>
                            </td>
                            <td>
                                <div class="text-capitalize" style="font-size: 0.8rem; color: var(--text-heading);"><?= str_replace('_', ' ', esc($v['type'])) ?></div>
                                <div class="mono text-muted" style="font-size: 0.72rem; text-transform: uppercase;"><?= esc($v['fuel_type']) ?></div>
                            </td>
                            <td>
                                <span class="mono fw-semibold" style="font-size: 0.84rem; color: var(--text-heading);"><?= number_format($v['odometer_km'], 1) ?></span>
                                <span class="mono text-muted" style="font-size: 0.72rem;">km</span>
                            </td>
                            <td>
                                <?php if (!empty($v['driver_name'])): ?>
                                    <div class="fw-medium" style="font-size: 0.82rem; color: var(--text-heading);">
                                        <i class="fa-solid fa-user me-1 text-muted" style="font-size: 0.75rem;"></i> <?= esc($v['driver_name']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="mono text-muted" style="font-size: 0.72rem; font-style: italic;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= esc($v['status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', esc($v['status']))) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= base_url('vehicles/' . $v['id']) ?>" class="btn-corp btn-corp-secondary text-decoration-none" title="Vehicle Profile">
                                        <i class="fa-solid fa-chart-simple"></i>
                                    </a>
                                    <a href="<?= base_url('vehicles/' . $v['id'] . '/edit') ?>" class="btn-corp btn-corp-secondary text-decoration-none" title="Edit Vehicle">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
