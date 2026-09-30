<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Fleet Vehicles Inventory</h3>
        <p class="text-muted mb-0">Manage registered commercial trucks, vans, payloads, and driver assignments.</p>
    </div>
    <a href="<?= base_url('vehicles/new') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Register Vehicle
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted">
                        <th>Vehicle Code</th>
                        <th>Plate & VIN</th>
                        <th>Make & Model</th>
                        <th>Type / Fuel</th>
                        <th>Odometer</th>
                        <th>Assigned Driver</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vehicles)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No vehicles in registry.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('vehicles/' . $v['id']) ?>" class="fw-bold text-decoration-none">
                                        <?= esc($v['vehicle_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= esc($v['plate_number']) ?></span>
                                    <span class="small text-muted d-block font-monospace"><?= esc($v['vin']) ?></span>
                                </td>
                                <td>
                                    <strong><?= esc($v['make']) ?></strong> <?= esc($v['model']) ?>
                                    <span class="badge bg-light text-secondary border ms-1"><?= esc($v['year']) ?></span>
                                </td>
                                <td>
                                    <span class="text-capitalize"><?= str_replace('_', ' ', esc($v['type'])) ?></span>
                                    <span class="small text-muted d-block text-capitalize"><?= esc($v['fuel_type']) ?></span>
                                </td>
                                <td>
                                    <strong><?= number_format($v['odometer_km'], 1) ?></strong> km
                                </td>
                                <td>
                                    <?php if (!empty($v['driver_name'])): ?>
                                        <span class="text-dark"><i class="fa-solid fa-user me-1 text-muted"></i> <?= esc($v['driver_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= esc($v['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', esc($v['status']))) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('vehicles/' . $v['id']) ?>" class="btn btn-outline-secondary" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('vehicles/' . $v['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
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
</div>
<?= $this->endSection() ?>
