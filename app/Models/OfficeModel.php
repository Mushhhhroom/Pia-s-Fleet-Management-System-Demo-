<?php

namespace App\Models;

use CodeIgniter\Model;

class OfficeModel extends Model
{
    protected $table            = 'offices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'office_code', 'office_name', 'office_type', 'address', 'division_head_name',
        'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
