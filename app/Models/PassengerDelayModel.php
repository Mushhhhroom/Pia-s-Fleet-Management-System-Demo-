<?php

namespace App\Models;

use CodeIgniter\Model;

/** FR-4.2 — 15-Minute Passenger Delay Log (notifies dispatchers past 15 min). */
class PassengerDelayModel extends Model
{
    protected $table            = 'passenger_delay_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'trip_ticket_id', 'delay_minutes', 'reason', 'dispatcher_notified',
        'logged_by', 'created_at',
    ];

    public function getForTicket(int $ticketId)
    {
        return $this->where('trip_ticket_id', $ticketId)->orderBy('id', 'DESC')->findAll();
    }
}
