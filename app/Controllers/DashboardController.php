<?php

namespace App\Controllers;

use App\Models\VehicleModel;
use App\Models\DriverModel;
use App\Models\TripModel;
use App\Models\MaintenanceModel;
use App\Models\FuelLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $vehicleModel = new VehicleModel();
        $driverModel = new DriverModel();
        $tripModel = new TripModel();
        $maintenanceModel = new MaintenanceModel();
        $fuelModel = new FuelLogModel();

        // 1. KPI Counts
        $totalVehicles = $vehicleModel->countAllResults();
        $inTransitVehicles = $vehicleModel->where('status', 'in_transit')->countAllResults();
        $activeVehicles = $vehicleModel->where('status', 'active')->countAllResults();
        $maintenanceVehicles = $vehicleModel->where('status', 'maintenance')->countAllResults();

        $totalDrivers = $driverModel->countAllResults();
        $availableDrivers = $driverModel->where('status', 'available')->countAllResults();
        $onTripDrivers = $driverModel->where('status', 'on_trip')->countAllResults();

        $activeTrips = $tripModel->whereIn('status', ['dispatched', 'in_transit'])->countAllResults();
        $scheduledTrips = $tripModel->where('status', 'scheduled')->countAllResults();

        $openMaintenance = $maintenanceModel->whereIn('status', ['scheduled', 'in_progress'])->countAllResults();

        // Total fuel cost
        $fuelSummary = $fuelModel->selectSum('total_cost')->first();
        $totalFuelCost = $fuelSummary['total_cost'] ?? 0;

        // 2. Live vehicles for interactive map widget
        $liveVehicles = $vehicleModel->select('vehicles.*, CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, drivers.phone AS driver_phone')
                                    ->join('drivers', 'drivers.id = vehicles.current_driver_id', 'left')
                                    ->findAll();

        // 3. Recent trips
        $recentTrips = $tripModel->getTripsWithDetails(5);

        // 4. Maintenance Alerts
        $maintenanceAlerts = $maintenanceModel->getRecordsWithVehicle(5);

        $data = [
            'title'               => 'Fleet Command Center',
            'totalVehicles'       => $totalVehicles,
            'inTransitVehicles'   => $inTransitVehicles,
            'activeVehicles'      => $activeVehicles,
            'maintenanceVehicles' => $maintenanceVehicles,
            'totalDrivers'        => $totalDrivers,
            'availableDrivers'    => $availableDrivers,
            'onTripDrivers'       => $onTripDrivers,
            'activeTrips'         => $activeTrips,
            'scheduledTrips'      => $scheduledTrips,
            'openMaintenance'     => $openMaintenance,
            'totalFuelCost'       => $totalFuelCost,
            'liveVehicles'        => $liveVehicles,
            'recentTrips'         => $recentTrips,
            'maintenanceAlerts'   => $maintenanceAlerts,
        ];

        return view('dashboard/index', $data);
    }
}
