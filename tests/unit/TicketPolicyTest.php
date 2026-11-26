<?php

use App\Libraries\TicketPolicy;
use CodeIgniter\Test\CIUnitTestCase;

final class TicketPolicyTest extends CIUnitTestCase
{
    public function testTicketVisibilityIsScopedByRoleAndRelationship(): void
    {
        $ticket = ['created_by' => 10, 'assigned_to' => 20];
        self::assertTrue(TicketPolicy::canView(['id' => 10, 'role' => 'Employee'], $ticket));
        self::assertFalse(TicketPolicy::canView(['id' => 11, 'role' => 'Employee'], $ticket));
        self::assertTrue(TicketPolicy::canView(['id' => 20, 'role' => 'IT Support'], $ticket));
        self::assertFalse(TicketPolicy::canView(['id' => 21, 'role' => 'IT Support'], $ticket));
        self::assertTrue(TicketPolicy::canView(['id' => 30, 'role' => 'IT Manager'], $ticket));
        self::assertFalse(TicketPolicy::canView(['id' => 1, 'role' => 'Administrator'], $ticket));
    }

    public function testOnlyDocumentedStatusTransitionsAreAllowed(): void
    {
        $ticket = ['created_by' => 10, 'assigned_to' => 20, 'status' => 'Open'];
        self::assertTrue(TicketPolicy::canChangeStatus(['id' => 20, 'role' => 'IT Support'], $ticket, 'In Progress'));
        self::assertFalse(TicketPolicy::canChangeStatus(['id' => 21, 'role' => 'IT Support'], $ticket, 'In Progress'));
        self::assertFalse(TicketPolicy::canChangeStatus(['id' => 10, 'role' => 'Employee'], $ticket, 'Resolved'));
        self::assertTrue(TicketPolicy::canChangeStatus(['id' => 20, 'role' => 'IT Support'], ['created_by' => 10, 'assigned_to' => 20, 'status' => 'In Progress'], 'Resolved'));
        self::assertTrue(TicketPolicy::canChangeStatus(['id' => 10, 'role' => 'Employee'], ['created_by' => 10, 'assigned_to' => 20, 'status' => 'Resolved'], 'Closed'));
        self::assertTrue(TicketPolicy::canChangeStatus(['id' => 10, 'role' => 'Employee'], ['created_by' => 10, 'assigned_to' => 20, 'status' => 'Resolved'], 'In Progress'));
        self::assertFalse(TicketPolicy::canChangeStatus(['id' => 10, 'role' => 'Employee'], ['created_by' => 10, 'assigned_to' => 20, 'status' => 'Closed'], 'In Progress'));
    }
}
