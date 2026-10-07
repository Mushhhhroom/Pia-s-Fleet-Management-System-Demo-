<?php

namespace App\Controllers;

use App\Models\TripRequestModel;
use App\Models\TripTicketModel;
use App\Models\VehicleModel;
use App\Models\DriverModel;
use App\Models\PmsRecordModel;
use App\Models\NotificationModel;
use App\Services\DispatchEngineService;
use App\Services\SecurityQrService;

class DispatchController extends BaseController
{
    protected TripRequestModel $requestModel;
    protected TripTicketModel $ticketModel;
    protected VehicleModel $vehicleModel;
    protected DriverModel $driverModel;
    protected PmsRecordModel $pmsModel;
    protected NotificationModel $notificationModel;
    protected DispatchEngineService $dispatchEngine;
    protected SecurityQrService $qrService;

    public function __construct()
    {
        $this->requestModel      = new TripRequestModel();
        $this->ticketModel       = new TripTicketModel();
        $this->vehicleModel      = new VehicleModel();
        $this->driverModel       = new DriverModel();
        $this->pmsModel          = new PmsRecordModel();
        $this->notificationModel = new NotificationModel();
        $this->dispatchEngine    = new DispatchEngineService();
        $this->qrService         = new SecurityQrService();
    }

    public function index()
    {
        // 1. Approved requests waiting for dispatch
        $pendingDispatch = $this->requestModel->getRequestsWithDetails(['status' => 'approved']);

        // 2. Active dispatched trips with tickets
        $activeDispatches = $this->ticketModel->getTicketsWithDetails();

        // 3. Vehicles with PMS Status
        $vehicles = $this->pmsModel->getPmsWithVehicles();

        // 4. Drivers
        $drivers = $this->driverModel->findAll();

        $data = [
            'title'            => 'Motorpool Dispatch Command & Heuristic Allocation',
            'pendingDispatch'  => $pendingDispatch,
            'activeDispatches' => $activeDispatches,
            'vehicles'         => $vehicles,
            'drivers'          => $drivers,
        ];

        return view('dispatch/index', $data);
    }

    public function assign($requestId)
    {
        $request = $this->requestModel->getRequestByIdWithDetails($requestId);
        if (!$request) {
            return redirect()->to('/dispatch')->with('error', 'Vehicle request not found.');
        }

        if ($request['status'] !== 'approved') {
            return redirect()->to('/dispatch')->with('error', 'Only authorized requests can be dispatched.');
        }

        // Calculate Smart Recommendations using Heuristic Formula
        $recommendations = $this->dispatchEngine->getRecommendations($request);

        $data = [
            'title'           => "Dispatch Assignment: {$request['request_number']}",
            'request'         => $request,
            'recommendations' => $recommendations,
        ];

        return view('dispatch/assign', $data);
    }

    public function storeAssignment($requestId)
    {
        $request = $this->requestModel->getRequestByIdWithDetails($requestId);
        if (!$request || $request['status'] !== 'approved') {
            return redirect()->to('/dispatch')->with('error', 'Invalid or unapproved request.');
        }

        $vehicleId = (int)$this->request->getPost('vehicle_id');
        $driverId  = (int)$this->request->getPost('driver_id');

        if (!$vehicleId || !$driverId) {
            return redirect()->back()->with('error', 'Please select both an authorized vehicle and driver.');
        }

        $vehicle = $this->vehicleModel->find($vehicleId);
        $driver  = $this->driverModel->find($driverId);

        if (!$vehicle || !$driver) {
            return redirect()->back()->with('error', 'Selected vehicle or driver does not exist.');
        }

        // Check if vehicle has PMS lock
        $pmsRecord = $this->pmsModel->where('vehicle_id', $vehicleId)->first();
        if ($pmsRecord && (int)$pmsRecord['is_locked'] === 1) {
            return redirect()->back()->with('error', "Security Lockout: Vehicle {$vehicle['plate_number']} is locked for 5,000 KM Preventive Maintenance. Please select an alternate vehicle.");
        }

        // 1. Generate Sequential Serial Number for e-DTT (ADMIN-F-001 rev1)
        $serialNo = $this->ticketModel->generateSerialNo();

        // 2. Generate Cryptographically Signed HMAC-SHA256 QR Token
        $qrToken = $this->qrService->generateToken(
            $serialNo,
            $vehicle['plate_number'],
            $driver['driver_code'],
            $request['departure_time']
        );

        // 3. Create Driver's Trip Ticket (ADMIN-F-001 rev1)
        // Automatically populates Section A from the approved VRS!
        $currentFuelLiters = ((float)($vehicle['current_fuel_level'] ?? 80) / 100.0) * (float)($vehicle['fuel_capacity_liters'] ?? 70);

        $ticketData = [
            'ticket_serial_no'          => $serialNo,
            'request_id'                => $requestId,
            'vehicle_id'                => $vehicleId,
            'driver_id'                 => $driverId,
            'qr_crypt_token'            => $qrToken,
            'status'                    => 'issued',
            'authorized_departure'      => $request['departure_time'],
            'authorized_return'         => $request['return_time'],
            'authorized_passengers'     => $request['passenger_names'],
            'authorized_destination'    => $request['destination'],
            'authorized_purpose'        => $request['purpose'],
            'admin_approver_name'       => $request['admin_approver_name'] ?? 'Atty. Julius S. De Peralta',
            'start_odometer'            => (float)$vehicle['odometer_km'],
            'fuel_balance_start_liters' => round($currentFuelLiters, 2),
            'fuel_issued_stock_liters'  => 0.00,
            'fuel_purchased_liters'     => 0.00,
            'fuel_used_liters'          => 0.00,
            'fuel_balance_end_liters'   => round($currentFuelLiters, 2),
        ];

        $ticketId = $this->ticketModel->insert($ticketData);

        // 4. Update Trip Request Status to Dispatched
        $this->requestModel->update($requestId, [
            'status' => 'dispatched',
        ]);

        // 5. Update Driver Status to 'on_trip'
        $this->driverModel->update($driverId, [
            'status' => 'on_trip',
        ]);

        // 6. Notify Driver
        if (!empty($driver['user_id'])) {
            $this->notificationModel->insert([
                'user_id'    => $driver['user_id'],
                'title'      => "Official Dispatch: {$serialNo}",
                'message'    => "You have been dispatched with vehicle {$vehicle['plate_number']} to {$request['destination']}. Present your e-DTT at Gate security.",
                'type'       => 'info',
                'link'       => "/tickets/{$ticketId}",
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 7. Notify Requestor
        $this->notificationModel->insert([
            'user_id'    => $request['requestor_id'],
            'title'      => "Trip Dispatched: {$request['request_number']}",
            'message'    => "Vehicle {$vehicle['make']} {$vehicle['model']} ({$vehicle['plate_number']}) with Driver {$driver['first_name']} {$driver['last_name']} has been assigned for your trip.",
            'type'       => 'success',
            'link'       => "/requests/{$requestId}",
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to("/tickets/{$ticketId}")->with('success', "Dispatched successfully! Driver's Trip Ticket {$serialNo} generated with cryptographic QR security token.");
    }
}
