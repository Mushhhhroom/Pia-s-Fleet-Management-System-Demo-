<?php

namespace App\Controllers;

use App\Models\TripTicketModel;
use App\Models\TripRequestModel;
use App\Models\GateLogModel;
use App\Models\VehicleModel;
use App\Models\DriverModel;
use App\Models\NotificationModel;

class TripTicketController extends BaseController
{
    protected TripTicketModel $ticketModel;
    protected TripRequestModel $requestModel;
    protected GateLogModel $gateLogModel;
    protected VehicleModel $vehicleModel;
    protected DriverModel $driverModel;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->ticketModel       = new TripTicketModel();
        $this->requestModel      = new TripRequestModel();
        $this->gateLogModel      = new GateLogModel();
        $this->vehicleModel      = new VehicleModel();
        $this->driverModel       = new DriverModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $statusFilter = $this->request->getGet('status');
        $filters = [];
        if ($statusFilter) {
            $filters['status'] = $statusFilter;
        }

        $session = session();
        $userRole = $session->get('user_role');
        $userId   = $session->get('user_id');

        // If driver, only show tickets assigned to them
        if ($userRole === 'driver') {
            $driver = $this->driverModel->where('user_id', $userId)->first();
            if ($driver) {
                $filters['driver_id'] = $driver['id'];
            }
        }

        $tickets = $this->ticketModel->getTicketsWithDetails($filters);

        $data = [
            'title'        => "Driver's Trip Tickets (ADMIN-F-001 rev1)",
            'tickets'      => $tickets,
            'activeFilter' => $statusFilter ?? 'all',
            'userRole'     => $userRole,
        ];

        return view('tickets/index', $data);
    }

    public function show($id)
    {
        $ticket = $this->ticketModel->getTicketByIdWithDetails($id);
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', "Trip ticket not found.");
        }

        // Fetch gate logs for this ticket
        $gateLogs = $this->gateLogModel->where('trip_ticket_id', $id)->orderBy('id', 'ASC')->findAll();

        $data = [
            'title'    => "Driver's Trip Ticket: {$ticket['ticket_serial_no']}",
            'ticket'   => $ticket,
            'gateLogs' => $gateLogs,
            'userRole' => session()->get('user_role'),
        ];

        return view('tickets/show', $data);
    }

    public function printDtt($id)
    {
        $ticket = $this->ticketModel->getTicketByIdWithDetails($id);
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', "Trip ticket not found.");
        }

        $data = [
            'title'  => "Official Print - {$ticket['ticket_serial_no']}",
            'ticket' => $ticket,
        ];

        return view('tickets/print_dtt', $data);
    }

    public function updateSectionB($id)
    {
        $ticket = $this->ticketModel->find($id);
        if (!$ticket) {
            return redirect()->to('/tickets')->with('error', "Trip ticket not found.");
        }

        // Section B Timeline & Odometers
        $depTimeStr    = $this->request->getPost('departure_time');
        $arrDestStr    = $this->request->getPost('arrival_dest_time');
        $depDestStr    = $this->request->getPost('departure_dest_time');
        $arrBackStr    = $this->request->getPost('arrival_back_time');

        $startOdo      = (float)$this->request->getPost('start_odometer');
        $destOdo       = (float)$this->request->getPost('dest_odometer');
        $returnOdo     = (float)$this->request->getPost('return_odometer');
        $totalDistKm   = max(0, $returnOdo - $startOdo);

        // Section B Fuel Balance Formula:
        // Balance End = Balance Start + Issued from Stock + Purchased on Trip - Used During Trip
        $fuelStart     = (float)$this->request->getPost('fuel_balance_start_liters');
        $fuelIssued    = (float)$this->request->getPost('fuel_issued_stock_liters');
        $fuelPurchased = (float)$this->request->getPost('fuel_purchased_liters');
        $fuelCost      = (float)$this->request->getPost('fuel_purchased_cost');
        $fuelUsed      = (float)$this->request->getPost('fuel_used_liters');

        $fuelEnd = round(($fuelStart + $fuelIssued + $fuelPurchased) - $fuelUsed, 2);

        // Fuel Efficiency Calculation & Anomaly Detection (>20% deviation)
        $fuelEfficiency = ($fuelUsed > 0 && $totalDistKm > 0) ? round($totalDistKm / $fuelUsed, 2) : 0.00;

        // Expected benchmark ~ 9-11 km/L for diesel vans/pickups
        $expectedKml = 10.00;
        $isAnomaly = 0;
        if ($fuelEfficiency > 0) {
            $deviation = abs($fuelEfficiency - $expectedKml) / $expectedKml;
            if ($deviation > 0.20) {
                $isAnomaly = 1;
            }
        }

        // Consumables
        $gearOil  = (float)$this->request->getPost('gear_oil_liters');
        $lubeOil  = (float)$this->request->getPost('lube_oil_liters');
        $grease   = (float)$this->request->getPost('grease_units');

        // Digital Certifications
        $driverCert = (int)$this->request->getPost('driver_certified');
        $passCert   = (int)$this->request->getPost('passenger_certified');
        $passName   = trim((string)$this->request->getPost('passenger_certifier_name'));

        $now = date('Y-m-d H:i:s');

        $updateData = [
            'departure_time'            => $depTimeStr ?: $ticket['departure_time'],
            'arrival_dest_time'         => $arrDestStr ?: null,
            'departure_dest_time'       => $depDestStr ?: null,
            'arrival_back_time'         => $arrBackStr ?: $ticket['arrival_back_time'],
            'start_odometer'            => $startOdo,
            'dest_odometer'             => $destOdo ?: null,
            'return_odometer'           => $returnOdo,
            'total_distance_km'         => $totalDistKm,
            'fuel_balance_start_liters' => $fuelStart,
            'fuel_issued_stock_liters'  => $fuelIssued,
            'fuel_purchased_liters'     => $fuelPurchased,
            'fuel_purchased_cost'       => $fuelCost,
            'fuel_used_liters'          => $fuelUsed,
            'fuel_balance_end_liters'   => $fuelEnd,
            'fuel_efficiency_kml'       => $fuelEfficiency,
            'fuel_anomaly_flag'         => $isAnomaly,
            'gear_oil_liters'           => $gearOil,
            'lube_oil_liters'           => $lubeOil,
            'grease_units'              => $grease,
            'driver_certified'          => $driverCert ? 1 : $ticket['driver_certified'],
            'driver_certified_at'       => $driverCert ? $now : $ticket['driver_certified_at'],
            'passenger_certified'       => $passCert ? 1 : $ticket['passenger_certified'],
            'passenger_certifier_name'  => $passName ?: $ticket['passenger_certifier_name'],
            'passenger_certified_at'    => $passCert ? $now : $ticket['passenger_certified_at'],
            'notes'                     => trim((string)$this->request->getPost('notes')),
        ];

        // If trip is returned and certifications completed, mark ticket and VRS completed
        if ($ticket['status'] === 'returned' && $driverCert && $passCert) {
            $updateData['status'] = 'completed';
            $this->requestModel->update($ticket['request_id'], ['status' => 'completed']);
        }

        $this->ticketModel->update($id, $updateData);

        $referer = $this->request->getServer('HTTP_REFERER') ?? '';
        if (str_contains($referer, 'driver/trips')) {
            return redirect()->to('/driver/trips')->with('success', "Driver Trip Ticket {$ticket['ticket_serial_no']} Section B updated successfully.");
        }

        return redirect()->to("/tickets/{$id}")->with('success', 'Driver Trip Ticket Section B (Trip Log & Fuel Accounting) updated successfully.');
    }
}
