<?php

namespace App\Services;

use App\Models\TripRequestModel;
use App\Models\NotificationModel;
use Config\Database;

class SlaService
{
    protected TripRequestModel $requestModel;
    protected NotificationModel $notificationModel;
    protected $db;

    public function __construct()
    {
        $this->requestModel      = new TripRequestModel();
        $this->notificationModel = new NotificationModel();
        $this->db                = Database::connect();
    }

    /**
     * Check pending requests and mark expired ones (BR-02 SLA enforcement)
     */
    public function checkAndExpireRequests(): array
    {
        $now = date('Y-m-d H:i:s');
        $expiredList = $this->db->table('trip_requests')
            ->whereIn('status', ['pending_oic', 'pending_admin'])
            ->where('sla_deadline <', $now)
            ->where('sla_deadline IS NOT NULL')
            ->get()->getResultArray();

        $processed = [];
        foreach ($expiredList as $req) {
            $this->requestModel->update($req['id'], [
                'status'          => 'expired',
                'is_sla_breached' => 1,
            ]);

            // Notify requestor
            $this->notificationModel->insert([
                'user_id'    => $req['requestor_id'],
                'title'      => "Request Expired: {$req['request_number']}",
                'message'    => "Your vehicle request to {$req['destination']} has expired because approval was not completed within the mandatory 24-hour SLA window.",
                'type'       => 'danger',
                'link'       => "/requests/{$req['id']}",
                'is_read'    => 0,
                'created_at' => $now,
            ]);

            $processed[] = $req['request_number'];
        }

        return $processed;
    }

    /**
     * Format remaining SLA countdown string
     */
    public static function formatSlaCountdown(?string $slaDeadline): array
    {
        if (!$slaDeadline) {
            return ['text' => 'No SLA', 'class' => 'badge-secondary', 'is_urgent' => false, 'seconds' => 0];
        }

        $remainingSecs = strtotime($slaDeadline) - time();

        if ($remainingSecs <= 0) {
            $hoursAgo = abs(round($remainingSecs / 3600, 1));
            return [
                'text'      => "Expired {$hoursAgo}h ago",
                'class'     => 'badge-danger text-white bg-danger',
                'is_urgent' => true,
                'is_breached' => true,
                'seconds'   => $remainingSecs,
            ];
        }

        $hours = floor($remainingSecs / 3600);
        $minutes = floor(($remainingSecs % 3600) / 60);

        if ($hours < 4) {
            $badgeClass = 'badge-danger bg-danger text-white animate-pulse';
            $isUrgent = true;
        } elseif ($hours < 12) {
            $badgeClass = 'badge-warning bg-warning text-dark';
            $isUrgent = true;
        } else {
            $badgeClass = 'badge-info bg-primary text-white';
            $isUrgent = false;
        }

        return [
            'text'        => "{$hours}h {$minutes}m left",
            'class'       => $badgeClass,
            'is_urgent'   => $isUrgent,
            'is_breached' => false,
            'seconds'     => $remainingSecs,
        ];
    }
}
