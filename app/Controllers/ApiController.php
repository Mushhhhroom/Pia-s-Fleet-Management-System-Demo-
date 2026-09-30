<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\TripModel;
use App\Models\TelemetryModel;
use App\Models\DriverModel;
use App\Libraries\RouteService;
use App\Libraries\GeofenceChecker;

class ApiController extends BaseController
{
    /**
     * Helper to return standard JSON with universal CORS headers
     */
    protected function corsResponse(array $data, int $statusCode = 200)
    {
        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }

    /**
     * Preflight CORS handler for external mapping apps / GIS integrations
     */
    public function optionsHandler()
    {
        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->setStatusCode(200);
    }

    /**
     * GET /api/v1/vehicles
     * Returns fleet asset registry
     */
    public function getVehicles()
    {
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->findAll();

        return $this->corsResponse([
            'status' => 'success',
            'count'  => count($vehicles),
            'data'   => $vehicles,
        ]);
    }

    /**
     * GET /api/v1/vehicles/(:num)
     * Returns individual vehicle details
     */
    public function getVehicle($id)
    {
        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->find($id);

        if (!$vehicle) {
            return $this->corsResponse([
                'status'  => 'error',
                'message' => 'Vehicle not found.',
            ], 404);
        }

        return $this->corsResponse([
            'status' => 'success',
            'data'   => $vehicle,
        ]);
    }

    /**
     * GET /api/v1/tracking or /api/v1/map
     * Core Real-Time Mapping Telematics API
     * Returns all fleet units with live coordinates, speed, fuel, driver, trip, and GeoJSON collection
     */
    public function getLiveTracking()
    {
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->select('vehicles.*, 
                                          CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, 
                                          drivers.phone AS driver_phone,
                                          drivers.license_type,
                                          trips.id AS active_trip_id,
                                          trips.trip_number, 
                                          trips.origin_address, 
                                          trips.destination_address, 
                                          trips.origin_lat, 
                                          trips.origin_lng, 
                                          trips.destination_lat, 
                                          trips.destination_lng, 
                                          trips.cargo_type,
                                          trips.priority AS trip_priority')
                                ->join('drivers', 'drivers.id = vehicles.current_driver_id', 'left')
                                ->join('trips', 'trips.vehicle_id = vehicles.id AND trips.status IN ("in_transit", "dispatched")', 'left')
                                ->findAll();

        $geofenceChecker = new GeofenceChecker();
        $features = [];

        foreach ($vehicles as &$v) {
            $lat = (float)($v['current_latitude'] ?? 0);
            $lng = (float)($v['current_longitude'] ?? 0);

            // Geofence resolution
            $fence = ($lat != 0 && $lng != 0) ? $geofenceChecker->check($lat, $lng) : null;
            $v['current_geofence'] = $fence ? $fence['name'] : 'Open Corridor';

            // Build GeoJSON Point Feature
            if ($lat != 0 && $lng != 0) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lng, $lat],
                    ],
                    'properties' => [
                        'vehicle_id'   => (int)$v['id'],
                        'vehicle_code' => $v['vehicle_code'],
                        'plate_number' => $v['plate_number'],
                        'make_model'   => $v['make'] . ' ' . $v['model'],
                        'status'       => $v['status'],
                        'speed_kmh'    => (float)$v['current_speed'],
                        'fuel_level'   => (float)$v['current_fuel_level'],
                        'driver_name'  => $v['driver_name'] ?: 'Unassigned',
                        'driver_phone' => $v['driver_phone'] ?: '',
                        'geofence'     => $v['current_geofence'],
                        'trip_number'  => $v['trip_number'] ?: null,
                    ]
                ];
            }
        }

        return $this->corsResponse([
            'status'    => 'success',
            'timestamp' => date('Y-m-d H:i:s'),
            'count'     => count($vehicles),
            'vehicles'  => $vehicles,
            'geojson'   => [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ],
        ]);
    }

    /**
     * GET /api/v1/tracking/(:num)
     * Returns tracking state for a specific vehicle
     */
    public function getVehicleTracking($id)
    {
        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->select('vehicles.*, 
                                         CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, 
                                         drivers.phone AS driver_phone,
                                         trips.id AS active_trip_id,
                                         trips.trip_number, 
                                         trips.origin_address, 
                                         trips.destination_address, 
                                         trips.origin_lat, 
                                         trips.origin_lng, 
                                         trips.destination_lat, 
                                         trips.destination_lng, 
                                         trips.cargo_type')
                               ->join('drivers', 'drivers.id = vehicles.current_driver_id', 'left')
                               ->join('trips', 'trips.vehicle_id = vehicles.id AND trips.status IN ("in_transit", "dispatched")', 'left')
                               ->where('vehicles.id', (int)$id)
                               ->first();

        if (!$vehicle) {
            return $this->corsResponse([
                'status'  => 'error',
                'message' => "Vehicle #{$id} not found.",
            ], 404);
        }

        $lat = (float)($vehicle['current_latitude'] ?? 0);
        $lng = (float)($vehicle['current_longitude'] ?? 0);
        $geofenceChecker = new GeofenceChecker();
        $fence = ($lat != 0 && $lng != 0) ? $geofenceChecker->check($lat, $lng) : null;
        $vehicle['current_geofence'] = $fence ? $fence['name'] : 'Open Corridor';

        return $this->corsResponse([
            'status'    => 'success',
            'timestamp' => date('Y-m-d H:i:s'),
            'vehicle'   => $vehicle,
        ]);
    }

    /**
     * GET /api/v1/tracking/(:num)/trail
     * Returns recent GPS breadcrumb trail coordinates for drawing polyline paths on the map
     */
    public function getVehicleTrail($id)
    {
        $limit = min(200, max(5, (int)($this->request->getGet('limit') ?: 60)));
        $telemetryModel = new TelemetryModel();

        $crumbs = $telemetryModel->where('vehicle_id', (int)$id)
                                ->orderBy('id', 'DESC')
                                ->findAll($limit);

        // Reverse to chronological order (oldest to newest)
        $crumbs = array_reverse($crumbs);

        $leafletCoords = [];
        $geoJsonCoords = [];

        foreach ($crumbs as $c) {
            $lat = (float)$c['latitude'];
            $lng = (float)$c['longitude'];
            if ($lat != 0 && $lng != 0) {
                $leafletCoords[] = [$lat, $lng];
                $geoJsonCoords[] = [$lng, $lat];
            }
        }

        return $this->corsResponse([
            'status'      => 'success',
            'vehicle_id'  => (int)$id,
            'point_count' => count($crumbs),
            'coordinates' => $leafletCoords, // [ [lat, lng], ... ] for Leaflet
            'geojson'     => [
                'type'        => 'Feature',
                'geometry'    => [
                    'type'        => 'LineString',
                    'coordinates' => $geoJsonCoords, // [ [lng, lat], ... ] GeoJSON standard
                ],
                'properties'  => [
                    'vehicle_id' => (int)$id,
                    'timestamp'  => date('Y-m-d H:i:s'),
                ]
            ],
            'points'      => $crumbs,
        ]);
    }

    /**
     * GET /api/v1/telemetry
     * Returns raw telemetry events with optional filters (?vehicle_id=, ?trip_id=, ?limit=)
     */
    public function getTelemetry()
    {
        $telemetryModel = new TelemetryModel();
        $builder = $telemetryModel->orderBy('id', 'DESC');

        if ($vehicleId = $this->request->getGet('vehicle_id')) {
            $builder->where('vehicle_id', (int)$vehicleId);
        }
        if ($tripId = $this->request->getGet('trip_id')) {
            $builder->where('trip_id', (int)$tripId);
        }

        $limit = min(200, max(1, (int)($this->request->getGet('limit') ?: 50)));
        $telemetry = $builder->findAll($limit);

        return $this->corsResponse([
            'status' => 'success',
            'count'  => count($telemetry),
            'data'   => $telemetry,
        ]);
    }

    /**
     * POST /api/v1/telemetry
     * Ingests GPS telemetry coordinate ping safely
     */
    public function ingestTelemetry()
    {
        $json = null;
        $raw = $this->request->getBody();
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $json = $decoded;
            }
        }
        if (empty($json)) {
            $json = $this->request->getPost();
        }
        if (empty($json)) {
            $json = $this->request->getGet();
        }

        if (empty($json['vehicle_id']) || !isset($json['latitude']) || !isset($json['longitude'])) {
            return $this->corsResponse([
                'status'  => 'error',
                'message' => 'Missing vehicle_id, latitude, or longitude.',
            ], 400);
        }

        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->find($json['vehicle_id']);
        if (!$vehicle) {
            return $this->corsResponse([
                'status'  => 'error',
                'message' => 'Vehicle does not exist in registry.',
            ], 404);
        }

        $telemetryModel = new TelemetryModel();
        $telemetryData = [
            'vehicle_id'      => (int)$json['vehicle_id'],
            'trip_id'         => !empty($json['trip_id']) ? (int)$json['trip_id'] : null,
            'latitude'        => (float)$json['latitude'],
            'longitude'       => (float)$json['longitude'],
            'speed_kmh'       => (float)($json['speed_kmh'] ?? 0.00),
            'heading'         => (float)($json['heading'] ?? 0.00),
            'fuel_level'      => (float)($json['fuel_level'] ?? $vehicle['current_fuel_level']),
            'engine_status'   => $json['engine_status'] ?? 'running',
            'battery_voltage' => isset($json['battery_voltage']) ? (float)$json['battery_voltage'] : null,
            'engine_temp_c'   => isset($json['engine_temp_c']) ? (float)$json['engine_temp_c'] : null,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $telemetryModel->insert($telemetryData);

        // Update live vehicle state
        $vehicleModel->update($json['vehicle_id'], [
            'current_latitude'   => $telemetryData['latitude'],
            'current_longitude'  => $telemetryData['longitude'],
            'current_speed'      => $telemetryData['speed_kmh'],
            'current_fuel_level' => $telemetryData['fuel_level'],
            'engine_status'      => $telemetryData['engine_status'],
        ]);

        return $this->corsResponse([
            'status'  => 'success',
            'message' => 'Telemetry coordinate ingested.',
            'data'    => $telemetryData,
        ]);
    }

    /**
     * GET or POST /api/v1/route
     * Computes road route geometry and distance between origin and destination coordinates
     */
    public function calculateRoute()
    {
        $originLat = $this->request->getVar('origin_lat');
        $originLng = $this->request->getVar('origin_lng');
        $destLat   = $this->request->getVar('destination_lat') ?? $this->request->getVar('dest_lat');
        $destLng   = $this->request->getVar('destination_lng') ?? $this->request->getVar('dest_lng');

        if (!isset($originLat) || !isset($originLng) || !isset($destLat) || !isset($destLng)) {
            return $this->corsResponse([
                'status'  => 'error',
                'message' => 'Required query or body parameters: origin_lat, origin_lng, destination_lat, destination_lng.',
                'example' => [
                    'origin_lat'      => 14.583333,
                    'origin_lng'      => 120.966667,
                    'destination_lat' => 15.185500,
                    'destination_lng' => 120.540600,
                ]
            ], 400);
        }

        $routeService = new RouteService();
        $route = $routeService->getRoute((float)$originLat, (float)$originLng, (float)$destLat, (float)$destLng);

        return $this->corsResponse([
            'status'       => 'success',
            'distance_km'  => $route['distance_km'],
            'duration_min' => $route['duration_min'],
            'source'       => $route['source'],
            'geojson'      => [
                'type'        => 'Feature',
                'geometry'    => [
                    'type'        => 'LineString',
                    'coordinates' => $route['geometry'] ?? [],
                ],
                'properties'  => [
                    'distance_km'  => $route['distance_km'],
                    'duration_min' => $route['duration_min'],
                ]
            ],
            'coordinates'  => $route['geometry'] ?? [],
        ]);
    }

    /**
     * POST /api/v1/tracking/simulate-step
     * Simulates forward motion along active transit routes for testing & demonstration
     */
    public function simulateStep()
    {
        $vehicleModel = new VehicleModel();
        $telemetryModel = new TelemetryModel();
        $tripModel = new TripModel();

        $activeTrips = $tripModel->where('status', 'in_transit')->findAll();
        $updatedCount = 0;

        foreach ($activeTrips as $trip) {
            $vehicle = $vehicleModel->find($trip['vehicle_id']);
            if (!$vehicle) continue;

            $destLat = (float)$trip['destination_lat'];
            $destLng = (float)$trip['destination_lng'];
            $curLat  = (float)$vehicle['current_latitude'];
            $curLng  = (float)$vehicle['current_longitude'];

            $dLat = ($destLat - $curLat) * 0.05 + ((rand(-10, 10)) / 100000);
            $dLng = ($destLng - $curLng) * 0.05 + ((rand(-10, 10)) / 100000);

            $newLat = round($curLat + $dLat, 7);
            $newLng = round($curLng + $dLng, 7);
            $newSpeed = rand(45, 75) + (rand(0, 9) / 10);
            $newFuel = max(10, round($vehicle['current_fuel_level'] - 0.2, 2));
            $newOdo = round($vehicle['odometer_km'] + 1.2, 2);

            $vehicleModel->update($vehicle['id'], [
                'current_latitude'   => $newLat,
                'current_longitude'  => $newLng,
                'current_speed'      => $newSpeed,
                'current_fuel_level' => $newFuel,
                'odometer_km'        => $newOdo,
                'engine_status'      => 'running',
            ]);

            $telemetryModel->insert([
                'vehicle_id'     => $vehicle['id'],
                'trip_id'        => $trip['id'],
                'latitude'       => $newLat,
                'longitude'      => $newLng,
                'speed_kmh'      => $newSpeed,
                'heading'        => rand(0, 360),
                'fuel_level'     => $newFuel,
                'engine_status'  => 'running',
                'battery_voltage'=> 14.1,
                'engine_temp_c'  => rand(86, 92),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $updatedCount++;
        }

        return $this->corsResponse([
            'status'        => 'success',
            'message'       => "Telemetry simulated for {$updatedCount} vehicles in transit.",
            'updated_count' => $updatedCount,
            'timestamp'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * GET /api/v1/trips/active
     * Returns trips currently in transit
     */
    public function getActiveTrips()
    {
        $tripModel = new TripModel();
        $trips = $tripModel->getTripsWithDetails(null, 'in_transit');

        return $this->corsResponse([
            'status' => 'success',
            'count'  => count($trips),
            'data'   => $trips,
        ]);
    }
}
