<?php

namespace App\Services;

use App\Models\VehicleModel;
use App\Models\DriverModel;
use App\Models\PmsRecordModel;
use App\Models\TripTicketModel;
use Config\Database;

class DispatchEngineService
{
    protected $db;
    protected VehicleModel $vehicleModel;
    protected DriverModel $driverModel;
    protected PmsRecordModel $pmsModel;
    protected TripTicketModel $ticketModel;

    public function __construct()
    {
        $this->db          = Database::connect();
        $this->vehicleModel= new VehicleModel();
        $this->driverModel = new DriverModel();
        $this->pmsModel    = new PmsRecordModel();
        $this->ticketModel = new TripTicketModel();
    }

    /**
     * Calculate recommendations and rank vehicle-driver pairings for a trip request
     */
    public function getRecommendations(array $request): array
    {
        $depTime = $request['departure_time'];
        $retTime = $request['return_time'];
        $passCount = (int)($request['passenger_count'] ?? 1);
        $reqType = strtolower($request['requested_vehicle_type'] ?? '');
        $preferredDriverId = (int)($request['requested_driver_id'] ?? 0);

        // Fetch active vehicles with PMS status
        $vehicles = $this->db->table('vehicles')
            ->select('vehicles.*, pms_records.is_locked, pms_records.next_pms_odometer, pms_records.status AS pms_status')
            ->join('pms_records', 'pms_records.vehicle_id = vehicles.id', 'left')
            ->whereNotIn('vehicles.status', ['maintenance', 'out_of_service'])
            ->get()->getResultArray();

        // Fetch drivers
        $drivers = $this->db->table('drivers')
            ->where('status !=', 'suspended')
            ->get()->getResultArray();

        // Check booked vehicle and driver IDs during this time window
        $conflicts = $this->getBookingConflicts($depTime, $retTime);

        $vehicleCandidates = [];
        foreach ($vehicles as $v) {
            $vid = (int)$v['id'];
            $isBooked = in_array($vid, $conflicts['vehicle_ids'], true);
            $isPmsLocked = (int)($v['is_locked'] ?? 0) === 1;

            if ($isBooked || $isPmsLocked) {
                continue; // Cannot recommend unavailable or PMS-locked vehicles
            }

            // Calculate Vehicle Sub-scores
            // PMS Health score (0.0 to 1.0)
            $currOdo = (float)$v['odometer_km'];
            $nextPms = (float)($v['next_pms_odometer'] ?? ($currOdo + 5000));
            $kmToPms = max(0, $nextPms - $currOdo);
            $wPms = min(1.0, $kmToPms / 5000.0);

            // Capacity / Type Match (0.0 to 1.0)
            $wCap = 0.8;
            if ($reqType && str_contains(strtolower($v['type']), $reqType)) {
                $wCap = 1.0;
            } elseif ($passCount > 4 && in_array($v['type'], ['delivery_van', 'box_truck', 'suv'])) {
                $wCap = 1.0;
            }

            $vehicleScore = ($wPms * 50) + ($wCap * 50);

            $v['calculated_score'] = round($vehicleScore, 1);
            $v['km_to_pms'] = round($kmToPms, 0);
            $vehicleCandidates[] = $v;
        }

        // Sort vehicles by score descending
        usort($vehicleCandidates, fn($a, $b) => $b['calculated_score'] <=> $a['calculated_score']);

        $driverCandidates = [];
        foreach ($drivers as $d) {
            $did = (int)$d['id'];
            $isBooked = in_array($did, $conflicts['driver_ids'], true);

            if ($isBooked) {
                continue;
            }

            // Driver Priority Match (0.0 to 1.0)
            $wPrio = 0.7;
            if ($preferredDriverId > 0 && $preferredDriverId === $did) {
                $wPrio = 1.0;
            }

            // Rest & Safety Score (0.0 to 1.0)
            $safetyScore = (float)($d['safety_score'] ?? 95.0);
            $wRest = min(1.0, $safetyScore / 100.0);

            $driverScore = ($wPrio * 50) + ($wRest * 50);

            $d['calculated_score'] = round($driverScore, 1);
            $driverCandidates[] = $d;
        }

        // Sort drivers by score descending
        usort($driverCandidates, fn($a, $b) => $b['calculated_score'] <=> $a['calculated_score']);

        return [
            'recommended_vehicles' => $vehicleCandidates,
            'recommended_drivers'  => $driverCandidates,
            'has_available_match'  => !empty($vehicleCandidates) && !empty($driverCandidates),
        ];
    }

    /**
     * Find active trip tickets that overlap with the proposed schedule
     */
    private function getBookingConflicts(string $depTime, string $retTime): array
    {
        $overlapping = $this->db->table('trip_tickets')
            ->select('vehicle_id, driver_id')
            ->whereIn('status', ['issued', 'departed', 'arrived_dest', 'departed_dest', 'returned'])
            ->groupStart()
                ->where("authorized_departure <= '{$retTime}' AND authorized_return >= '{$depTime}'")
            ->groupEnd()
            ->get()->getResultArray();

        $vehicleIds = array_column($overlapping, 'vehicle_id');
        $driverIds  = array_column($overlapping, 'driver_id');

        return [
            'vehicle_ids' => array_unique(array_map('intval', $vehicleIds)),
            'driver_ids'  => array_unique(array_map('intval', $driverIds)),
        ];
    }
}
