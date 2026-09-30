<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\TripModel;
use App\Models\TelemetryModel;

class ApiController extends BaseController
{
    public function getVehicles()
    {
        $vehicleModel = new VehicleModel();
        $vehicles = $vehicleModel->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'count'  => count($vehicles),
            'data'   => $vehicles,
        ]);
    }

    public function getVehicle($id)
    {
        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->find($id);

        if (!$vehicle) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Vehicle not found',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $vehicle,
        ]);
    }

    public function ingestTelemetry()
    {
        $json = null;
        try {
            $json = $this->request->getJSON(true);
        } catch (\Throwable $e) {
            $raw = $this->request->getBody();
            if (!empty($raw)) {
                $json = json_decode($raw, true);
            }
        }
        if (empty($json)) {
            $json = $this->request->getPost();
        }

        if (empty($json['vehicle_id']) || !isset($json['latitude']) || !isset($json['longitude'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Missing vehicle_id, latitude, or longitude.',
            ]);
        }

        $vehicleModel = new VehicleModel();
        $vehicle = $vehicleModel->find($json['vehicle_id']);
        if (!$vehicle) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Vehicle does not exist in registry.',
            ]);
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

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Telemetry coordinate ingested.',
            'data'    => $telemetryData,
        ]);
    }

    public function getActiveTrips()
    {
        $tripModel = new TripModel();
        $trips = $tripModel->getTripsWithDetails(null, 'in_transit');

        return $this->response->setJSON([
            'status' => 'success',
            'count'  => count($trips),
            'data'   => $trips,
        ]);
    }
}
