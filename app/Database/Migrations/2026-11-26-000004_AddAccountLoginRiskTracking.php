<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountLoginRiskTracking extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'attempt_key' => ['type' => 'CHAR', 'constraint' => 64],
            'window_started_at' => ['type' => 'DATETIME'],
            'failure_count' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
        ]);
        $this->forge->addKey('attempt_key', true);
        $this->forge->createTable('account_login_risks');
    }

    public function down(): void
    {
        $this->forge->dropTable('account_login_risks', true);
    }
}
