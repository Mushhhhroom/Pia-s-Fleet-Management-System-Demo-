<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * FR-5.1 / FR-5.2 — Mandatory Web BLOWBAGETS Pre-Trip Safety Checklist.
 *
 * BLOWBAGETS = Battery, Lights, Oil, Water, Brake, Air, Gas, Engine, Tire, Self(?)
 * Standard PIA/LTO pre-trip inspection items stored as JSON in `items`.
 */
class SafetyCheckModel extends Model
{
    protected $table            = 'safety_checks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'trip_ticket_id', 'vehicle_id', 'driver_id', 'items', 'failed_items',
        'result', 'remarks', 'checked_by', 'created_at',
    ];

    /**
     * Canonical BLOWBAGETS checklist items.
     *
     * @return array<string,string> code => label
     */
    public static function checklistItems(): array
    {
        return [
            'battery'       => 'B — Battery & Terminals',
            'lights'        => 'L — Lights & Signals',
            'oil'           => 'O — Engine Oil Level',
            'water'         => 'W — Water / Coolant Level',
            'brake'         => 'B — Brake System',
            'air'           => 'A — Air Pressure / Tire Pressure',
            'gas'           => 'G — Gas / Fuel Level',
            'engine'        => 'E — Engine Condition & Belts',
            'tire'          => 'T — Tires, Jack & Tools',
            'steering'      => 'S — Steering, Horn & Seatbelts',
        ];
    }

    public function getForTicket(int $ticketId)
    {
        return $this->where('trip_ticket_id', $ticketId)->orderBy('id', 'DESC')->findAll();
    }

    /**
     * True when the ticket has at least one PASSED checklist (FR-5.1 gate).
     */
    public function hasPassedCheck(int $ticketId): bool
    {
        return $this->where('trip_ticket_id', $ticketId)
                    ->where('result', 'pass')
                    ->countAllResults() > 0;
    }

    /**
     * Latest check result for a ticket, if any.
     */
    public function latestForTicket(int $ticketId): ?array
    {
        return $this->where('trip_ticket_id', $ticketId)
                    ->orderBy('id', 'DESC')
                    ->first();
    }
}
