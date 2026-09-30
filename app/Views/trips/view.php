<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold mb-0">Waybill: <?= esc($trip['trip_number']) ?></h3>
            <span class="badge-status badge-<?= esc($trip['status']) ?>"><?= ucfirst(str_replace('_', ' ', esc($trip['status']))) ?></span>
            <?php if ($trip['priority'] === 'urgent' || $trip['priority'] === 'critical'): ?>
                <span class="badge bg-danger-subtle text-danger"><?= ucfirst($trip['priority']) ?> Priority</span>
            <?php endif; ?>
        </div>
        <p class="text-muted mb-0">Dispatched: <strong><?= esc($trip['scheduled_departure']) ?></strong> | Cargo: <strong><?= esc($trip['cargo_type']) ?></strong> (<?= number_format($trip['cargo_weight_kg'], 0) ?> kg)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('trips') ?>" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> All Trips
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Map & Route Info (8 cols) -->
    <div class="col-lg-8">
        <div class="card-custom mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-map-location-dot text-primary me-2"></i> Live Transit Corridor Route Map</span>
                <span class="small text-muted">Distance: <strong><?= $trip['distance_km'] ?> km</strong></span>
            </div>
            <div class="card-body p-0">
                <div id="tripMap" style="height: 420px; width: 100%;"></div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header">
                <i class="fa-solid fa-timeline text-primary me-2"></i> Route Origin & Destination Summary
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 border-end">
                        <div class="text-success small fw-bold text-uppercase"><i class="fa-solid fa-circle-dot me-1"></i> Origin Pickup</div>
                        <h6 class="fw-bold mt-1 text-dark"><?= esc($trip['origin_address']) ?></h6>
                        <div class="small text-muted font-monospace">Lat: <?= $trip['origin_lat'] ?>, Lng: <?= $trip['origin_lng'] ?></div>
                        <div class="small text-secondary mt-1">Scheduled: <?= esc($trip['scheduled_departure']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-danger small fw-bold text-uppercase"><i class="fa-solid fa-location-dot me-1"></i> Destination Dropoff</div>
                        <h6 class="fw-bold mt-1 text-dark"><?= esc($trip['destination_address']) ?></h6>
                        <div class="small text-muted font-monospace">Lat: <?= $trip['destination_lat'] ?>, Lng: <?= $trip['destination_lng'] ?></div>
                        <div class="small text-secondary mt-1">Target Arrival: <?= esc($trip['scheduled_arrival'] ?: 'TBD') ?></div>
                    </div>
                </div>

                <?php if (!empty($trip['notes'])): ?>
                    <div class="mt-3 pt-3 border-top">
                        <span class="small fw-bold text-secondary">Dispatcher Special Notes:</span>
                        <p class="small text-muted mb-0 mt-1"><?= nl2br(esc($trip['notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status & Details (4 cols) -->
    <div class="col-lg-4">
        <!-- Status Control Box -->
        <div class="card-custom mb-4">
            <div class="card-header bg-light">
                <i class="fa-solid fa-sliders text-primary me-2"></i> Update Dispatch Status
            </div>
            <div class="card-body">
                <form action="<?= base_url('trips/' . $trip['id'] . '/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current State</label>
                        <select name="status" class="form-select" id="tripStatusSelect">
                            <option value="scheduled" <?= $trip['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="dispatched" <?= $trip['status'] === 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                            <option value="in_transit" <?= $trip['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit (On Road)</option>
                            <option value="completed" <?= $trip['status'] === 'completed' ? 'selected' : '' ?>>Completed (Arrived)</option>
                            <option value="cancelled" <?= $trip['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3" id="endOdoField" style="<?= $trip['status'] === 'completed' ? '' : 'display: none;' ?>">
                        <label class="form-label small fw-semibold">Arrival End Odometer (km)</label>
                        <input type="number" step="0.1" name="end_odometer" class="form-control" placeholder="Current km" value="<?= old('end_odometer', $trip['end_odometer'] ?? ($trip['start_odometer'] + $trip['distance_km'])) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-check me-1"></i> Update Trip Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fa-solid fa-truck text-primary me-2"></i> Transport Asset
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1"><?= esc($trip['vehicle_code']) ?></h5>
                <div class="small text-muted mb-3"><?= esc($trip['make'] . ' ' . $trip['model']) ?> &bull; Plate: <?= esc($trip['plate_number']) ?></div>
                
                <div class="d-flex justify-content-between small py-1 border-bottom">
                    <span class="text-muted">Current Speed:</span>
                    <strong><?= $trip['current_speed'] ?> km/h</strong>
                </div>
                <div class="d-flex justify-content-between small py-1 border-bottom">
                    <span class="text-muted">Fuel Level:</span>
                    <strong><?= $trip['current_fuel_level'] ?>%</strong>
                </div>
                <div class="d-flex justify-content-between small py-1">
                    <span class="text-muted">Start Odometer:</span>
                    <strong><?= number_format($trip['start_odometer'], 1) ?> km</strong>
                </div>

                <div class="mt-3">
                    <a href="<?= base_url('vehicles/' . $trip['vehicle_id']) ?>" class="btn btn-sm btn-outline-secondary w-100">
                        View Vehicle Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Driver Details -->
        <div class="card-custom">
            <div class="card-header">
                <i class="fa-solid fa-user text-primary me-2"></i> Assigned Operator
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-1"><?= esc($trip['driver_name']) ?></h5>
                <div class="small text-muted mb-3"><i class="fa-solid fa-phone me-1"></i> <?= esc($trip['driver_phone']) ?></div>
                <div class="small text-muted">License: <span class="font-monospace text-dark"><?= esc($trip['license_number']) ?></span></div>
                <div class="mt-3">
                    <a href="<?= base_url('drivers/' . $trip['driver_id']) ?>" class="btn btn-sm btn-outline-secondary w-100">
                        View Driver Scorecard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const origin = [<?= (float)$trip['origin_lat'] ?>, <?= (float)$trip['origin_lng'] ?>];
    const destination = [<?= (float)$trip['destination_lat'] ?>, <?= (float)$trip['destination_lng'] ?>];
    const vehiclePos = [<?= (float)$trip['current_latitude'] ?>, <?= (float)$trip['current_longitude'] ?>];

    const map = L.map('tripMap').setView(vehiclePos, 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Origin Marker (Green)
    L.circleMarker(origin, {
        radius: 9,
        fillColor: '#10b981',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
    }).addTo(map).bindPopup("<b>Pickup:</b> <?= esc($trip['origin_address']) ?>");

    // Destination Marker (Red)
    L.circleMarker(destination, {
        radius: 9,
        fillColor: '#ef4444',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
    }).addTo(map).bindPopup("<b>Delivery:</b> <?= esc($trip['destination_address']) ?>");

    // Draw route line
    const routeLine = L.polyline([origin, vehiclePos, destination], {
        color: '#2563eb',
        weight: 4,
        opacity: 0.8,
        dashArray: '8, 8'
    }).addTo(map);

    // Vehicle live pin
    const truckIcon = L.divIcon({
        className: 'custom-fleet-pin',
        html: `<div style="background-color: #2563eb; color: white; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border: 2px solid #fff;">
                <i class="fa-solid fa-truck" style="font-size: 16px;"></i>
               </div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19]
    });

    L.marker(vehiclePos, { icon: truckIcon }).addTo(map)
        .bindPopup("<b><?= esc($trip['vehicle_code']) ?></b><br>Speed: <?= $trip['current_speed'] ?> km/h")
        .openPopup();

    map.fitBounds(L.featureGroup([
        L.marker(origin),
        L.marker(destination),
        L.marker(vehiclePos)
    ]).getBounds().pad(0.2));

    // Toggle end odometer field on status change
    document.getElementById('tripStatusSelect').addEventListener('change', function() {
        const odoField = document.getElementById('endOdoField');
        if (this.value === 'completed') {
            odoField.style.display = 'block';
        } else {
            odoField.style.display = 'none';
        }
    });
</script>
<?= $this->endSection() ?>
