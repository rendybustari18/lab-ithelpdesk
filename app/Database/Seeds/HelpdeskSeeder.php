<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->db;
        if ($db->table('users')->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $users = [
            ['Administrator', 'admin@example.test', 'Administrator'],
            ['IT Manager', 'manager@example.test', 'IT Manager'],
            ['IT Support 01', 'support01@example.test', 'IT Support'],
            ['IT Support 02', 'support02@example.test', 'IT Support'],
            ['Employee 01', 'employee01@example.test', 'Employee'],
            ['Employee 02', 'employee02@example.test', 'Employee'],
            ['Employee 03', 'employee03@example.test', 'Employee'],
        ];
        foreach ($users as $user) {
            $db->table('users')->insert([
                'name' => $user[0], 'email' => $user[1], 'password_hash' => password_hash('TrainingOnly!234', PASSWORD_DEFAULT),
                'department' => 'IT', 'role' => $user[2], 'is_active' => true,
                'force_password_change' => false, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $categoryNames = ['Hardware', 'Software', 'Network', 'Email', 'Account & Access', 'Other'];
        foreach ($categoryNames as $name) {
            $db->table('categories')->insert(['name' => $name, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        $subjects = ['Laptop will not start', 'VPN connection drops', 'Email sync issue', 'Printer setup', 'Access request', 'Application error', 'Wi-Fi unavailable', 'Monitor flickers', 'Password reset help', 'Shared drive access', 'Software installation', 'Other support request'];
        $statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
        $employees = [5, 6, 7];
        for ($i = 0; $i < 12; $i++) {
            $supportId = $i % 2 === 0 ? 3 : 4;
            $status = $statuses[$i % 4];
            $db->table('tickets')->insert([
                'ticket_number' => sprintf('HD-%s-%04d', date('Y'), $i + 1),
                'subject' => $subjects[$i], 'description' => 'Training ticket description for ' . $subjects[$i] . '.',
                'category_id' => ($i % 6) + 1, 'priority' => ['Low', 'Medium', 'High'][$i % 3],
                'status' => $status, 'created_by' => $employees[$i % 3],
                'assigned_to' => $i % 3 === 0 ? null : $supportId, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $ticketId = (int) $db->insertID();
            if ($i % 3 !== 0) {
                $db->table('ticket_assignments')->insert(['ticket_id' => $ticketId, 'support_id' => $supportId, 'manager_id' => 2, 'assigned_at' => $now]);
            }
            $db->table('ticket_histories')->insert(['ticket_id' => $ticketId, 'user_id' => $employees[$i % 3], 'event' => 'created', 'details' => 'Ticket created', 'occurred_at' => $now]);
            if ($i % 3 !== 0) {
                $db->table('ticket_histories')->insert(['ticket_id' => $ticketId, 'user_id' => 2, 'event' => 'assigned', 'details' => 'Assigned to ' . $supportId, 'occurred_at' => $now]);
            }
            foreach ([$employees[$i % 3], $supportId] as $author) {
                $db->table('comments')->insert(['ticket_id' => $ticketId, 'user_id' => $author, 'body' => 'Training discussion update.', 'created_at' => $now, 'updated_at' => $now]);
                $db->table('ticket_histories')->insert(['ticket_id' => $ticketId, 'user_id' => $author, 'event' => 'comment added', 'details' => 'Comment added', 'occurred_at' => $now]);
            }
            if ($i < 12) {
                $folder = WRITEPATH . 'uploads/tickets';
                if (! is_dir($folder)) {
                    mkdir($folder, 0750, true);
                }
                $key = bin2hex(random_bytes(16)) . '.pdf';
                file_put_contents($folder . '/' . $key, "%PDF-1.4\n% Training sample attachment " . ($i + 1) . "\n%%EOF\n", LOCK_EX);
                $db->table('attachments')->insert([
                    'ticket_id' => $ticketId, 'user_id' => $employees[$i % 3], 'storage_key' => $key,
                    'original_name' => 'sample-' . ($i + 1) . '.pdf', 'mime_type' => 'application/pdf',
                    'size_bytes' => filesize($folder . '/' . $key), 'created_at' => $now,
                ]);
                $db->table('ticket_histories')->insert(['ticket_id' => $ticketId, 'user_id' => $employees[$i % 3], 'event' => 'attachment added', 'details' => 'Training sample attachment', 'occurred_at' => $now]);
            }
        }
    }
}
