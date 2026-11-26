<?php

namespace App\Controllers;

class Admin extends BaseController
{
    private const ROLES = ['Employee', 'IT Support', 'IT Manager', 'Administrator'];

    public function users(): string
    {
        $users = $this->db->table('users')->select('id,name,email,department,role,is_active')->orderBy('name')->get()->getResultArray();
        return $this->page('Manage users', view('admin/users', ['users' => $users]));
    }

    public function newUser(): string
    {
        return $this->page('Create user', view('admin/user_form', ['person' => null]));
    }

    public function createUser()
    {
        if (! $this->stringPosts(['name', 'email', 'department', 'role', 'password'])) return $this->safeBack()->with('error', 'User fields must be plain text.');
        if (! $this->validate($this->userRules(true))) return $this->safeBack()->with('errors', $this->validator->getErrors());
        $data = $this->userInput(true);
        if ($this->emailExists($data['email'])) return $this->safeBack()->with('error', 'Email is already in use.');
        $this->db->table('users')->insert($data + ['is_active' => true, 'force_password_change' => false, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        log_message('notice', 'Administrator {actor} created user {target}', ['actor' => $this->user['id'], 'target' => $this->db->insertID()]);
        return redirect()->to(site_url('admin/users'))->with('success', 'User created.');
    }

    public function showUser(int $id): string|\CodeIgniter\HTTP\ResponseInterface
    {
        $person = $this->db->table('users')->select('id,name,email,department,role,is_active,force_password_change,created_at,updated_at')->where('id', $id)->get()->getRowArray();
        if (! $person) return $this->response->setStatusCode(404)->setBody('Not found');
        return $this->page('User details', view('admin/user_detail', ['person' => $person]));
    }

    public function editUser(int $id): string|\CodeIgniter\HTTP\ResponseInterface
    {
        $person = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        if (! $person) return $this->response->setStatusCode(404)->setBody('Not found');
        return $this->page('Edit user', view('admin/user_form', ['person' => $person]));
    }

    public function updateUser(int $id)
    {
        $person = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        if (! $person) return $this->response->setStatusCode(404)->setBody('Not found');
        if (! $this->stringPosts(['name', 'email', 'department', 'role'])) return $this->safeBack()->with('error', 'User fields must be plain text.');
        if (! $this->validate($this->userRules(false))) return $this->safeBack()->with('errors', $this->validator->getErrors());
        $data = $this->userInput();
        if ($this->emailExists($data['email'], $id)) return $this->safeBack()->with('error', 'Email is already in use.');
        $this->db->transBegin();
        $locked = $this->db->query('SELECT id, role FROM users WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        if (! $locked || ($locked['role'] === 'IT Support' && $data['role'] !== 'IT Support' && $this->hasActiveAssignments($id))) {
            $this->db->transRollback();
            return $this->safeBack()->with('error', 'Reassign this user’s open tickets before changing their role.');
        }
        $this->db->table('users')->where('id', $id)->update($data + ['updated_at' => date('Y-m-d H:i:s')]);
        $this->db->transComplete();
        if (! $this->db->transStatus()) return $this->safeBack()->with('error', 'User could not be updated.');
        log_message('notice', 'Administrator {actor} updated user {target}', ['actor' => $this->user['id'], 'target' => $id]);
        return redirect()->to(site_url('admin/users/' . $id))->with('success', 'User updated.');
    }

    public function resetPassword(int $id)
    {
        $person = $this->db->table('users')->select('id,session_version')->where('id', $id)->get()->getRowArray();
        if (! $person) return $this->response->setStatusCode(404)->setBody('Not found');
        if (! $this->stringPosts(['temporary_password'])) return $this->safeBack()->with('error', 'Temporary password must be plain text.');
        $temporary = $this->postString('temporary_password');
        if (strlen($temporary) < 12 || strlen($temporary) > 255) return $this->safeBack()->with('error', 'Temporary password must be 12–255 characters.');
        $this->db->table('users')->where('id', $id)->update([
            'password_hash' => password_hash($temporary, PASSWORD_DEFAULT), 'force_password_change' => true,
            'session_version' => (int) $person['session_version'] + 1, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        log_message('notice', 'Administrator {actor} reset password for user {target}', ['actor' => $this->user['id'], 'target' => $id]);
        return redirect()->to(site_url('admin/users/' . $id))->with('success', 'Temporary password set. Share it securely and ask the user to change it at sign in.');
    }

    public function toggleUser(int $id)
    {
        $person = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        if (! $person) return $this->response->setStatusCode(404)->setBody('Not found');
        if ($id === (int) $this->user['id'] && $person['is_active']) return $this->safeBack()->with('error', 'You cannot deactivate your own account.');
        $this->db->transBegin();
        $locked = $this->db->query('SELECT id, role, is_active FROM users WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        if (! $locked || ($locked['role'] === 'IT Support' && $locked['is_active'] && $this->hasActiveAssignments($id))) {
            $this->db->transRollback();
            return $this->safeBack()->with('error', 'Reassign this user’s open tickets before deactivation.');
        }
        $active = ! (bool) $locked['is_active'];
        $this->db->table('users')->where('id', $id)->update(['is_active' => $active, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->db->transComplete();
        if (! $this->db->transStatus()) return $this->safeBack()->with('error', 'Account status could not be updated.');
        log_message('notice', 'Administrator {actor} changed active status for user {target}', ['actor' => $this->user['id'], 'target' => $id]);
        return redirect()->to(site_url('admin/users/' . $id))->with('success', 'Account status updated.');
    }

    public function categories(): string
    {
        $categories = $this->db->table('categories')->orderBy('name')->get()->getResultArray();
        return $this->page('Ticket categories', view('admin/categories', ['categories' => $categories]));
    }

    public function createCategory()
    {
        if (! $this->stringPosts(['name'])) return $this->safeBack()->with('error', 'Category name must be plain text.');
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[80]'])) return $this->safeBack()->withInput()->with('errors', $this->validator->getErrors());
        $name = trim($this->postString('name'));
        if ($this->db->table('categories')->where('name', $name)->countAllResults()) return $this->safeBack()->withInput()->with('error', 'Category name already exists.');
        $this->db->table('categories')->insert(['name' => $name, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        log_message('notice', 'Administrator {actor} created a category', ['actor' => $this->user['id']]);
        return redirect()->to(site_url('admin/categories'))->with('success', 'Category created.');
    }

    public function updateCategory(int $id)
    {
        if (! $this->stringPosts(['name'])) return $this->safeBack()->with('error', 'Category name must be plain text.');
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[80]'])) return $this->safeBack()->withInput()->with('errors', $this->validator->getErrors());
        $name = trim($this->postString('name'));
        if ($this->db->table('categories')->where('name', $name)->where('id !=', $id)->countAllResults()) return $this->safeBack()->with('error', 'Category name already exists.');
        $this->db->table('categories')->where('id', $id)->update(['name' => $name, 'updated_at' => date('Y-m-d H:i:s')]);
        log_message('notice', 'Administrator {actor} updated category {target}', ['actor' => $this->user['id'], 'target' => $id]);
        return redirect()->to(site_url('admin/categories'))->with('success', 'Category updated.');
    }

    public function toggleCategory(int $id)
    {
        $category = $this->db->table('categories')->where('id', $id)->get()->getRowArray();
        if (! $category) return $this->response->setStatusCode(404)->setBody('Not found');
        $this->db->table('categories')->where('id', $id)->update(['is_active' => ! (bool) $category['is_active'], 'updated_at' => date('Y-m-d H:i:s')]);
        log_message('notice', 'Administrator {actor} changed category status {target}', ['actor' => $this->user['id'], 'target' => $id]);
        return redirect()->to(site_url('admin/categories'))->with('success', 'Category status updated.');
    }

    private function userRules(bool $password): array
    {
        $rules = ['name' => 'required|min_length[2]|max_length[120]', 'email' => 'required|valid_email|max_length[190]', 'department' => 'permit_empty|max_length[120]', 'role' => 'required|in_list[Employee,IT Support,IT Manager,Administrator]'];
        if ($password) $rules['password'] = 'required|min_length[12]|max_length[255]';
        return $rules;
    }

    private function userInput(bool $includePassword = false): array
    {
        $data = ['name' => trim($this->postString('name')), 'email' => strtolower(trim($this->postString('email'))), 'department' => trim($this->postString('department')), 'role' => $this->postString('role')];
        if ($includePassword) $data['password_hash'] = password_hash($this->postString('password'), PASSWORD_DEFAULT);
        return $data;
    }

    private function emailExists(string $email, int $exceptId = 0): bool
    {
        $query = $this->db->table('users')->where('email', $email);
        if ($exceptId) $query->where('id !=', $exceptId);
        return $query->countAllResults() > 0;
    }

    private function hasActiveAssignments(int $userId): bool
    {
        return $this->db->table('tickets')->where('assigned_to', $userId)->whereIn('status', ['Open', 'In Progress'])->where('deleted_at', null)->countAllResults() > 0;
    }
}
