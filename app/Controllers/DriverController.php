<?php

namespace App\Controllers;

use App\Models\DriverModel;
use App\Models\VehicleModel;
use App\Models\TripModel;
use App\Models\IncidentModel;

class DriverController extends BaseController
{
    public function index()
    {
        $driverModel = new DriverModel();
        $drivers = $driverModel->orderBy('id', 'DESC')->findAll();

        return view('drivers/index', [
            'title'   => 'Fleet Drivers & Operators',
            'drivers' => $drivers,
        ]);
    }

    public function mobileApp()
    {
        $driverModel = new DriverModel();
        $driverId = $this->request->getGet('driver_id');
        
        $driver = null;
        if ($driverId) {
            $driver = $driverModel->find($driverId);
        }
        if (!$driver && session()->get('user_id')) {
            $driver = $driverModel->where('user_id', session()->get('user_id'))->first();
        }
        if (!$driver) {
            $driver = $driverModel->first();
        }

        $vehicleModel = new VehicleModel();
        $vehicle = null;
        if ($driver) {
            $vehicle = $vehicleModel->where('current_driver_id', $driver['id'])->first();
        }

        $tripModel = new TripModel();
        $trips = $driver ? $tripModel->where('driver_id', $driver['id'])->orderBy('id', 'DESC')->findAll() : [];

        // If no vehicle directly assigned to driver, check active trip's vehicle
        if (!$vehicle && !empty($trips)) {
            $activeTrip = null;
            foreach ($trips as $t) {
                if (in_array($t['status'], ['in_transit', 'dispatched', 'scheduled'])) {
                    $activeTrip = $t;
                    break;
                }
            }
            if ($activeTrip) {
                $vehicle = $vehicleModel->find($activeTrip['vehicle_id']);
            }
        }

        return view('driver/trips', [
            'driver'  => $driver,
            'vehicle' => $vehicle,
            'trips'   => $trips,
        ]);
    }

    public function create()
    {
        return view('drivers/form', [
            'title'  => 'Register New Driver',
            'driver' => null,
        ]);
    }

    public function store()
    {
        $driverModel = new DriverModel();

        $rules = [
            'first_name'     => 'required|min_length[2]',
            'last_name'      => 'required|min_length[2]',
            'phone'          => 'required|min_length[7]',
            'license_number' => 'required|is_unique[drivers.license_number]',
            'license_type'   => 'required',
            'license_expiry' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = 'DRV-' . rand(100, 999);

        $data = [
            'driver_code'       => $code,
            'first_name'        => trim($this->request->getPost('first_name')),
            'last_name'         => trim($this->request->getPost('last_name')),
            'email'             => trim($this->request->getPost('email')),
            'phone'             => trim($this->request->getPost('phone')),
            'license_number'    => strtoupper(trim($this->request->getPost('license_number'))),
            'license_type'      => trim($this->request->getPost('license_type')),
            'license_expiry'    => $this->request->getPost('license_expiry'),
            'status'            => $this->request->getPost('status') ?: 'available',
            'safety_score'      => (float)($this->request->getPost('safety_score') ?: 100.00),
            'address'           => trim($this->request->getPost('address')),
            'emergency_contact' => trim($this->request->getPost('emergency_contact')),
        ];

        $driverModel->insert($data);

        return redirect()->to('/drivers')->with('success', 'Driver ' . $data['first_name'] . ' ' . $data['last_name'] . ' registered successfully.');
    }

    public function show($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            return redirect()->to('/drivers')->with('error', 'Driver not found.');
        }

        $vehicleModel = new VehicleModel();
        $assignedVehicle = $vehicleModel->where('current_driver_id', $id)->first();

        $tripModel = new TripModel();
        $trips = $tripModel->where('driver_id', $id)->orderBy('id', 'DESC')->findAll();

        $incidentModel = new IncidentModel();
        $incidents = $incidentModel->where('driver_id', $id)->orderBy('id', 'DESC')->findAll();

        return view('drivers/view', [
            'title'           => 'Driver Profile: ' . $driver['first_name'] . ' ' . $driver['last_name'],
            'driver'          => $driver,
            'assignedVehicle' => $assignedVehicle,
            'trips'           => $trips,
            'incidents'       => $incidents,
        ]);
    }

    public function edit($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            return redirect()->to('/drivers')->with('error', 'Driver not found.');
        }

        return view('drivers/form', [
            'title'  => 'Edit Driver: ' . $driver['first_name'] . ' ' . $driver['last_name'],
            'driver' => $driver,
        ]);
    }

    public function update($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            return redirect()->to('/drivers')->with('error', 'Driver not found.');
        }

        $rules = [
            'first_name'     => 'required|min_length[2]',
            'last_name'      => 'required|min_length[2]',
            'phone'          => 'required|min_length[7]',
            'license_number' => "required|is_unique[drivers.license_number,id,{$id}]",
            'license_type'   => 'required',
            'license_expiry' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name'        => trim($this->request->getPost('first_name')),
            'last_name'         => trim($this->request->getPost('last_name')),
            'email'             => trim($this->request->getPost('email')),
            'phone'             => trim($this->request->getPost('phone')),
            'license_number'    => strtoupper(trim($this->request->getPost('license_number'))),
            'license_type'      => trim($this->request->getPost('license_type')),
            'license_expiry'    => $this->request->getPost('license_expiry'),
            'status'            => $this->request->getPost('status'),
            'safety_score'      => (float)$this->request->getPost('safety_score'),
            'address'           => trim($this->request->getPost('address')),
            'emergency_contact' => trim($this->request->getPost('emergency_contact')),
        ];

        $driverModel->update($id, $data);

        return redirect()->to('/drivers/' . $id)->with('success', 'Driver profile updated.');
    }

    public function delete($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if ($driver) {
            $driverModel->delete($id);
            return redirect()->to('/drivers')->with('success', 'Driver removed successfully.');
        }

        return redirect()->to('/drivers')->with('error', 'Driver not found.');
    }

    public function reportIncident()
    {
        $incidentModel = new IncidentModel();
        $vehicleModel = new VehicleModel();

        $vehicleId = (int)$this->request->getPost('vehicle_id');
        $driverId = (int)$this->request->getPost('driver_id');
        $type = $this->request->getPost('type') ?: 'Breakdown / Mechanical';
        $severity = $this->request->getPost('severity') ?: 'minor';
        $location = trim($this->request->getPost('location') ?: 'Highway Route');
        $description = trim($this->request->getPost('description'));

        $incNo = 'INC-' . date('Ymd-His') . '-' . rand(10, 99);

        $incidentModel->insert([
            'incident_no'     => $incNo,
            'vehicle_id'      => $vehicleId,
            'driver_id'       => $driverId ?: null,
            'trip_id'         => (int)$this->request->getPost('trip_id') ?: null,
            'incident_date'   => date('Y-m-d H:i:s'),
            'severity'        => $severity,
            'type'            => $type,
            'location'        => $location,
            'description'     => $description ?: "Driver reported {$type} at {$location}.",
            'damage_estimate' => 0.00,
            'status'          => 'reported',
        ]);

        if (in_array($severity, ['severe', 'critical']) && $vehicleId > 0) {
            $vehicleModel->update($vehicleId, ['status' => 'out_of_service']);
        }

        // Notify dispatch via SMS (SDD §5.9)
        $smsSender = new \App\Libraries\SmsSender();
        $smsSender->send(
            '+63 918 555 0200',
            "FleetPulse Dispatch Alert: Incident {$incNo} reported by driver ({$type} - {$severity}). Location: {$location}."
        );

        $referer = $this->request->getServer('HTTP_REFERER') ?? '';
        if (str_contains($referer, 'driver/trips') || !$this->request->isAJAX()) {
            return redirect()->to('/driver/trips')->with('success', "Incident {$incNo} reported to Dispatch.");
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'incident_no' => $incNo,
            'message'     => 'Incident reported to Dispatch.',
        ]);
    }
}
