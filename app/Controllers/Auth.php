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
        $accountKey = hash('sha256', 'account|' . $email);
        $cutoff = date('Y-m-d H:i:s', time() - 900);
        $now = date('Y-m-d H:i:s');
        $this->db->table('login_rate_limits')->where('window_started_at <', $cutoff)->delete();
        $this->db->table('account_login_risks')->where('window_started_at <', $cutoff)->delete();
        $this->db->query('INSERT INTO login_rate_limits (attempt_key, window_started_at, attempt_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempt_count = IF(window_started_at < ?, 1, attempt_count + 1), window_started_at = IF(window_started_at < ?, ?, window_started_at)', [$key, $now, $cutoff, $cutoff, $now]);
        $attempts = (int) $this->db->table('login_rate_limits')->where('attempt_key', $key)->get()->getRow('attempt_count');
        if ($attempts > 5) {
            log_message('warning', 'Login rate limit reached for account key {key}', ['key' => $key]);
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password. Try again later.');
        }

        // Escalate the cost of repeated guesses across source IPs without locking the account.
        $accountFailures = (int) ($this->db->table('account_login_risks')
            ->where('attempt_key', $accountKey)
            ->where('window_started_at >=', $cutoff)
            ->get()->getRow('failure_count') ?? 0);
        if ($accountFailures > 3) {
            $delaySeconds = min(5, 1 << min($accountFailures - 4, 3));
            usleep($delaySeconds * 1_000_000);
        }

        $user = $this->db->table('users')->where('email', $email)->where('is_active', 1)->get()->getRowArray();
        $validPassword = password_verify($password, $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (! $user || ! $validPassword) {
            $this->db->query('INSERT INTO account_login_risks (attempt_key, window_started_at, failure_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE failure_count = IF(window_started_at < ?, 1, failure_count + 1), window_started_at = IF(window_started_at < ?, ?, window_started_at)', [$accountKey, $now, $cutoff, $cutoff, $now]);
            $accountFailures = (int) $this->db->table('account_login_risks')->where('attempt_key', $accountKey)->get()->getRow('failure_count');
            if ($accountFailures === 4) {
                log_message('warning', 'Repeated login failures for account key {key}', ['key' => $accountKey]);
            }
            log_message('notice', 'Login failed for account key {key}', ['key' => $key]);
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password.');
        }

        $this->db->table('login_rate_limits')->where('attempt_key', $key)->delete();
        $this->db->table('account_login_risks')->where('attempt_key', $accountKey)->delete();
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
