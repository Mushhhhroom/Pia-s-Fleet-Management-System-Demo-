<?php

namespace App\Models;

use CodeIgniter\Model;

/** FR-7.4 — Monthly Certificates of Non-Usage (COA / HRDD compliance). */
class NonUsageCertificateModel extends Model
{
    protected $table            = 'non_usage_certificates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'certificate_no', 'vehicle_id', 'period_month', 'trip_count',
        'odometer_start', 'odometer_end', 'generated_by', 'created_at',
    ];

    public function generateCertificateNo(): string
    {
        $count = $this->countAllResults();
        return sprintf('CNU-%s-%04d', date('Ym'), $count + 1);
    }

    public function getWithVehicle(?string $periodMonth = null)
    {
        $builder = $this->select('non_usage_certificates.*, vehicles.plate_number,
                                  vehicles.vehicle_code, vehicles.make, vehicles.model,
                                  vehicles.fleet_category, gen.name AS generated_by_name')
                        ->join('vehicles', 'vehicles.id = non_usage_certificates.vehicle_id', 'left')
                        ->join('users AS gen', 'gen.id = non_usage_certificates.generated_by', 'left')
                        ->orderBy('non_usage_certificates.id', 'DESC');

        if ($periodMonth) {
            $builder->where('non_usage_certificates.period_month', $periodMonth);
        }

        return $builder->findAll();
    }
}
