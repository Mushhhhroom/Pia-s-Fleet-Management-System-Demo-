<?php

namespace App\Models;

use CodeIgniter\Model;

class PmsRecordModel extends Model
{
    protected $table            = 'pms_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'vehicle_id', 'last_pms_odometer', 'next_pms_odometer', 'pms_interval_km',
        'is_locked', 'status', 'last_pms_date', 'notes', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPmsWithVehicles()
    {
        return $this->select('pms_records.*,
                             vehicles.plate_number, vehicles.vehicle_code, vehicles.make, vehicles.model,
                             vehicles.odometer_km, vehicles.type AS vehicle_type, vehicles.status AS vehicle_status')
                    ->join('vehicles', 'vehicles.id = pms_records.vehicle_id', 'left')
                    ->orderBy('pms_records.is_locked', 'DESC')
                    ->orderBy('pms_records.status', 'ASC')
                    ->findAll();
    }
}
