<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\TripModel;
use App\Models\TelemetryModel;

class TrackingController extends BaseController
{
    public function index()
    {
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->select('vehicles.*, 
                                          CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, 
                                          drivers.phone AS driver_phone,
                                          trips.trip_number, trips.origin_address, trips.destination_address, 
                                          trips.origin_lat, trips.origin_lng, trips.destination_lat, trips.destination_lng,
                                          trips.cargo_type')
                                ->join('drivers', 'drivers.id = vehicles.current_driver_id', 'left')
                                ->join('trips', 'trips.vehicle_id = vehicles.id AND trips.status IN ("in_transit", "dispatched")', 'left')
                                ->findAll();

        $geofenceChecker = new \App\Libraries\GeofenceChecker();
        foreach ($vehicles as &$v) {
            $lat = (float)($v['current_latitude'] ?? 0);
            $lng = (float)($v['current_longitude'] ?? 0);
            $fence = ($lat != 0 && $lng != 0) ? $geofenceChecker->check($lat, $lng) : null;
            $v['current_geofence'] = $fence ? $fence['name'] : 'Open Corridor';
        }

        $googleMapsConfig = config('GoogleMaps');
        $apiKey = $googleMapsConfig ? $googleMapsConfig->apiKey : env('GOOGLE_MAPS_API_KEY', '');

        return view('tracking/index', [
            'title'            => 'Real-Time Fleet Telematics & GPS Tracking',
            'vehicles'         => $vehicles,
            'googleMapsApiKey' => $apiKey,
        ]);
    }

    public function liveData()
    {
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->select('vehicles.*, 
                                          CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, 
                                          drivers.phone AS driver_phone,
                                          trips.trip_number, trips.origin_address, trips.destination_address, 
                                          trips.origin_lat, trips.origin_lng, trips.destination_lat, trips.destination_lng,
                                          trips.cargo_type')
                                ->join('drivers', 'drivers.id = vehicles.current_driver_id', 'left')
                                ->join('trips', 'trips.vehicle_id = vehicles.id AND trips.status IN ("in_transit", "dispatched")', 'left')
                                ->findAll();

        $geofenceChecker = new \App\Libraries\GeofenceChecker();
        foreach ($vehicles as &$v) {
            $lat = (float)($v['current_latitude'] ?? 0);
            $lng = (float)($v['current_longitude'] ?? 0);
            $fence = ($lat != 0 && $lng != 0) ? $geofenceChecker->check($lat, $lng) : null;
            $v['current_geofence'] = $fence ? $fence['name'] : 'Open Corridor';
        }

        return $this->response->setHeader('Access-Control-Allow-Origin', '*')->setJSON([
            'status'    => 'success',
            'timestamp' => date('Y-m-d H:i:s'),
            'vehicles'  => $vehicles,
        ]);
    }

    /**
     * Simulation action: advances vehicles that are in transit along their route
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

            // Small jitter towards destination
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

            // Save telemetry log
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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Telemetry simulated for {$updatedCount} vehicles in transit.",
        ]);
    }
}
