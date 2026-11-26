<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function loginForm(): string|\CodeIgniter\HTTP\ResponseInterface
    {
        if (session('user_id')) {
            return redirect()->to(site_url('/'));
        }
        return view('auth/login');
    }

    public function login()
    {
        $email = strtolower(trim($this->postString('email')));
        $password = $this->postString('password');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) > 4096) {
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password.');
        }

        $key = hash('sha256', $this->request->getIPAddress() . '|' . $email);
        $cutoff = date('Y-m-d H:i:s', time() - 900);
        $now = date('Y-m-d H:i:s');
        $this->db->table('login_rate_limits')->where('window_started_at <', $cutoff)->delete();
        $this->db->query('INSERT INTO login_rate_limits (attempt_key, window_started_at, attempt_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempt_count = IF(window_started_at < ?, 1, attempt_count + 1), window_started_at = IF(window_started_at < ?, ?, window_started_at)', [$key, $now, $cutoff, $cutoff, $now]);
        $attempts = (int) $this->db->table('login_rate_limits')->where('attempt_key', $key)->get()->getRow('attempt_count');
        if ($attempts > 5) {
            log_message('warning', 'Login rate limit reached for account key {key}', ['key' => $key]);
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password. Try again later.');
        }
        $user = $this->db->table('users')->where('email', $email)->where('is_active', 1)->get()->getRowArray();
        $validPassword = password_verify($password, $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (! $user || ! $validPassword) {
            log_message('notice', 'Login failed for account key {key}', ['key' => $key]);
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password.');
        }

        $this->db->table('login_rate_limits')->where('attempt_key', $key)->delete();
        session()->regenerate(true);
        unset($user['password_hash']);
        session()->set(['user_id' => $user['id'], 'auth_version' => $user['session_version'], 'user' => $user]);
        return redirect()->to(site_url('/'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }
}
