<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="fw-bold mb-1">Live Telematics & GPS Radar Console</h3>
        <p class="text-muted mb-0">Real-time satellite coordinates, velocity monitoring, and active route traces.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button id="btnSimulate" class="btn btn-warning shadow-sm">
            <i class="fa-solid fa-play me-1"></i> Simulate GPS Movement
        </button>
        <button id="btnRefresh" class="btn btn-outline-primary">
            <i class="fa-solid fa-rotate me-1"></i> Refresh Map
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Fleet List Column -->
    <div class="col-lg-4 col-xl-3">
        <div class="card-custom mb-3" style="max-height: calc(100vh - 200px); display: flex; flex-direction: column;">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold small text-uppercase text-secondary">Vehicles Radar (<span id="vehicleCount"><?= count($vehicles) ?></span>)</span>
                <span class="badge bg-success-subtle text-success small"><i class="fa-solid fa-circle fa-beat me-1" style="font-size: 8px;"></i>Live</span>
            </div>
            
            <div class="p-2 border-bottom">
                <div class="btn-group w-100 btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active btn-filter" data-filter="all">All</button>
                    <button type="button" class="btn btn-outline-secondary btn-filter" data-filter="in_transit">In Transit</button>
                    <button type="button" class="btn btn-outline-secondary btn-filter" data-filter="active">Active</button>
                    <button type="button" class="btn btn-outline-secondary btn-filter" data-filter="maintenance">Maint</button>
                </div>
            </div>

            <div class="list-group list-group-flush overflow-auto flex-grow-1" id="vehicleList">
                <?php foreach ($vehicles as $v): ?>
                    <button type="button" 
                            class="list-group-item list-group-item-action p-3 vehicle-item" 
                            data-id="<?= $v['id'] ?>"
                            data-status="<?= $v['status'] ?>"
                            data-lat="<?= $v['current_latitude'] ?>"
                            data-lng="<?= $v['current_longitude'] ?>"
                            onclick="focusVehicle(<?= $v['id'] ?>)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-primary"><?= esc($v['vehicle_code']) ?></strong>
                            <span class="badge-status badge-<?= esc($v['status']) ?>"><?= ucfirst(str_replace('_', ' ', $v['status'])) ?></span>
                        </div>
                        <div class="small text-muted mb-2"><?= esc($v['make'] . ' ' . $v['model']) ?> (<?= esc($v['plate_number']) ?>)</div>
                        <div class="d-flex justify-content-between small text-secondary">
                            <span><i class="fa-solid fa-gauge-simple-high me-1 text-info"></i> <span id="speed-<?= $v['id'] ?>"><?= $v['current_speed'] ?></span> km/h</span>
                            <span><i class="fa-solid fa-gas-pump me-1 text-warning"></i> <span id="fuel-<?= $v['id'] ?>"><?= $v['current_fuel_level'] ?></span>%</span>
                        </div>
                        <?php if (!empty($v['driver_name'])): ?>
                            <div class="mt-1 small text-dark"><i class="fa-solid fa-user me-1 text-muted"></i> <?= esc($v['driver_name']) ?></div>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Map Column -->
    <div class="col-lg-8 col-xl-9">
        <div class="card-custom">
            <div class="card-body p-0">
                <div id="telematicsMap" style="height: calc(100vh - 200px); width: 100%;"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let vehicles = <?= json_encode($vehicles) ?>;
    const map = L.map('telematicsMap').setView([14.5995, 120.9842], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let markers = {};
    const markerGroup = L.featureGroup().addTo(map);

    function getPinColor(status) {
        switch(status) {
            case 'in_transit': return '#10b981';
            case 'active': return '#2563eb';
            case 'maintenance': return '#f59e0b';
            case 'out_of_service': return '#ef4444';
            default: return '#64748b';
        }
    }

    function renderMarkers() {
        markerGroup.clearLayers();
        markers = {};

        vehicles.forEach(v => {
            if (!v.current_latitude || !v.current_longitude) return;

            const color = getPinColor(v.status);
            const icon = L.divIcon({
                className: 'custom-fleet-pin',
                html: `<div style="background-color: ${color}; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border: 2px solid #fff; cursor: pointer;">
                        <i class="fa-solid fa-truck" style="font-size: 14px;"></i>
                       </div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            const marker = L.marker([parseFloat(v.current_latitude), parseFloat(v.current_longitude)], { icon: icon });
            
            const popup = `
                <div style="min-width: 200px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-primary fs-6">${v.vehicle_code}</strong>
                        <span class="badge-status badge-${v.status}">${v.status.replace('_', ' ')}</span>
                    </div>
                    <div class="small text-muted mb-2">${v.make} ${v.model} &bull; ${v.plate_number}</div>
                    <div class="bg-light p-2 rounded mb-2 small">
                        <div><strong>Speed:</strong> ${v.current_speed} km/h</div>
                        <div><strong>Fuel:</strong> ${v.current_fuel_level}%</div>
                        <div><strong>Driver:</strong> ${v.driver_name || 'None'}</div>
                        ${v.trip_number ? `<div class="mt-1 pt-1 border-top"><strong>Trip:</strong> ${v.trip_number}<br><strong>Cargo:</strong> ${v.cargo_type}</div>` : ''}
                    </div>
                    <a href="<?= base_url('vehicles/') ?>/${v.id}" class="btn btn-sm btn-outline-primary w-100">Vehicle Profile &rarr;</a>
                </div>
            `;
            marker.bindPopup(popup);
            markerGroup.addLayer(marker);
            markers[v.id] = marker;
        });

        if (markerGroup.getLayers().length > 0) {
            map.fitBounds(markerGroup.getBounds().pad(0.15));
        }
    }

    renderMarkers();

    function focusVehicle(id) {
        const v = vehicles.find(item => item.id == id);
        if (v && markers[id]) {
            map.setView([parseFloat(v.current_latitude), parseFloat(v.current_longitude)], 14, { animate: true });
            markers[id].openPopup();
        }
    }

    // Filter Buttons
    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            document.querySelectorAll('.vehicle-item').forEach(item => {
                if (filter === 'all' || item.getAttribute('data-status') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Refresh Live Data
    async function refreshData() {
        try {
            const res = await fetch('<?= base_url('tracking/live-data') ?>');
            const data = await res.json();
            if (data.status === 'success') {
                vehicles = data.vehicles;
                renderMarkers();
                // Update speed/fuel text in sidebar
                vehicles.forEach(v => {
                    const speedEl = document.getElementById(`speed-${v.id}`);
                    const fuelEl = document.getElementById(`fuel-${v.id}`);
                    if (speedEl) speedEl.innerText = v.current_speed;
                    if (fuelEl) fuelEl.innerText = v.current_fuel_level;
                });
            }
        } catch(e) {
            console.error(e);
        }
    }

    document.getElementById('btnRefresh').addEventListener('click', refreshData);

    // Simulate GPS Movement Step
    document.getElementById('btnSimulate').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Simulating...';

        try {
            const res = await fetch('<?= base_url('tracking/simulate-step') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await res.json();
            await refreshData();
        } catch (e) {
            alert('Simulation error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Simulate GPS Movement';
        }
    });
</script>
<?= $this->endSection() ?>
