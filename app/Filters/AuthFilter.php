<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $id = (int) session('user_id');
        $user = $id ? db_connect()->table('users')->where('id', $id)->where('is_active', 1)->get()->getRowArray() : null;
        if (! $user || (int) ($user['session_version'] ?? -1) !== (int) session('auth_version')) {
            session()->destroy();
            return redirect()->to(site_url('login'));
        }
        unset($user['password_hash']);
        unset($user['session_version']);
        session()->set('user', $user);
        if ($user['force_password_change'] && ! str_starts_with($request->getUri()->getPath(), 'profile/password')) {
            return redirect()->to(site_url('profile/password'));
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
