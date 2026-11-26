<?php

namespace App\Controllers;

class Reports extends BaseController
{
    public function index(): string|\CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->user['role'] === 'Administrator') return $this->response->setStatusCode(403)->setBody('Forbidden');
        $page = max(1, min(10000, (int) $this->getString('page')));
        $query = $this->query();
        $total = (clone $query)->countAllResults();
        return $this->page('Ticket report', view('reports/list', [
            'user' => $this->user,
            'tickets' => $query->orderBy('t.created_at', 'DESC')->limit(20, ($page - 1) * 20)->get()->getResultArray(),
            'total' => $total, 'pageNumber' => $page,
            'categories' => $this->db->table('categories')->orderBy('name')->get()->getResultArray(),
            'filters' => array_combine(['q', 'status', 'priority', 'category', 'from', 'to'], array_map(fn (string $key): string => $this->getString($key), ['q', 'status', 'priority', 'category', 'from', 'to'])),
            'queryString' => http_build_query(array_filter(array_map(fn (string $key): string => $this->getString($key), ['q', 'status', 'priority', 'category', 'from', 'to']))),
        ]));
    }

    public function export(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->user['role'] === 'Administrator') return $this->response->setStatusCode(403)->setBody('Forbidden');
        $tickets = $this->query()->orderBy('t.created_at', 'DESC')->get()->getResultArray();
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['Ticket number', 'Subject', 'Category', 'Priority', 'Status', 'Created at', 'Created by', 'Assigned to']);
        foreach ($tickets as $ticket) {
            fputcsv($stream, array_map(static function ($value): string {
                $value = (string) $value;
                return preg_match('/^[\s]*[=+@\-]/', $value) ? "'" . $value : $value;
            }, array_values($ticket)));
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="helpdeskhub-report-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    private function query(): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('tickets t')->select('t.id, t.ticket_number, t.subject, c.name AS category_name, t.priority, t.status, t.created_at, creator.name AS created_by, COALESCE(assignee.name, "") AS assigned_to')
            ->join('categories c', 'c.id = t.category_id')->join('users creator', 'creator.id = t.created_by')
            ->join('users assignee', 'assignee.id = t.assigned_to', 'left')->where('t.deleted_at', null);
        if ($this->user['role'] === 'Employee') $builder->where('t.created_by', $this->user['id']);
        if ($this->user['role'] === 'IT Support') $builder->where('t.assigned_to', $this->user['id']);
        foreach (['status' => ['t.status', ['Open', 'In Progress', 'Resolved', 'Closed']], 'priority' => ['t.priority', ['Low', 'Medium', 'High']]] as $key => [$column, $allowed]) {
            $value = $this->getString($key);
            if (in_array($value, $allowed, true)) $builder->where($column, $value);
        }
        $category = filter_var($this->getString('category'), FILTER_VALIDATE_INT);
        if ($category) $builder->where('t.category_id', $category);
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            $date = $this->getString($key);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $builder->where('DATE(t.created_at) ' . $operator, $date);
        }
        $query = trim($this->getString('q'));
        if ($query !== '') $builder->groupStart()->like('t.ticket_number', $query)->orLike('t.subject', $query)->groupEnd();
        return $builder;
    }
}
