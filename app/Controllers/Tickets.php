<?php

namespace App\Controllers;

use App\Libraries\TicketPolicy;
use App\Libraries\AttachmentPolicy;

class Tickets extends BaseController
{
    public function index(): string|\CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->user['role'] === 'Administrator') return redirect()->to(site_url('admin/users'));
        $builder = $this->db->table('tickets t')->select('t.*, c.name AS category_name')->join('categories c', 'c.id = t.category_id')->where('t.deleted_at', null);
        if ($this->user['role'] === 'Employee') $builder->where('t.created_by', $this->user['id']);
        if ($this->user['role'] === 'IT Support') $builder->where('t.assigned_to', $this->user['id']);
        foreach (['status' => 't.status', 'priority' => 't.priority', 'category' => 't.category_id'] as $param => $column) {
            $value = $this->getString($param);
            if ($value !== null && $value !== '') $builder->where($column, $value);
        }
        foreach (['from' => '>=', 'to' => '<='] as $param => $operator) {
            $date = $this->getString($param);
            if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $builder->where('DATE(t.created_at) ' . $operator, $date);
        }
        $query = trim($this->getString('q'));
        if ($query !== '') $builder->groupStart()->like('t.ticket_number', $query)->orLike('t.subject', $query)->groupEnd();
        $page = max(1, min(10000, (int) $this->getString('page')));
        $total = (clone $builder)->countAllResults();
        $tickets = $builder->orderBy('t.created_at', 'DESC')->limit(15, ($page - 1) * 15)->get()->getResultArray();
        return $this->page('Tickets', view('tickets/index', [
            'user' => $this->user, 'tickets' => $tickets, 'categories' => $this->db->table('categories')->orderBy('name')->get()->getResultArray(),
            'total' => $total, 'pageNumber' => $page,
            'filters' => array_combine(['q', 'status', 'priority', 'category', 'from', 'to'], array_map(fn (string $key): string => $this->getString($key), ['q', 'status', 'priority', 'category', 'from', 'to'])),
        ]));
    }

    public function new(): string|\CodeIgniter\HTTP\ResponseInterface
    {
        if ($this->user['role'] !== 'Employee') return $this->denied();
        return $this->page('Create ticket', view('tickets/form', ['ticket' => null, 'categories' => $this->activeCategories()]));
    }

    public function create()
    {
        if ($this->user['role'] !== 'Employee') return $this->denied();
        if (! $this->stringPosts(['subject', 'description', 'category_id', 'priority'])) return $this->safeBack()->with('error', 'Ticket fields must be plain text.');
        if (! $this->validate($this->ticketRules())) return $this->safeBack()->withInput()->with('errors', $this->validator->getErrors());
        $categoryId = (int) filter_var($this->postString('category_id'), FILTER_VALIDATE_INT);
        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        $category = $this->db->query('SELECT id FROM categories WHERE id = ? AND is_active = 1 FOR UPDATE', [$categoryId])->getRowArray();
        if (! $category) { $this->db->transRollback(); return $this->safeBack()->withInput()->with('error', 'Choose an active category.'); }
        $this->db->table('tickets')->insert([
            'ticket_number' => 'TMP-' . bin2hex(random_bytes(8)), 'subject' => trim($this->postString('subject')),
            'description' => trim($this->postString('description')), 'category_id' => $category['id'],
            'priority' => $this->postString('priority'), 'status' => 'Open', 'created_by' => $this->user['id'],
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $number = sprintf('HD-%s-%06d', date('Y'), $id);
        $this->db->table('tickets')->where('id', $id)->update(['ticket_number' => $number]);
        $this->logHistory($id, 'created', 'Ticket created');
        $this->db->transComplete();
        if (! $this->db->transStatus()) return $this->safeBack()->withInput()->with('error', 'Ticket could not be saved.');
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Ticket created.');
    }

    public function show(int $id): string|\CodeIgniter\HTTP\ResponseInterface
    {
        $ticket = $this->ticket($id);
        if (! $ticket) return $this->denied();
        $comments = $this->db->table('comments c')->select('c.*, u.name')->join('users u', 'u.id = c.user_id')->where('c.ticket_id', $id)->orderBy('c.created_at')->get()->getResultArray();
        $attachments = $this->db->table('attachments')->where('ticket_id', $id)->orderBy('created_at')->get()->getResultArray();
        $support = $this->user['role'] === 'IT Manager' ? $this->db->table('users')->where('role', 'IT Support')->where('is_active', 1)->orderBy('name')->get()->getResultArray() : [];
        return $this->page($ticket['ticket_number'], view('tickets/show', ['user' => $this->user, 'ticket' => $ticket, 'comments' => $comments, 'attachments' => $attachments, 'support' => $support]));
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\ResponseInterface
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $this->user['role'] !== 'Employee' || $ticket['status'] !== 'Open') return $this->denied();
        return $this->page('Edit ticket', view('tickets/form', ['ticket' => $ticket, 'categories' => $this->activeCategories()]));
    }

    public function update(int $id)
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $this->user['role'] !== 'Employee' || $ticket['status'] !== 'Open') return $this->denied();
        if (! $this->stringPosts(['subject', 'description', 'category_id', 'priority'])) return $this->safeBack()->with('error', 'Ticket fields must be plain text.');
        if (! $this->validate($this->ticketRules())) return $this->safeBack()->withInput()->with('errors', $this->validator->getErrors());
        $category = $this->activeCategory((int) filter_var($this->postString('category_id'), FILTER_VALIDATE_INT));
        if (! $category) return $this->safeBack()->withInput()->with('error', 'Choose an active category.');
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        $activeCategory = $this->db->query('SELECT id FROM categories WHERE id = ? AND is_active = 1 FOR UPDATE', [$category['id']])->getRowArray();
        if (! $locked || ! $this->canView($locked) || $locked['status'] !== 'Open' || ! $activeCategory) { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('tickets')->where('id', $id)->where('status', 'Open')->update([
            'subject' => trim($this->postString('subject')), 'description' => trim($this->postString('description')),
            'category_id' => $category['id'], 'priority' => $this->postString('priority'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logHistory($id, 'updated', 'Ticket details updated');
        $this->db->transComplete();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Ticket updated.');
    }

    public function delete(int $id)
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $this->user['role'] !== 'Employee' || $ticket['status'] !== 'Open') return $this->denied();
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        if (! $locked || ! $this->canView($locked) || $locked['status'] !== 'Open') { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('tickets')->where('id', $id)->where('created_by', $this->user['id'])->where('status', 'Open')->update(['deleted_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        if ($this->db->affectedRows() !== 1) { $this->db->transRollback(); return $this->denied(); }
        $this->logHistory($id, 'deleted', 'Ticket removed from active lists');
        $this->db->transComplete();
        return redirect()->to(site_url('tickets'))->with('success', 'Ticket deleted.');
    }

    public function status(int $id)
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $ticket['status'] === 'Closed') return $this->denied();
        $next = $this->postString('status');
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        if (! $locked || ! $this->canView($locked) || ! TicketPolicy::canChangeStatus($this->user, $locked, $next)) {
            $this->db->transRollback();
            return $this->denied();
        }
        $this->db->table('tickets')->where('id', $id)->where('status', $locked['status'])->update(['status' => $next, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($this->db->affectedRows() !== 1) { $this->db->transRollback(); return $this->denied(); }
        $this->logHistory($id, 'status changed', $locked['status'] . ' to ' . $next);
        $this->db->transComplete();
        if (! $this->db->transStatus()) return $this->safeBack();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Status updated.');
    }

    public function assign(int $id)
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $this->user['role'] !== 'IT Manager' || ! in_array($ticket['status'], ['Open', 'In Progress'], true)) return $this->denied();
        $supportId = (int) filter_var($this->postString('support_id'), FILTER_VALIDATE_INT);
        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        $support = $this->db->query('SELECT id FROM users WHERE id = ? AND role = ? AND is_active = 1 FOR UPDATE', [$supportId, 'IT Support'])->getRowArray();
        if (! $locked || ! $this->canView($locked) || ! in_array($locked['status'], ['Open', 'In Progress'], true) || ! $support) {
            $this->db->transRollback();
            return $this->safeBack()->with('error', 'Ticket or support account is no longer available for assignment.');
        }
        if ((int) $locked['assigned_to'] === $supportId) { $this->db->transRollback(); return $this->safeBack()->with('success', 'Ticket is already assigned to that user.'); }
        $this->db->table('tickets')->where('id', $id)->whereIn('status', ['Open', 'In Progress'])->update(['assigned_to' => $supportId, 'updated_at' => $now]);
        if ($this->db->affectedRows() !== 1) { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('ticket_assignments')->insert(['ticket_id' => $id, 'support_id' => $supportId, 'manager_id' => $this->user['id'], 'assigned_at' => $now]);
        $this->logHistory($id, 'assigned', 'Assigned to support user ' . $supportId);
        $this->db->transComplete();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Ticket assigned.');
    }

    public function comment(int $id)
    {
        $ticket = $this->ticket($id);
        $body = trim($this->postString('body'));
        if (! $ticket || $ticket['status'] === 'Closed' || $body === '' || mb_strlen($body) > 5000) return $this->denied();
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        if (! $locked || ! $this->canView($locked) || $locked['status'] === 'Closed') { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('comments')->insert(['ticket_id' => $id, 'user_id' => $this->user['id'], 'body' => $body, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->logHistory($id, 'comment added', 'Comment added');
        $this->db->transComplete();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Comment added.');
    }

    public function editComment(int $id, int $commentId)
    {
        $ticket = $this->ticket($id);
        $body = trim($this->postString('body'));
        if (! $ticket || $ticket['status'] === 'Closed' || $body === '' || mb_strlen($body) > 5000) return $this->denied();
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        $comment = $this->db->query('SELECT * FROM comments WHERE id = ? AND ticket_id = ? FOR UPDATE', [$commentId, $id])->getRowArray();
        if (! $locked || ! $this->canView($locked) || $locked['status'] === 'Closed' || ! $comment || (int) $comment['user_id'] !== (int) $this->user['id']) { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('comments')->where('id', $commentId)->update(['body' => $body, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->logHistory($id, 'comment edited', 'Comment edited');
        $this->db->transComplete();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Comment updated.');
    }

    public function deleteComment(int $id, int $commentId)
    {
        $ticket = $this->ticket($id);
        if (! $ticket || $ticket['status'] === 'Closed') return $this->denied();
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        $comment = $this->db->query('SELECT * FROM comments WHERE id = ? AND ticket_id = ? FOR UPDATE', [$commentId, $id])->getRowArray();
        if (! $locked || ! $this->canView($locked) || $locked['status'] === 'Closed' || ! $comment || (int) $comment['user_id'] !== (int) $this->user['id']) { $this->db->transRollback(); return $this->denied(); }
        $this->db->table('comments')->where('id', $commentId)->delete();
        $this->logHistory($id, 'comment deleted', 'Comment deleted');
        $this->db->transComplete();
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Comment deleted.');
    }

    public function upload(int $id)
    {
        $ticket = $this->ticket($id);
        $file = $this->request->getFile('attachment');
        if (! $file instanceof \CodeIgniter\HTTP\Files\UploadedFile) return $this->safeBack()->with('error', 'Choose a single attachment file.');
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
        $extension = strtolower($file->getClientExtension());
        if (! $ticket || $ticket['status'] === 'Closed' || ! $file || ! $file->isValid() || $file->hasMoved() || $file->getSize() > 2 * 1024 * 1024
            || ! isset($allowed[$extension]) || $file->getMimeType() !== $allowed[$extension] || ! AttachmentPolicy::validContent($file->getTempName(), $extension)) return $this->safeBack()->with('error', 'File must be a valid JPG, PNG, or PDF no larger than 2 MB.');
        $folder = WRITEPATH . 'uploads/tickets';
        if (! is_dir($folder) && ! mkdir($folder, 0750, true) && ! is_dir($folder)) return $this->safeBack()->with('error', 'Upload storage is unavailable.');
        $key = bin2hex(random_bytes(16)) . '.' . $extension;
        $originalName = basename(str_replace('\\', '/', $file->getClientName()));
        $originalName = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?: ('attachment.' . $extension), 0, 190);
        $this->db->transBegin();
        $locked = $this->lockTicket($id);
        if (! $locked || ! $this->canView($locked) || $locked['status'] === 'Closed') { $this->db->transRollback(); return $this->denied(); }
        if ($this->db->table('attachments')->where('ticket_id', $id)->countAllResults() >= 3) { $this->db->transRollback(); return $this->safeBack()->with('error', 'A ticket can have at most three attachments.'); }
        try {
            $file->move($folder, $key);
        } catch (\Throwable) {
            $this->db->transRollback();
            return $this->safeBack()->with('error', 'Attachment could not be stored.');
        }
        $this->db->table('attachments')->insert([
            'ticket_id' => $id, 'user_id' => $this->user['id'], 'storage_key' => $key,
            'original_name' => $originalName, 'mime_type' => $allowed[$extension], 'size_bytes' => $file->getSize(), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logHistory($id, 'attachment added', 'Attachment added');
        $this->db->transComplete();
        if (! $this->db->transStatus()) { if (is_file($folder . '/' . $key)) unlink($folder . '/' . $key); return $this->safeBack()->with('error', 'Attachment could not be saved.'); }
        return redirect()->to(site_url('tickets/' . $id))->with('success', 'Attachment uploaded.');
    }

    public function download(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $attachment = $this->db->table('attachments')->where('id', $id)->get()->getRowArray();
        $ticket = $attachment ? $this->ticket((int) $attachment['ticket_id']) : null;
        $path = $attachment ? WRITEPATH . 'uploads/tickets/' . $attachment['storage_key'] : '';
        if (! $attachment || ! $ticket || ! is_file($path) || ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|pdf)$/', $attachment['storage_key'])) return $this->denied();
        return $this->response->download($path, null)->setFileName($attachment['original_name']);
    }

    public function deleteAttachment(int $id)
    {
        $attachment = $this->db->table('attachments')->where('id', $id)->get()->getRowArray();
        $ticket = $attachment ? $this->ticket((int) $attachment['ticket_id']) : null;
        if (! $attachment || ! $ticket || $ticket['status'] === 'Closed' || (int) $attachment['user_id'] !== (int) $this->user['id']) return $this->denied();
        $this->db->transBegin();
        $locked = $this->lockTicket((int) $ticket['id']);
        $lockedAttachment = $this->db->query('SELECT * FROM attachments WHERE id = ? AND ticket_id = ? FOR UPDATE', [$id, $ticket['id']])->getRowArray();
        if (! $locked || ! $this->canView($locked) || $locked['status'] === 'Closed' || ! $lockedAttachment || (int) $lockedAttachment['user_id'] !== (int) $this->user['id']) { $this->db->transRollback(); return $this->denied(); }
        $path = WRITEPATH . 'uploads/tickets/' . $lockedAttachment['storage_key'];
        $this->db->table('attachments')->where('id', $id)->delete();
        $this->logHistory((int) $ticket['id'], 'attachment deleted', 'Attachment deleted');
        $this->db->transComplete();
        if (preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|pdf)$/', $lockedAttachment['storage_key']) && is_file($path)) unlink($path);
        return redirect()->to(site_url('tickets/' . $ticket['id']))->with('success', 'Attachment deleted.');
    }

    public function history(int $id): string|\CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->ticket($id)) return $this->denied();
        $events = $this->db->table('ticket_histories h')->select('h.*, u.name')->join('users u', 'u.id = h.user_id')->where('h.ticket_id', $id)->orderBy('h.occurred_at')->get()->getResultArray();
        return $this->page('Ticket history', view('tickets/history', ['events' => $events, 'ticketId' => $id]));
    }

    private function ticketRules(): array
    {
        return ['subject' => 'required|min_length[3]|max_length[180]', 'description' => 'required|min_length[5]|max_length[10000]', 'category_id' => 'required|is_natural_no_zero', 'priority' => 'required|in_list[Low,Medium,High]'];
    }

    private function activeCategories(): array
    {
        return $this->db->table('categories')->where('is_active', 1)->orderBy('name')->get()->getResultArray();
    }

    private function activeCategory(int $id): ?array
    {
        return $this->db->table('categories')->where('id', $id)->where('is_active', 1)->get()->getRowArray();
    }

    private function denied(): \CodeIgniter\HTTP\ResponseInterface
    {
        log_message('warning', 'Ticket access denied for user ID {id}', ['id' => $this->user['id'] ?? 'unknown']);
        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }
}
