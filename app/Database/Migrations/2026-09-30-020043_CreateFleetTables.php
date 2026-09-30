<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFleetTables extends Migration
{
    public function up()
    {
        // 1. Users Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'dispatcher', 'maintenance', 'driver'],
                'default'    => 'dispatcher',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        // 2. Drivers Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'driver_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'license_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'license_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Class A Commercial',
            ],
            'license_expiry' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'on_trip', 'off_duty', 'suspended'],
                'default'    => 'available',
            ],
            'safety_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 100.00,
            ],
            'total_trips' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'emergency_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('drivers', true);

        // 3. Vehicles Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vehicle_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'plate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'unique'     => true,
            ],
            'vin' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'make' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'year' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['semi_truck', 'box_truck', 'delivery_van', 'pickup', 'trailer', 'suv'],
                'default'    => 'box_truck',
            ],
            'fuel_type' => [
                'type'       => 'ENUM',
                'constraint' => ['diesel', 'gasoline', 'electric', 'hybrid'],
                'default'    => 'diesel',
            ],
            'max_payload_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'fuel_capacity_liters' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'default'    => 100.00,
            ],
            'odometer_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'current_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 14.5995120, // Default near metro
            ],
            'current_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 120.9842220,
            ],
            'current_speed' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 0.00,
            ],
            'current_fuel_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 100.00,
            ],
            'engine_status' => [
                'type'       => 'ENUM',
                'constraint' => ['off', 'idling', 'running'],
                'default'    => 'off',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'in_transit', 'maintenance', 'out_of_service'],
                'default'    => 'active',
            ],
            'current_driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_service_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'next_service_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('vehicles', true);

        // 4. Trips Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'trip_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'origin_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'origin_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 0.0000000,
            ],
            'origin_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 0.0000000,
            ],
            'destination_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'destination_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 0.0000000,
            ],
            'destination_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'default'    => 0.0000000,
            ],
            'cargo_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => 'General Merchandise',
            ],
            'cargo_weight_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'distance_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'scheduled_departure' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'scheduled_arrival' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'actual_departure' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'actual_arrival' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'start_odometer' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'end_odometer' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'dispatched', 'in_transit', 'completed', 'cancelled'],
                'default'    => 'scheduled',
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'urgent', 'critical'],
                'default'    => 'normal',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('trips', true);

        // 5. Telemetry Logs Table
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'trip_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
            ],
            'speed_kmh' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 0.00,
            ],
            'heading' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'fuel_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 100.00,
            ],
            'engine_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'running',
            ],
            'battery_voltage' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,2',
                'null'       => true,
            ],
            'engine_temp_c' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('vehicle_id');
        $this->forge->createTable('telemetry_logs', true);

        // 6. Maintenance Records Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'service_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default'    => 'medium',
            ],
            'scheduled_date' => [
                'type' => 'DATE',
            ],
            'completion_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'odometer_at_service' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'service_center' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'technician_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'scheduled',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('maintenance_records', true);

        // 7. Fuel Logs Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'receipt_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'fuel_date' => [
                'type' => 'DATE',
            ],
            'odometer_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'liters' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
            ],
            'cost_per_liter' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
            ],
            'total_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'fuel_station' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fuel_logs', true);

        // 8. Incidents Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'incident_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'trip_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'incident_date' => [
                'type' => 'DATETIME',
            ],
            'severity' => [
                'type'       => 'ENUM',
                'constraint' => ['minor', 'moderate', 'severe', 'critical'],
                'default'    => 'minor',
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'damage_estimate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['reported', 'investigating', 'resolved', 'closed'],
                'default'    => 'reported',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('incidents', true);
    }

    public function down()
    {
        $this->forge->dropTable('incidents', true);
        $this->forge->dropTable('fuel_logs', true);
        $this->forge->dropTable('maintenance_records', true);
        $this->forge->dropTable('telemetry_logs', true);
        $this->forge->dropTable('trips', true);
        $this->forge->dropTable('vehicles', true);
        $this->forge->dropTable('drivers', true);
        $this->forge->dropTable('users', true);
    }
}
