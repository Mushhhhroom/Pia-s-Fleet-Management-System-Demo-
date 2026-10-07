<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\SlaService;

class SlaWatch extends BaseCommand
{
    protected $group       = 'PIA Fleet';
    protected $name        = 'sla:watch';
    protected $description = 'Scans pending VRS approvals against the 24-hour SLA deadline and auto-expires stalled requests (BR-02)';

    public function run(array $params)
    {
        CLI::write('Checking pending VRS requests for SLA expiration...', 'yellow');

        $slaService = new SlaService();
        $expired = $slaService->checkAndExpireRequests();

        if (empty($expired)) {
            CLI::write('All pending requests are within the 24-hour SLA window. No expirations.', 'green');
        } else {
            CLI::error(sprintf('Auto-expired %d stalled request(s): %s', count($expired), implode(', ', $expired)));
        }
    }
}
