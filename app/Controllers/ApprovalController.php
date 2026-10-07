<?php

namespace App\Controllers;

use App\Models\TripRequestModel;
use App\Models\TripTicketModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Services\SlaService;

class ApprovalController extends BaseController
{
    protected TripRequestModel $requestModel;
    protected TripTicketModel $ticketModel;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->requestModel      = new TripRequestModel();
        $this->ticketModel       = new TripTicketModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $session = session();
        $userRole = $session->get('user_role');
        $userId   = $session->get('user_id');

        // Check pending requests by tier
        $builder = $this->requestModel->select('trip_requests.*,
                                                offices.office_name, offices.office_code,
                                                users.name AS requestor_full_name, users.designation AS requestor_designation,
                                                oic_u.name AS oic_approver_name,
                                                adm_u.name AS admin_approver_name')
                                      ->join('offices', 'offices.id = trip_requests.office_id', 'left')
                                      ->join('users', 'users.id = trip_requests.requestor_id', 'left')
                                      ->join('users AS oic_u', 'oic_u.id = trip_requests.oic_approver_id', 'left')
                                      ->join('users AS adm_u', 'adm_u.id = trip_requests.admin_approver_id', 'left')
                                      ->orderBy('trip_requests.is_rush_request', 'DESC')
                                      ->orderBy('trip_requests.sla_deadline', 'ASC');

        if ($userRole === 'approver_oic') {
            $builder->whereIn('trip_requests.status', ['pending_oic', 'pending_admin', 'approved']);
        } elseif ($userRole === 'approver_admin') {
            $builder->whereIn('trip_requests.status', ['pending_admin', 'approved']);
        } else {
            // Admin sees all
            $builder->whereIn('trip_requests.status', ['pending_oic', 'pending_admin', 'approved', 'rejected', 'expired']);
        }

        $allRequests = $builder->findAll();

        $pendingOic   = [];
        $pendingAdmin = [];
        $recentlyApproved = [];

        foreach ($allRequests as $req) {
            $req['sla_info'] = SlaService::formatSlaCountdown($req['sla_deadline']);
            if ($req['status'] === 'pending_oic') {
                $pendingOic[] = $req;
            } elseif ($req['status'] === 'pending_admin') {
                $pendingAdmin[] = $req;
            } else {
                $recentlyApproved[] = $req;
            }
        }

        $data = [
            'title'            => 'Government Approval Portal & 24h SLA Monitor',
            'userRole'         => $userRole,
            'pendingOic'       => $pendingOic,
            'pendingAdmin'     => $pendingAdmin,
            'recentlyApproved' => $recentlyApproved,
        ];

        return view('approvals/index', $data);
    }

    public function action($id)
    {
        $session = session();
        $userRole = $session->get('user_role');
        $userId   = $session->get('user_id');
        $userName = $session->get('user_name');

        $request = $this->requestModel->find($id);
        if (!$request) {
            return redirect()->to('/approvals')->with('error', 'Request not found.');
        }

        $action = $this->request->getPost('action'); // 'approve' or 'reject'
        $remarks = trim((string)$this->request->getPost('remarks'));
        $now = date('Y-m-d H:i:s');

        if ($action === 'reject' && empty($remarks)) {
            return redirect()->back()->with('error', 'Please provide justification remarks when rejecting an official travel request.');
        }

        // Tier 1: OIC / Staff Director Approval
        if ($userRole === 'approver_oic' || ($userRole === 'admin' && $request['status'] === 'pending_oic')) {
            if ($request['status'] !== 'pending_oic') {
                return redirect()->to('/approvals')->with('error', 'This request is no longer awaiting Tier 1 OIC action.');
            }

            if ($action === 'approve') {
                // Find Admin Division Head (Atty. Julius S. De Peralta)
                $userModel = new UserModel();
                $adminHead = $userModel->where('role', 'approver_admin')->first();

                // Refresh 24h SLA deadline for Tier 2 approval (BR-02)
                $newSlaDeadline = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $this->requestModel->update($id, [
                    'oic_approver_id'  => $userId,
                    'oic_action'       => 'approved',
                    'oic_action_at'    => $now,
                    'oic_remarks'      => $remarks ?: 'Endorsed for administrative clearance.',
                    'admin_approver_id'=> $adminHead ? $adminHead['id'] : null,
                    'status'           => 'pending_admin',
                    'sla_deadline'     => $newSlaDeadline,
                ]);

                // Notify Admin Head
                if ($adminHead) {
                    $this->notificationModel->insert([
                        'user_id'    => $adminHead['id'],
                        'title'      => "Endorsed VRS Ready for Final Approval: {$request['request_number']}",
                        'message'    => "Dir. {$userName} has endorsed VRS {$request['request_number']} ({$request['destination']}). Requires administrative clearance.",
                        'type'       => $request['is_rush_request'] ? 'danger' : 'info',
                        'link'       => '/approvals',
                        'is_read'    => 0,
                        'created_at' => $now,
                    ]);
                }

                return redirect()->to('/approvals')->with('success', "VRS {$request['request_number']} endorsed successfully. Transferred to Administrative Division Head.");
            } else {
                // Reject at Tier 1
                $this->requestModel->update($id, [
                    'oic_approver_id' => $userId,
                    'oic_action'      => 'rejected',
                    'oic_action_at'   => $now,
                    'oic_remarks'     => $remarks,
                    'status'          => 'rejected',
                ]);

                $this->notificationModel->insert([
                    'user_id'    => $request['requestor_id'],
                    'title'      => "Request Rejected: {$request['request_number']}",
                    'message'    => "Your vehicle request was not endorsed by OIC. Reason: {$remarks}",
                    'type'       => 'danger',
                    'link'       => "/requests/{$id}",
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);

                return redirect()->to('/approvals')->with('success', "VRS {$request['request_number']} has been rejected.");
            }
        }

        // Tier 2: Administrative Division Head Approval (Atty. Julius S. De Peralta)
        if ($userRole === 'approver_admin' || ($userRole === 'admin' && $request['status'] === 'pending_admin')) {
            if ($request['status'] !== 'pending_admin') {
                return redirect()->to('/approvals')->with('error', 'This request is not awaiting Administrative Division Head approval.');
            }

            if ($action === 'approve') {
                $this->requestModel->update($id, [
                    'admin_approver_id' => $userId,
                    'admin_action'      => 'approved',
                    'admin_action_at'   => $now,
                    'admin_remarks'     => $remarks ?: 'Approved for official travel and motorpool dispatch.',
                    'status'            => 'approved',
                ]);

                // Notify Requestor
                $this->notificationModel->insert([
                    'user_id'    => $request['requestor_id'],
                    'title'      => "VRS Approved: {$request['request_number']}",
                    'message'    => "Administrative Division Head has officially authorized your trip to {$request['destination']}. Awaiting motorpool dispatch.",
                    'type'       => 'success',
                    'link'       => "/requests/{$id}",
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);

                // Notify Dispatchers
                $userModel = new UserModel();
                $dispatchers = $userModel->where('role', 'dispatcher')->findAll();
                foreach ($dispatchers as $disp) {
                    $this->notificationModel->insert([
                        'user_id'    => $disp['id'],
                        'title'      => "New Approved Trip for Dispatch: {$request['request_number']}",
                        'message'    => "Trip to {$request['destination']} has been authorized by Admin Division Head. Assign vehicle and driver.",
                        'type'       => 'info',
                        'link'       => '/dispatch',
                        'is_read'    => 0,
                        'created_at' => $now,
                    ]);
                }

                return redirect()->to('/approvals')->with('success', "VRS {$request['request_number']} officially authorized! Request is now in the Dispatch Queue.");
            } else {
                // Reject at Tier 2
                $this->requestModel->update($id, [
                    'admin_approver_id' => $userId,
                    'admin_action'      => 'rejected',
                    'admin_action_at'   => $now,
                    'admin_remarks'     => $remarks,
                    'status'            => 'rejected',
                ]);

                $this->notificationModel->insert([
                    'user_id'    => $request['requestor_id'],
                    'title'      => "Request Disapproved: {$request['request_number']}",
                    'message'    => "Administrative Division Head disapproved travel request. Reason: {$remarks}",
                    'type'       => 'danger',
                    'link'       => "/requests/{$id}",
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);

                return redirect()->to('/approvals')->with('success', "VRS {$request['request_number']} disapproved.");
            }
        }

        return redirect()->to('/approvals')->with('error', 'Unauthorized approval action.');
    }
}
