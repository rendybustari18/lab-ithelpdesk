<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionVersion extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', ['session_version' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0]]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'session_version');
    }
}
