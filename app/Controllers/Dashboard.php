<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        if ($this->user['role'] === 'Administrator') {
            $users = $this->db->table('users')->select('role, is_active, COUNT(*) AS total')->groupBy(['role', 'is_active'])->get()->getResultArray();
            $categories = $this->db->table('categories')->select('is_active, COUNT(*) AS total')->groupBy('is_active')->get()->getResultArray();
            return $this->page('Dashboard', view('dashboard/admin', ['counts' => $users, 'categoryCounts' => $categories]));
        }
        $builder = $this->db->table('tickets')->where('deleted_at', null);
        if ($this->user['role'] === 'Employee') {
            $builder->where('created_by', $this->user['id']);
        } elseif ($this->user['role'] === 'IT Support') {
            $builder->where('assigned_to', $this->user['id']);
        }
        $rows = $builder->select('status, COUNT(*) AS total')->groupBy('status')->get()->getResultArray();
        $recent = $this->db->table('tickets t')->select('t.id, t.ticket_number, t.subject, t.status, t.priority, t.created_at')
            ->where('t.deleted_at', null)->orderBy('t.created_at', 'DESC');
        if ($this->user['role'] === 'Employee') $recent->where('t.created_by', $this->user['id']);
        if ($this->user['role'] === 'IT Support') $recent->where('t.assigned_to', $this->user['id']);
        $tickets = $recent->limit(8)->get()->getResultArray();
        $workload = $this->user['role'] === 'IT Manager' ? $this->db->table('users u')->select('u.name, COUNT(t.id) AS total')
            ->join('tickets t', "t.assigned_to = u.id AND t.deleted_at IS NULL AND t.status IN ('Open', 'In Progress')", 'left')->where('u.role', 'IT Support')->where('u.is_active', 1)->groupBy('u.id')->get()->getResultArray() : [];
        return $this->page('Dashboard', view('dashboard/index', ['user' => $this->user, 'counts' => $rows, 'tickets' => $tickets, 'workload' => $workload]));
    }
}
