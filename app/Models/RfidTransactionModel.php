<?php

namespace App\Models;

use CodeIgniter\Model;

/** FR-6.2 — RFID reload / toll / adjustment ledger. */
class RfidTransactionModel extends Model
{
    protected $table            = 'rfid_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'rfid_card_id', 'trip_ticket_id', 'type', 'amount',
        'balance_after', 'remarks', 'recorded_by', 'created_at',
    ];

    public function getRecentWithDetails(int $limit = 100)
    {
        return $this->select('rfid_transactions.*, rfid_cards.card_number, rfid_cards.provider,
                              vehicles.plate_number, users.name AS recorded_by_name')
                    ->join('rfid_cards', 'rfid_cards.id = rfid_transactions.rfid_card_id', 'left')
                    ->join('vehicles', 'vehicles.id = rfid_cards.vehicle_id', 'left')
                    ->join('users', 'users.id = rfid_transactions.recorded_by', 'left')
                    ->orderBy('rfid_transactions.id', 'DESC')
                    ->findAll($limit);
    }
}
