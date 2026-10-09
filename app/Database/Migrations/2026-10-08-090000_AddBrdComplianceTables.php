<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * BRD Compliance Migration (PIA-AFMS BRD dated October 8, 2026)
 *
 * Adds the structures required by the following BRD clauses that were
 * missing from the previous schema:
 *
 *  - FR-1.3 Emergency Fast-Track Override + post-trip documentation tracking
 *  - FR-1.4 4-Hour Approval Escalation (escalated_at marker)
 *  - FR-2.1  Fleet Segregation (Dedicated Executive vs. Shared Pool vehicles,
 *            LTO registration & GSIS insurance expiry for FR-7.1)
 *  - FR-4.1  Toll expense capture on Trip Tickets
 *  - FR-4.2  15-Minute Passenger Delay Log
 *  - FR-5.1/5.2/5.3  BLOWBAGETS Web Checklist + automated PIR tickets
 *  - FR-6.1/6.2/6.3  AutoSweep/EasyTrip RFID cards, transactions, alerts
 *  - FR-7.4  Certificates of Non-Usage
 *  - NFR-2   TOTP MFA fields (Data Privacy / access control hardening)
 *  - NFR-3   system_audit_logs (immutable event log with IP addresses)
 */
class AddBrdComplianceTables extends Migration
{
    public function up()
    {
        // ------------------------------------------------------------------
        // NFR-2: TOTP MFA columns on users (optional per-account MFA)
        // ------------------------------------------------------------------
        $this->db->query("ALTER TABLE `users` ADD COLUMN `totp_secret` VARCHAR(64) NULL AFTER `password`");
        $this->db->query("ALTER TABLE `users` ADD COLUMN `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `totp_secret`");

        // ------------------------------------------------------------------
        // FR-2.1 + FR-7.1: Fleet segregation & regulatory expiry tracking
        // FR-4.1: toll expense on trip tickets
        // ------------------------------------------------------------------
        $this->db->query("ALTER TABLE `vehicles`
            ADD COLUMN `fleet_category` ENUM('dedicated','pool') NOT NULL DEFAULT 'pool' AFTER `status`,
            ADD COLUMN `assigned_official` VARCHAR(100) NULL AFTER `fleet_category`,
            ADD COLUMN `lto_registration_expiry` DATE NULL AFTER `assigned_official`,
            ADD COLUMN `gsis_insurance_expiry` DATE NULL AFTER `lto_registration_expiry`,
            MODIFY COLUMN `status` ENUM('active','in_transit','maintenance','under_maintenance','disabled_breakdown','out_of_service') DEFAULT 'active'");

        $this->db->query("ALTER TABLE `trip_tickets`
            ADD COLUMN `toll_expense` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `fuel_anomaly_flag`,
            ADD COLUMN `toll_provider` ENUM('autosweep','easytrip','cash') NULL AFTER `toll_expense`");

        // ------------------------------------------------------------------
        // FR-1.3 + FR-1.4: Emergency override & escalation markers on VRS
        // ------------------------------------------------------------------
        $this->db->query("ALTER TABLE `trip_requests`
            ADD COLUMN `is_emergency_override` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_sla_breached`,
            ADD COLUMN `emergency_override_by` INT UNSIGNED NULL AFTER `is_emergency_override`,
            ADD COLUMN `emergency_override_at` DATETIME NULL AFTER `emergency_override_by`,
            ADD COLUMN `emergency_override_remarks` TEXT NULL AFTER `emergency_override_at`,
            ADD COLUMN `escalated_at` DATETIME NULL AFTER `emergency_override_remarks`,
            ADD COLUMN `post_trip_doc_due` DATETIME NULL AFTER `escalated_at`,
            ADD COLUMN `post_trip_doc_status` ENUM('pending','submitted','overdue') NULL AFTER `post_trip_doc_due`");

        // ------------------------------------------------------------------
        // NFR-3: system_audit_logs — immutable critical event log
        // (approvals, emergency overrides, status changes, login failures)
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'user_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'entity_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // Immutable timestamp — no updated_at by design (BRD NFR-3)
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['event_type', 'created_at']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->createTable('system_audit_logs', true);

        // ------------------------------------------------------------------
        // FR-5.1 / FR-5.2: Mandatory Web BLOWBAGETS Pre-Trip Safety Checklist
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'trip_ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
            'items' => [
                'type' => 'TEXT',
            ],
            'failed_items' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'result' => [
                'type'       => 'ENUM',
                'constraint' => ['pass', 'fail'],
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'checked_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['trip_ticket_id', 'result']);
        $this->forge->addKey('vehicle_id');
        $this->forge->createTable('safety_checks', true);

        // ------------------------------------------------------------------
        // FR-4.3 / FR-5.2 / FR-5.3: Pre-Repair Inspection Reports (PIR)
        // Automated mechanic queue tickets (BLOWBAGETS failure & breakdowns)
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pir_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'trip_ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'incident_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['blowbagets', 'breakdown', 'incident', 'manual'],
                'default'    => 'manual',
            ],
            'defect_description' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'in_inspection', 'in_repair', 'ready_for_release', 'released'],
                'default'    => 'pending',
            ],
            'pre_inspection_findings' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'post_repair_evaluation' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'parts_replaced' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'repair_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'mechanic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reported_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'released_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey(['vehicle_id', 'status']);
        $this->forge->createTable('pir_reports', true);

        // ------------------------------------------------------------------
        // FR-6.1: RFID Balance Tracking (AutoSweep & EasyTrip per vehicle)
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'provider' => [
                'type'       => 'ENUM',
                'constraint' => ['autosweep', 'easytrip'],
            ],
            'card_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'low_balance_threshold' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 500.00,
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
        $this->forge->addUniqueKey(['vehicle_id', 'provider']);
        $this->forge->createTable('rfid_cards', true);

        // ------------------------------------------------------------------
        // FR-6.2: Toll Expense & Reload Entry ledger
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rfid_card_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'trip_ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['reload', 'toll', 'adjustment'],
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'balance_after' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recorded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('rfid_card_id');
        $this->forge->createTable('rfid_transactions', true);

        // ------------------------------------------------------------------
        // FR-4.2: 15-Minute Passenger Delay Log
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'trip_ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'delay_minutes' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dispatcher_notified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'logged_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('trip_ticket_id');
        $this->forge->createTable('passenger_delay_logs', true);

        // ------------------------------------------------------------------
        // FR-7.4: Certificates of Non-Usage (COA / HRDD compliance)
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'certificate_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'period_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
            ],
            'trip_count' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
            ],
            'odometer_start' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'odometer_end' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'generated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['vehicle_id', 'period_month']);
        $this->forge->createTable('non_usage_certificates', true);

        // ------------------------------------------------------------------
        // Mail graceful-fallback outbox (email attempted or logged when
        // SMTP is unconfigured — always mirrored as in-app notification)
        // ------------------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'to_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'to_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'body' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['sent', 'logged', 'failed'],
                'default'    => 'logged',
            ],
            'error' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('email_outbox', true);
    }

    public function down()
    {
        $this->forge->dropTable('email_outbox', true);
        $this->forge->dropTable('non_usage_certificates', true);
        $this->forge->dropTable('passenger_delay_logs', true);
        $this->forge->dropTable('rfid_transactions', true);
        $this->forge->dropTable('rfid_cards', true);
        $this->forge->dropTable('pir_reports', true);
        $this->forge->dropTable('safety_checks', true);
        $this->forge->dropTable('system_audit_logs', true);

        $this->db->query("ALTER TABLE `trip_requests`
            DROP COLUMN `is_emergency_override`, DROP COLUMN `emergency_override_by`,
            DROP COLUMN `emergency_override_at`, DROP COLUMN `emergency_override_remarks`,
            DROP COLUMN `escalated_at`, DROP COLUMN `post_trip_doc_due`, DROP COLUMN `post_trip_doc_status`");

        $this->db->query("ALTER TABLE `trip_tickets` DROP COLUMN `toll_expense`, DROP COLUMN `toll_provider`");

        $this->db->query("ALTER TABLE `vehicles`
            DROP COLUMN `fleet_category`, DROP COLUMN `assigned_official`,
            DROP COLUMN `lto_registration_expiry`, DROP COLUMN `gsis_insurance_expiry`,
            MODIFY COLUMN `status` ENUM('active','in_transit','maintenance','out_of_service') DEFAULT 'active'");

        $this->db->query("ALTER TABLE `users` DROP COLUMN `totp_secret`, DROP COLUMN `totp_enabled`");
    }
}
