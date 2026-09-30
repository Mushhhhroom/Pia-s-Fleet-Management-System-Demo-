<?php

namespace App\Models;

use CodeIgniter\Model;

class TripModel extends Model
{
    protected $table            = 'trips';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'trip_number', 'vehicle_id', 'driver_id', 'origin_address', 'origin_lat',
        'origin_lng', 'destination_address', 'destination_lat', 'destination_lng',
        'cargo_type', 'cargo_weight_kg', 'distance_km', 'scheduled_departure',
        'scheduled_arrival', 'actual_departure', 'actual_arrival', 'start_odometer',
        'end_odometer', 'status', 'priority', 'notes', 'created_by', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getTripsWithDetails($limit = null, $status = null)
    {
        $builder = $this->select('trips.*, 
                                 vehicles.vehicle_code, vehicles.plate_number, vehicles.make, vehicles.model,
                                 CONCAT(drivers.first_name, " ", drivers.last_name) AS driver_name, drivers.driver_code, drivers.phone AS driver_phone')
                        ->join('vehicles', 'vehicles.id = trips.vehicle_id', 'left')
                        ->join('drivers', 'drivers.id = trips.driver_id', 'left')
                        ->orderBy('trips.id', 'DESC');

        if ($status) {
            $builder->where('trips.status', $status);
        }

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }
}
