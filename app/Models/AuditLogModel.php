<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'system_audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    // Immutable log — no updated_at field by design (BRD NFR-3)
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'user_id', 'user_name', 'user_role', 'event_type', 'entity_type',
        'entity_id', 'description', 'context', 'ip_address', 'user_agent', 'created_at',
    ];

    public function recent(int $limit = 100, ?string $eventType = null)
    {
        $builder = $this->orderBy('id', 'DESC');
        if ($eventType) {
            $builder->where('event_type', $eventType);
        }
        return $builder->findAll($limit);
    }
}
