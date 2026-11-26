<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use App\Libraries\TicketPolicy;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url'];
    protected BaseConnection $db;
    protected array $user;

    /**
     * @param CLIRequest|IncomingRequest $request
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        $this->db = db_connect();
        $this->user = (array) session('user');
    }

    protected function page(string $title, string $content, array $data = []): string
    {
        return view('layout', ['title' => $title, 'content' => $content, 'user' => $this->user] + $data);
    }

    protected function canView(array $ticket): bool
    {
        return TicketPolicy::canView($this->user, $ticket);
    }

    protected function ticket(int $id): ?array
    {
        $ticket = $this->db->table('tickets t')->select('t.*, c.name AS category_name, creator.name AS creator_name, assignee.name AS assignee_name')
            ->join('categories c', 'c.id = t.category_id')->join('users creator', 'creator.id = t.created_by')
            ->join('users assignee', 'assignee.id = t.assigned_to', 'left')->where('t.id', $id)->where('t.deleted_at', null)->get()->getRowArray();
        return $ticket && $this->canView($ticket) ? $ticket : null;
    }

    protected function lockTicket(int $id): ?array
    {
        return $this->db->query('SELECT * FROM tickets WHERE id = ? AND deleted_at IS NULL FOR UPDATE', [$id])->getRowArray() ?: null;
    }

    protected function logHistory(int $ticketId, string $event, string $details = ''): void
    {
        $this->db->table('ticket_histories')->insert([
            'ticket_id' => $ticketId, 'user_id' => $this->user['id'], 'event' => $event,
            'details' => mb_substr($details, 0, 255), 'occurred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function postString(string $key): string
    {
        $value = $this->request->getPost($key);
        return is_string($value) ? $value : '';
    }

    protected function getString(string $key): string
    {
        $value = $this->request->getGet($key);
        return is_string($value) ? $value : '';
    }

    protected function stringPosts(array $keys): bool
    {
        foreach ($keys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null && ! is_string($value)) return false;
        }
        return true;
    }

    protected function safeBack(): \CodeIgniter\HTTP\RedirectResponse
    {
        $target = new \CodeIgniter\HTTP\URI(previous_url());
        $host = $target->getHost();
        $current = $this->request->getUri();
        $external = $host !== '' && (strcasecmp($host, $current->getHost()) !== 0
            || strcasecmp($target->getScheme(), $current->getScheme()) !== 0
            || $target->getPort() !== $current->getPort());

        return redirect()->to($external ? site_url('/') : (string) $target);
    }
}
