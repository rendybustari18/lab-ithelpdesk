<?php

namespace App\Controllers;

class Profile extends BaseController
{
    public function edit(): string
    {
        return $this->page('My profile', view('profile/edit', ['user' => $this->user]));
    }

    public function save()
    {
        if (! $this->stringPosts(['name', 'email', 'department'])) return $this->safeBack()->with('error', 'Profile fields must be plain text.');
        $rules = ['name' => 'required|min_length[2]|max_length[120]', 'email' => 'required|valid_email|max_length[190]', 'department' => 'permit_empty|max_length[120]'];
        if (! $this->validate($rules)) {
            return $this->safeBack()->withInput()->with('errors', $this->validator->getErrors());
        }
        $email = strtolower(trim($this->postString('email')));
        $duplicate = $this->db->table('users')->where('email', $email)->where('id !=', $this->user['id'])->countAllResults();
        if ($duplicate) {
            return $this->safeBack()->withInput()->with('errors', ['email' => 'Email is already in use.']);
        }
        $this->db->table('users')->where('id', $this->user['id'])->update([
            'name' => trim($this->postString('name')), 'email' => $email,
            'department' => trim($this->postString('department')), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('profile'))->with('success', 'Profile updated.');
    }

    public function password(): string
    {
        return $this->page('Change password', view('profile/password'));
    }

    public function changePassword()
    {
        if (! $this->stringPosts(['current_password', 'password', 'password_confirm'])) return $this->safeBack()->with('error', 'Password fields must be plain text.');
        $rules = [
            'current_password' => 'required|max_length[4096]',
            'password' => 'required|min_length[12]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (! $this->validate($rules)) {
            return $this->safeBack()->with('errors', $this->validator->getErrors());
        }
        $row = $this->db->table('users')->where('id', $this->user['id'])->get()->getRowArray();
        if (! password_verify($this->postString('current_password'), $row['password_hash'])) {
            return $this->safeBack()->with('error', 'Current password is incorrect.');
        }
        $this->db->table('users')->where('id', $this->user['id'])->update([
            'password_hash' => password_hash($this->postString('password'), PASSWORD_DEFAULT),
            'force_password_change' => false, 'session_version' => (int) $row['session_version'] + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        session()->regenerate(true);
        session()->set('auth_version', (int) $row['session_version'] + 1);
        return redirect()->to(site_url('/'))->with('success', 'Password updated.');
    }
}
