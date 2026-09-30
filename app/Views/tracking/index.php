<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="mono" style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2px;">
            Telematics Console &bull; Real-Time Operations
        </div>
        <h1 class="page-title mb-1">Live GPS Fleet Radar</h1>
        <p class="text-muted small mb-0">Monitor active transit corridors, satellite velocity telemetry, and vehicle breadcrumbs.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button id="btnSimulate" class="btn-corp btn-corp-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-satellite text-primary"></i>
            <span>Simulate Step</span>
        </button>
        <button id="btnRefresh" class="btn-corp btn-corp-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrows-rotate"></i>
            <span>Sync Telemetry</span>
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Fleet List Column -->
    <div class="col-lg-4 col-xl-3">
        <div class="card-panel" style="height: calc(100vh - 180px); display: flex; flex-direction: column;">
            <!-- Header with Search -->
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="mono" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: var(--text-muted);">
                        Transponders (<span id="vehicleCount"><?= count($vehicles) ?></span>)
                    </span>
                    <span class="telemetry-pulse" style="padding: 2px 8px; font-size: 0.7rem;">
                        <span class="pulse-dot"></span> Live
                    </span>
                </div>
                <!-- Vehicle Quick Search -->
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass" style="font-size: 0.75rem;"></i></span>
                    <input type="text" id="filterSearch" class="form-control border-start-0" placeholder="Filter code or plate..." style="font-size: 0.8rem;">
                </div>
            </div>
            
            <!-- Filter Pills -->
            <div class="p-2 border-bottom bg-light">
                <div class="btn-group w-100 btn-group-sm" role="group">
                    <button type="button" class="btn btn-sm btn-corp-secondary active btn-filter" data-filter="all" style="padding: 3px 6px; font-size: 0.72rem;">All</button>
                    <button type="button" class="btn btn-sm btn-corp-secondary btn-filter" data-filter="in_transit" style="padding: 3px 6px; font-size: 0.72rem;">Transit</button>
                    <button type="button" class="btn btn-sm btn-corp-secondary btn-filter" data-filter="active" style="padding: 3px 6px; font-size: 0.72rem;">Active</button>
                    <button type="button" class="btn btn-sm btn-corp-secondary btn-filter" data-filter="maintenance" style="padding: 3px 6px; font-size: 0.72rem;">Maint</button>
                </div>
            </div>

            <!-- Scrollable Vehicle List -->
            <div class="overflow-auto flex-grow-1" id="vehicleList" style="padding: 8px;">
                <?php foreach ($vehicles as $v): ?>
                    <div class="vehicle-item p-2 mb-2 rounded border bg-white" 
                         data-id="<?= $v['id'] ?>"
                         data-status="<?= $v['status'] ?>"
                         data-code="<?= esc(strtolower($v['vehicle_code'])) ?>"
                         data-plate="<?= esc(strtolower($v['plate_number'])) ?>"
                         data-lat="<?= $v['current_latitude'] ?>"
                         data-lng="<?= $v['current_longitude'] ?>"
                         onclick="focusVehicle(<?= $v['id'] ?>)"
                         style="cursor: pointer; transition: all 0.15s ease;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="mono fw-bold" style="font-size: 0.82rem; color: var(--text-heading);"><?= esc($v['vehicle_code']) ?></span>
                            <span class="status-badge status-<?= esc($v['status']) ?>" style="font-size: 0.65rem; padding: 2px 6px;">
                                <?= ucfirst(str_replace('_', ' ', $v['status'])) ?>
                            </span>
                        </div>
                        <div class="text-muted" style="font-size: 0.74rem; margin-bottom: 6px;">
                            <?= esc($v['make'] . ' ' . $v['model']) ?> &bull; <span class="mono"><?= esc($v['plate_number']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="font-size: 0.72rem; color: var(--text-muted);">
                            <span><i class="fa-solid fa-gauge-high text-primary me-1"></i> <span id="speed-<?= $v['id'] ?>" class="mono fw-semibold text-dark"><?= $v['current_speed'] ?></span> km/h</span>
                            <span><i class="fa-solid fa-gas-pump text-warning me-1"></i> <span id="fuel-<?= $v['id'] ?>" class="mono fw-semibold text-dark"><?= $v['current_fuel_level'] ?></span>%</span>
                            <?php if (!empty($v['driver_name'])): ?>
                                <span class="text-truncate" style="max-width: 80px;" title="<?= esc($v['driver_name']) ?>"><i class="fa-solid fa-user me-1"></i> <?= esc($v['driver_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Map Column -->
    <div class="col-lg-8 col-xl-9">
        <div class="card-panel" style="height: calc(100vh - 180px); overflow: hidden; position: relative;">
            <div id="telematicsMap" style="height: 100%; width: 100%;"></div>
            
            <!-- Map Overlay Legend -->
            <div style="position: absolute; bottom: 16px; right: 16px; z-index: 500; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(4px); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-subtle); box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-size: 0.72rem;">
                <div class="mono text-muted mb-1 text-uppercase" style="font-size: 0.65rem; font-weight: 600;">Status Spectrum</div>
                <div class="d-flex gap-3 align-items-center">
                    <span><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#2563eb; margin-right:4px;"></span>Active</span>
                    <span><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; margin-right:4px;"></span>In Transit</span>
                    <span><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#f59e0b; margin-right:4px;"></span>Maintenance</span>
                    <span><span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#ef4444; margin-right:4px;"></span>Out of Service</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let vehicles = <?= json_encode($vehicles) ?>;
    const map = L.map('telematicsMap', {
        zoomControl: true,
        attributionControl: false
    }).setView([14.5995, 120.9842], 11);

    // CartoDB Voyager Light Tiles for high-clarity minimalist mapping
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
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
                html: `<div style="background-color: ${color}; color: #ffffff; border-radius: 8px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2); border: 2px solid #ffffff; cursor: pointer; transition: transform 0.15s ease;">
                        <i class="fa-solid fa-truck" style="font-size: 13px;"></i>
                       </div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            const marker = L.marker([parseFloat(v.current_latitude), parseFloat(v.current_longitude)], { icon: icon });
            
            const popup = `
                <div style="min-width: 220px; font-family: var(--font-sans);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="mono" style="font-size: 0.88rem; color: #0f172a;">${v.vehicle_code}</strong>
                        <span class="status-badge status-${v.status}" style="font-size: 0.65rem;">${v.status.replace('_', ' ')}</span>
                    </div>
                    <div style="font-size: 0.76rem; color: #64748b; margin-bottom: 8px;">${v.make} ${v.model} &bull; <span class="mono">${v.plate_number}</span></div>
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; margin-bottom: 8px; font-size: 0.75rem;">
                        <div class="d-flex justify-content-between mb-1"><span>Speed:</span> <strong class="mono">${v.current_speed} km/h</strong></div>
                        <div class="d-flex justify-content-between mb-1"><span>Fuel Level:</span> <strong class="mono">${v.current_fuel_level}%</strong></div>
                        <div class="d-flex justify-content-between mb-1"><span>Driver:</span> <strong>${v.driver_name || 'Unassigned'}</strong></div>
                        ${v.trip_number ? `<div class="pt-1 mt-1 border-top"><div class="d-flex justify-content-between"><span>Trip:</span> <strong class="mono text-primary">${v.trip_number}</strong></div><div class="d-flex justify-content-between"><span>Cargo:</span> <strong>${v.cargo_type || 'General'}</strong></div></div>` : ''}
                    </div>

                    <a href="<?= base_url('vehicles/') ?>/${v.id}" class="btn-corp btn-corp-secondary w-100 text-center text-decoration-none d-block" style="font-size: 0.75rem; padding: 5px 8px;">
                        Vehicle Ledger &rarr;
                    </a>
                </div>
            `;
            marker.bindPopup(popup);
            markerGroup.addLayer(marker);
            markers[v.id] = marker;
        });

        if (markerGroup.getLayers().length > 0) {
            map.fitBounds(markerGroup.getBounds().pad(0.12));
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

    // Filter by Status & Text Search
    function applyFilters() {
        const activeFilter = document.querySelector('.btn-filter.active').getAttribute('data-filter');
        const searchTerm = document.getElementById('filterSearch').value.toLowerCase().trim();

        document.querySelectorAll('.vehicle-item').forEach(item => {
            const statusMatch = (activeFilter === 'all' || item.getAttribute('data-status') === activeFilter);
            const textMatch = !searchTerm || 
                              item.getAttribute('data-code').includes(searchTerm) || 
                              item.getAttribute('data-plate').includes(searchTerm);

            if (statusMatch && textMatch) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    document.getElementById('filterSearch').addEventListener('input', applyFilters);

    // Refresh Live Telematics Data
    async function refreshData() {
        try {
            const res = await fetch('<?= base_url('tracking/live-data') ?>');
            const data = await res.json();
            if (data.status === 'success') {
                vehicles = data.vehicles;
                renderMarkers();
                vehicles.forEach(v => {
                    const speedEl = document.getElementById(`speed-${v.id}`);
                    const fuelEl = document.getElementById(`fuel-${v.id}`);
                    if (speedEl) speedEl.innerText = v.current_speed;
                    if (fuelEl) fuelEl.innerText = v.current_fuel_level;
                });
            }
        } catch(e) {
            console.error('Failed to sync telemetry:', e);
        }
    }

    document.getElementById('btnRefresh').addEventListener('click', refreshData);

    // Simulate Step
    document.getElementById('btnSimulate').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Computing...</span>';

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
            console.error(e);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    });
</script>
<?= $this->endSection() ?>
