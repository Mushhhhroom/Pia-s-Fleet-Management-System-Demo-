<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Fleet Operations Command Center</h3>
        <p class="text-muted mb-0">Real-time status overview of vehicles, active dispatches, and maintenance alerts.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('tracking') ?>" class="btn btn-primary">
            <i class="fa-solid fa-map-location-dot me-1"></i> Full-Screen Map
        </a>
        <a href="<?= base_url('trips/new') ?>" class="btn btn-outline-dark">
            <i class="fa-solid fa-plus me-1"></i> New Dispatch
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Total Fleet</span>
                <h3 class="fw-bold mb-0 mt-1"><?= $totalVehicles ?></h3>
            </div>
            <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-truck"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">In Transit</span>
                <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $inTransitVehicles ?></h3>
            </div>
            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                <i class="fa-solid fa-route"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">In Service / Shop</span>
                <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $maintenanceVehicles ?></h3>
            </div>
            <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Active Drivers</span>
                <h3 class="fw-bold mb-0 mt-1"><?= $availableDrivers ?> <span class="fs-6 text-muted font-normal">/ <?= $totalDrivers ?></span></h3>
            </div>
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-id-card"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Active Trips</span>
                <h3 class="fw-bold mb-0 mt-1 text-primary"><?= $activeTrips ?></h3>
            </div>
            <div class="kpi-icon bg-purple bg-opacity-10 text-purple" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                <i class="fa-solid fa-dolly"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-semibold text-uppercase">Fuel Spend</span>
                <h3 class="fw-bold mb-0 mt-1 text-success fs-5">₱<?= number_format($totalFuelCost, 0) ?></h3>
            </div>
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="fa-solid fa-gas-pump"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Map Column -->
    <div class="col-lg-8">
        <div class="card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-satellite text-primary me-2"></i> Live Fleet GPS Radar</span>
                <a href="<?= base_url('tracking') ?>" class="btn btn-sm btn-link text-decoration-none">Open Telematics Console &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div id="dashMap" style="height: 380px; width: 100%;"></div>
            </div>
        </div>

        <!-- Active Trips Table -->
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-truck-moving text-primary me-2"></i> Active Logistics & Dispatch Trips</span>
                <a href="<?= base_url('trips') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>Trip #</th>
                                <th>Vehicle</th>
                                <th>Driver</th>
                                <th>Origin &rarr; Destination</th>
                                <th>Cargo</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTrips)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No active trips found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentTrips as $trip): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('trips/' . $trip['id']) ?>" class="fw-bold text-decoration-none">
                                                <?= esc($trip['trip_number']) ?>
                                            </a>
                                            <?php if ($trip['priority'] === 'urgent' || $trip['priority'] === 'critical'): ?>
                                                <span class="badge bg-danger-subtle text-danger ms-1"><?= ucfirst($trip['priority']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-semibold"><?= esc($trip['vehicle_code']) ?></span>
                                            <span class="small text-muted d-block"><?= esc($trip['plate_number']) ?></span>
                                        </td>
                                        <td>
                                            <span><?= esc($trip['driver_name']) ?></span>
                                            <span class="small text-muted d-block"><?= esc($trip['driver_phone']) ?></span>
                                        </td>
                                        <td style="max-width: 260px;">
                                            <div class="text-truncate small"><strong>From:</strong> <?= esc($trip['origin_address']) ?></div>
                                            <div class="text-truncate small"><strong>To:</strong> <?= esc($trip['destination_address']) ?></div>
                                        </td>
                                        <td class="small">
                                            <?= esc($trip['cargo_type']) ?><br>
                                            <span class="text-muted"><?= number_format($trip['cargo_weight_kg'], 0) ?> kg</span>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-<?= esc($trip['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $trip['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= base_url('trips/' . $trip['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                Track &rarr;
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

    <!-- Right Sidebar Column -->
    <div class="col-lg-4">
        <!-- Fleet Distribution Chart -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fa-solid fa-chart-pie text-primary me-2"></i> Fleet Status Distribution
            </div>
            <div class="card-body">
                <div style="height: 220px; position: relative;">
                    <canvas id="fleetStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i> Service & Work Orders</span>
                <a href="<?= base_url('maintenance') ?>" class="btn btn-sm btn-link text-decoration-none">Manage</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($maintenanceAlerts)): ?>
                        <li class="list-group-item text-center text-muted py-3">No pending maintenance orders.</li>
                    <?php else: ?>
                        <?php foreach ($maintenanceAlerts as $wo): ?>
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="text-dark small"><?= esc($wo['reference_no']) ?> (<?= esc($wo['vehicle_code']) ?>)</strong>
                                    <span class="badge-status badge-<?= esc($wo['status']) ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span>
                                </div>
                                <div class="text-muted small text-truncate mb-2"><?= esc($wo['service_type']) ?></div>
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span><i class="fa-regular fa-calendar me-1"></i> <?= esc($wo['scheduled_date']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= esc(ucfirst($wo['priority'])) ?> Priority</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize Dashboard Leaflet Map
    const vehiclesData = <?= json_encode($liveVehicles) ?>;
    const map = L.map('dashMap').setView([14.5995, 120.9842], 9);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markersGroup = L.featureGroup();

    vehiclesData.forEach(v => {
        if (!v.current_latitude || !v.current_longitude) return;

        let iconColor = '#2563eb'; // blue
        if (v.status === 'in_transit') iconColor = '#10b981'; // green
        if (v.status === 'maintenance') iconColor = '#f59e0b'; // amber
        if (v.status === 'out_of_service') iconColor = '#ef4444'; // red

        const customMarker = L.divIcon({
            className: 'custom-fleet-pin',
            html: `<div style="background-color: ${iconColor}; color: white; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 6px rgba(0,0,0,0.3); border: 2px solid #fff;">
                    <i class="fa-solid fa-truck" style="font-size: 14px;"></i>
                   </div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        const marker = L.marker([parseFloat(v.current_latitude), parseFloat(v.current_longitude)], { icon: customMarker });
        
        const popupContent = `
            <div style="min-width: 180px;">
                <h6 class="fw-bold mb-1">${v.vehicle_code} (${v.plate_number})</h6>
                <div class="small text-muted mb-1">${v.make} ${v.model}</div>
                <div class="small mb-1"><strong>Status:</strong> <span class="badge-status badge-${v.status}">${v.status.replace('_', ' ')}</span></div>
                <div class="small mb-1"><strong>Speed:</strong> ${v.current_speed} km/h</div>
                <div class="small mb-1"><strong>Fuel:</strong> ${v.current_fuel_level}%</div>
                <div class="small mb-2"><strong>Driver:</strong> ${v.driver_name || 'Unassigned'}</div>
                <a href="<?= base_url('vehicles/') ?>/${v.id}" class="btn btn-xs btn-primary btn-sm w-100 text-white text-decoration-none">Vehicle Profile</a>
            </div>
        `;
        marker.bindPopup(popupContent);
        markersGroup.addLayer(marker);
    });

    markersGroup.addTo(map);
    if (markersGroup.getLayers().length > 0) {
        map.fitBounds(markersGroup.getBounds().pad(0.15));
    }

    // Chart.js Fleet Status
    const ctx = document.getElementById('fleetStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['In Transit', 'Active Available', 'In Maintenance'],
            datasets: [{
                data: [<?= (int)$inTransitVehicles ?>, <?= (int)$activeVehicles ?>, <?= (int)$maintenanceVehicles ?>],
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
<?= $this->endSection() ?>
