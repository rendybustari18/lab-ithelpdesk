<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHelpdeskTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'department' => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => ''],
            'role' => ['type' => 'ENUM', 'constraint' => ['Employee', 'IT Support', 'IT Manager', 'Administrator']],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'force_password_change' => ['type' => 'BOOLEAN', 'default' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('categories');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_number' => ['type' => 'VARCHAR', 'constraint' => 24],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT'],
            'category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'priority' => ['type' => 'ENUM', 'constraint' => ['Low', 'Medium', 'High']],
            'status' => ['type' => 'ENUM', 'constraint' => ['Open', 'In Progress', 'Resolved', 'Closed'], 'default' => 'Open'],
            'created_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'assigned_to' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('ticket_number');
        $this->forge->addKey(['created_by', 'status']);
        $this->forge->addKey(['assigned_to', 'status']);
        $this->forge->addKey('category_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('assigned_to', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('tickets');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'support_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'manager_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'assigned_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('support_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('manager_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('ticket_assignments');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'body' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['ticket_id', 'created_at']);
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('comments');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'storage_key' => ['type' => 'VARCHAR', 'constraint' => 80],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 190],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'size_bytes' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('attachments');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'event' => ['type' => 'VARCHAR', 'constraint' => 80],
            'details' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'occurred_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['ticket_id', 'occurred_at']);
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('ticket_histories');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'attempt_key' => ['type' => 'CHAR', 'constraint' => 64],
            'attempted_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['attempt_key', 'attempted_at']);
        $this->forge->createTable('login_attempts');
    }

    public function down(): void
    {
        foreach (['login_attempts', 'ticket_histories', 'attachments', 'comments', 'ticket_assignments', 'tickets', 'categories', 'users'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
