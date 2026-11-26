<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAtomicLoginRateLimits extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'attempt_key' => ['type' => 'CHAR', 'constraint' => 64],
            'window_started_at' => ['type' => 'DATETIME'],
            'attempt_count' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
        ]);
        $this->forge->addKey('attempt_key', true);
        $this->forge->createTable('login_rate_limits');
    }

    public function down(): void
    {
        $this->forge->dropTable('login_rate_limits', true);
    }
}
