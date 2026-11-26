<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! in_array(session('user')['role'] ?? null, $arguments ?? [], true)) {
            log_message('warning', 'Authorization denied for user ID {id}', ['id' => session('user.id')]);
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
