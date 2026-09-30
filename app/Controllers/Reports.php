<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\TripModel;
use App\Models\FuelLogModel;
use App\Models\MaintenanceModel;

class Reports extends BaseController
{
    public function index()
    {
        $tripModel = new TripModel();
        $fuelModel = new FuelLogModel();
        $maintenanceModel = new MaintenanceModel();
        $vehicleModel = new VehicleModel();

        // 1. Fleet KPIs
        $totalTrips = $tripModel->countAllResults();
        $completedTrips = $tripModel->where('status', 'completed')->countAllResults();
        $totalDistance = $tripModel->selectSum('distance_km')->first()['distance_km'] ?? 0;

        $fuelStats = $fuelModel->selectSum('liters', 'total_liters')
                              ->selectSum('total_cost', 'total_fuel_cost')
                              ->first();

        $maintenanceStats = $maintenanceModel->selectSum('cost', 'total_maint_cost')->first();

        // Vehicle summary
        $vehicles = $vehicleModel->getVehiclesWithDriver();

        return view('reports/index', [
            'title'             => 'Fleet Reports & Analytics',
            'totalTrips'        => $totalTrips,
            'completedTrips'    => $completedTrips,
            'totalDistance'     => $totalDistance,
            'totalLiters'       => $fuelStats['total_liters'] ?? 0,
            'totalFuelCost'     => $fuelStats['total_fuel_cost'] ?? 0,
            'totalMaintCost'    => $maintenanceStats['total_maint_cost'] ?? 0,
            'vehicles'          => $vehicles,
        ]);
    }

    public function exportCsv()
    {
        $type = $this->request->getGet('type') ?: 'trips';
        $filename = "fleet_{$type}_report_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        if ($type === 'trips') {
            fputcsv($output, ['Trip Number', 'Vehicle Code', 'Driver Name', 'Origin', 'Destination', 'Cargo Type', 'Distance (km)', 'Departure', 'Status']);
            $tripModel = new TripModel();
            $trips = $tripModel->getTripsWithDetails();
            foreach ($trips as $t) {
                fputcsv($output, [
                    $t['trip_number'],
                    $t['vehicle_code'],
                    $t['driver_name'],
                    $t['origin_address'],
                    $t['destination_address'],
                    $t['cargo_type'],
                    $t['distance_km'],
                    $t['scheduled_departure'],
                    $t['status'],
                ]);
            }
        } elseif ($type === 'fuel') {
            fputcsv($output, ['Receipt #', 'Vehicle Code', 'Driver Name', 'Date', 'Liters', 'Cost Per Liter', 'Total Cost', 'Station', 'Odometer']);
            $fuelModel = new FuelLogModel();
            $logs = $fuelModel->getLogsWithDetails();
            foreach ($logs as $f) {
                fputcsv($output, [
                    $f['receipt_no'],
                    $f['vehicle_code'],
                    $f['driver_name'],
                    $f['fuel_date'],
                    $f['liters'],
                    $f['cost_per_liter'],
                    $f['total_cost'],
                    $f['fuel_station'],
                    $f['odometer_km'],
                ]);
            }
        } elseif ($type === 'maintenance') {
            fputcsv($output, ['Reference #', 'Vehicle Code', 'Service Type', 'Priority', 'Scheduled Date', 'Cost', 'Status', 'Service Center']);
            $maintenanceModel = new MaintenanceModel();
            $records = $maintenanceModel->getRecordsWithVehicle();
            foreach ($records as $m) {
                fputcsv($output, [
                    $m['reference_no'],
                    $m['vehicle_code'],
                    $m['service_type'],
                    $m['priority'],
                    $m['scheduled_date'],
                    $m['cost'],
                    $m['status'],
                    $m['service_center'],
                ]);
            }
        }

        fclose($output);
        exit();
    }
}
