<?php

namespace App\Libraries;

final class TicketPolicy
{
    public static function canView(array $user, array $ticket): bool
    {
        return match ($user['role'] ?? '') {
            'IT Manager' => true,
            'Employee' => (int) $ticket['created_by'] === (int) $user['id'],
            'IT Support' => (int) ($ticket['assigned_to'] ?? 0) === (int) $user['id'],
            default => false,
        };
    }

    public static function canChangeStatus(array $user, array $ticket, string $next): bool
    {
        if ($ticket['status'] === 'Closed') return false;
        if (($user['role'] ?? '') === 'IT Support' && (int) ($ticket['assigned_to'] ?? 0) === (int) $user['id']) {
            return ($ticket['status'] === 'Open' && $next === 'In Progress') || ($ticket['status'] === 'In Progress' && $next === 'Resolved');
        }
        return ($user['role'] ?? '') === 'Employee' && (int) $ticket['created_by'] === (int) $user['id']
            && $ticket['status'] === 'Resolved' && in_array($next, ['Closed', 'In Progress'], true);
    }
}
