<?php

namespace App\Controllers;

use App\Models\TripTicketModel;
use App\Models\TripRequestModel;
use App\Models\PmsRecordModel;
use App\Models\GateLogModel;
use Config\Database;

class AuditController extends BaseController
{
    protected TripTicketModel $ticketModel;
    protected TripRequestModel $requestModel;
    protected PmsRecordModel $pmsModel;
    protected GateLogModel $gateLogModel;
    protected $db;

    public function __construct()
    {
        $this->ticketModel  = new TripTicketModel();
        $this->requestModel = new TripRequestModel();
        $this->pmsModel     = new PmsRecordModel();
        $this->gateLogModel = new GateLogModel();
        $this->db           = Database::connect();
    }

    public function index()
    {
        // 1. SLA Performance Analytics
        $totalRequests = $this->requestModel->countAllResults();
        $slaBreached = $this->requestModel->where('is_sla_breached', 1)->countAllResults();
        $slaComplianceRate = $totalRequests > 0 ? round((($totalRequests - $slaBreached) / $totalRequests) * 100, 1) : 100.0;

        // 2. Rush vs Standard Request Ratio (BR-01 vs BR-03)
        $rushRequests = $this->requestModel->where('is_rush_request', 1)->countAllResults();
        $standardRequests = max(0, $totalRequests - $rushRequests);

        // 3. Fuel Anomaly Flags (>20% deviation)
        $anomalies = $this->ticketModel->getTicketsWithDetails(['status' => 'completed']);
        $flaggedAnomalies = array_filter($anomalies, fn($t) => (int)($t['fuel_anomaly_flag'] ?? 0) === 1);

        // 4. PMS Status Summary
        $pmsRecords = $this->pmsModel->getPmsWithVehicles();
        $lockedCount = 0;
        $overdueCount = 0;
        $dueSoonCount = 0;
        foreach ($pmsRecords as $p) {
            if ((int)$p['is_locked'] === 1) $lockedCount++;
            if ($p['status'] === 'overdue') $overdueCount++;
            if ($p['status'] === 'due_soon') $dueSoonCount++;
        }

        // 5. Historical Audit Trail of Completed Trips
        $completedTrips = $this->ticketModel->getTicketsWithDetails([], 50);

        $data = [
            'title'             => 'Commission on Audit (COA) Compliance & Fleet Governance',
            'totalRequests'     => $totalRequests,
            'slaComplianceRate' => $slaComplianceRate,
            'slaBreached'       => $slaBreached,
            'rushRequests'      => $rushRequests,
            'standardRequests'  => $standardRequests,
            'flaggedAnomalies'  => $flaggedAnomalies,
            'pmsRecords'        => $pmsRecords,
            'lockedCount'       => $lockedCount,
            'overdueCount'      => $overdueCount,
            'dueSoonCount'      => $dueSoonCount,
            'completedTrips'    => $completedTrips,
        ];

        return view('audit/index', $data);
    }

    public function exportCsv()
    {
        $trips = $this->ticketModel->getTicketsWithDetails();

        $filename = 'COA_Trip_Audit_Report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // CSV Header following COA Fleet Audit specifications
        fputcsv($out, [
            'Ticket Serial No', 'VRS Request No', 'Office / Division', 'Plate Number',
            'Driver Name', 'Destination', 'Purpose', 'Departure Date/Time',
            'Return Date/Time', 'Start Odometer (KM)', 'Return Odometer (KM)', 'Total Distance (KM)',
            'Fuel Start (L)', 'Fuel Purchased (L)', 'Fuel Purchased Cost (PHP)', 'Fuel Used (L)',
            'Fuel End (L)', 'Fuel Efficiency (KM/L)', 'Fuel Anomaly Flag', 'Driver Certified', 'Passenger Certified'
        ]);

        foreach ($trips as $t) {
            fputcsv($out, [
                $t['ticket_serial_no'] ?? '',
                $t['request_number'] ?? '',
                $t['office_name'] ?? '',
                $t['plate_number'] ?? '',
                $t['driver_name'] ?? '',
                $t['destination'] ?? '',
                $t['purpose'] ?? '',
                $t['departure_time'] ?? $t['authorized_departure'] ?? '',
                $t['arrival_back_time'] ?? $t['authorized_return'] ?? '',
                $t['start_odometer'] ?? '0.00',
                $t['return_odometer'] ?? '0.00',
                $t['total_distance_km'] ?? '0.00',
                $t['fuel_balance_start_liters'] ?? '0.00',
                $t['fuel_purchased_liters'] ?? '0.00',
                $t['fuel_purchased_cost'] ?? '0.00',
                $t['fuel_used_liters'] ?? '0.00',
                $t['fuel_balance_end_liters'] ?? '0.00',
                $t['fuel_efficiency_kml'] ?? '0.00',
                ((int)($t['fuel_anomaly_flag'] ?? 0) === 1) ? 'YES (>20% DEVIATION)' : 'NO',
                ((int)($t['driver_certified'] ?? 0) === 1) ? 'YES' : 'NO',
                ((int)($t['passenger_certified'] ?? 0) === 1) ? 'YES' : 'NO',
            ]);
        }

        fclose($out);
        exit;
    }
}
