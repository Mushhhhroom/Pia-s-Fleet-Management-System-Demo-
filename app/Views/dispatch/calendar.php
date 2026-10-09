<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: var(--primary-accent); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            Module 2 &bull; Fleet Segregation &amp; Allocation Calendar &bull; FR-2.1 / FR-2.2
        </div>
        <h1 class="page-title mb-1">Interactive Dispatch Calendar</h1>
        <p class="text-muted small mb-0">
            Live vehicle + driver allocations. The dispatch engine rejects any assignment that overlaps an existing booking (no double-booking).
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="<?= base_url('dispatch') ?>" class="btn-corp btn-corp-secondary text-decoration-none">
            <i class="fa-solid fa-table-list me-1"></i> Dispatch Board
        </a>
        <form action="<?= base_url('dispatch/calendar') ?>" method="GET" class="d-flex gap-2">
            <input type="month" name="month" class="form-control" value="<?= esc($month) ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<!-- Legend -->
<div class="d-flex flex-wrap gap-3 mb-4 p-3 rounded-2 border" style="background: var(--bg-secondary, #f8fafc); font-size: 0.78rem;">
    <span><span class="badge bg-secondary">issued</span> Awaiting departure</span>
    <span><span class="badge bg-primary">departed / in trip</span> On the road</span>
    <span><span class="badge bg-success">returned / completed</span> Back in compound</span>
    <span><span class="badge bg-warning text-dark">dedicated</span> Executive reserved unit</span>
    <span><span class="badge bg-info">pool</span> Shared pool unit</span>
</div>

<?php
    // Group allocations by day
    $byDay = [];
    foreach ($allocations as $a) {
        $byDay[$a['date']][] = $a;
    }

    $firstOfMonth = strtotime($month . '-01');
    $daysInMonth  = (int) date('t', $firstOfMonth);
    $startDow     = (int) date('w', $firstOfMonth); // 0 = Sunday
    $monthName    = date('F Y', $firstOfMonth);
?>

<div class="card-panel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h6 mb-0 fw-bold"><i class="fa-solid fa-calendar-days me-2" style="color: var(--primary-accent);"></i> <?= esc($monthName) ?> Allocation Board</h2>
        <span class="badge bg-light text-dark border p-2"><?= count($allocations) ?> booking(s)</span>
    </div>

    <div class="row g-1" style="border-left: 1px solid #e2e8f0;">
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow): ?>
            <div class="col" style="max-width: 14.2857%;">
                <div class="text-center fw-semibold text-muted py-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #e2e8f0;">
                    <?= $dow ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php $dayCounter = 1; ?>
    <?php for ($week = 0; $week < ceil(($startDow + $daysInMonth) / 7); $week++): ?>
        <div class="row g-1 mt-0" style="border-left: 1px solid #e2e8f0;">
            <?php for ($dow = 0; $dow < 7; $dow++): ?>
                <div class="col" style="max-width: 14.2857%; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; min-height: 110px; padding: 4px;">
                    <?php if (($week === 0 && $dow < $startDow) || $dayCounter > $daysInMonth): ?>
                        <!-- blank cell -->
                    <?php else: ?>
                        <?php
                            $dateKey  = sprintf('%s-%02d', $month, $dayCounter);
                            $today    = $dateKey === date('Y-m-d');
                            $dayItems = $byDay[$dateKey] ?? [];
                        ?>
                        <div class="text-end fw-semibold <?= $today ? 'text-white rounded-pill px-2' : 'text-muted' ?>"
                             style="font-size: 0.72rem; <?= $today ? 'background: var(--primary-accent); display:inline-block;' : '' ?>">
                            <?= $dayCounter ?>
                        </div>

                        <?php foreach ($dayItems as $a): ?>
                            <?php
                                $statusBadge = match ($a['status']) {
                                    'issued'              => 'bg-secondary',
                                    'departed'            => 'bg-primary',
                                    'arrived_dest'        => 'bg-primary',
                                    'departed_dest'       => 'bg-primary',
                                    'returned'            => 'bg-success',
                                    'completed'           => 'bg-success',
                                    default               => 'bg-secondary',
                                };
                            ?>
                            <a href="<?= $a['url'] ?>" class="d-block text-decoration-none mt-1 p-1 rounded border-start"
                               style="background: #f1f5f9; border-left-width: 3px !important; border-left-color: <?= $a['fleet_category'] === 'dedicated' ? '#f59e0b' : '#3b82f6' ?> !important;">
                                <div class="mono fw-bold text-dark" style="font-size: 0.62rem; line-height: 1.2;">
                                    <?= esc($a['plate'] ?: 'TBD') ?>
                                </div>
                                <div class="text-truncate text-dark" style="font-size: 0.6rem; line-height: 1.2;" title="<?= esc($a['destination']) ?>">
                                    <?= esc($a['destination'] ?: '—') ?>
                                </div>
                                <div class="text-truncate text-muted" style="font-size: 0.58rem; line-height: 1.2;">
                                    <?= esc($a['driver'] ?: 'Unassigned') ?>
                                </div>
                                <span class="badge <?= $statusBadge ?>" style="font-size: 0.55rem;"><?= esc($a['status']) ?></span>
                            </a>
                        <?php endforeach; ?>

                        <?php $dayCounter++; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    <?php endfor; ?>
</div>

<!-- FR-2.1 Fleet Segregation Summary -->
<div class="row g-3 mt-1">
    <?php
        $dedicated = array_values(array_filter($vehicles, fn($v) => ($v['fleet_category'] ?? 'pool') === 'dedicated'));
        $pool      = array_values(array_filter($vehicles, fn($v) => ($v['fleet_category'] ?? 'pool') !== 'dedicated'));
    ?>
    <div class="col-md-6">
        <div class="card-panel">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-user-tie me-2 text-warning"></i> Dedicated Executive Vehicles (FR-2.1)</h2>
            <?php if (empty($dedicated)): ?>
                <p class="text-muted small mb-0">No dedicated executive vehicles classified yet.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0 small">
                    <?php foreach ($dedicated as $v): ?>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="mono"><?= esc($v['plate_number']) ?></span>
                            <span class="text-muted"><?= esc($v['assigned_official'] ?? 'Unassigned official') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-panel">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-car-side me-2 text-primary"></i> Shared Pool Vehicles (FR-2.1)</h2>
            <?php if (empty($pool)): ?>
                <p class="text-muted small mb-0">No shared pool vehicles on file.</p>
            <?php else: ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($pool as $v): ?>
                        <span class="badge bg-light text-dark border p-2 mono"><?= esc($v['plate_number']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
