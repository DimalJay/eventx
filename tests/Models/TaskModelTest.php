<?php

namespace Tests\Models;

use DateTime;
use Models\Task;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $task = new Task(7, 'Setup stage', 'Prepare the lights', 1, 2, 3, '2026-06-01 18:00:00');

        $this->assertSame(7, $this->prop($task, 'eventId'));
        $this->assertSame('Setup stage', $this->prop($task, 'title'));
        $this->assertSame('Prepare the lights', $this->prop($task, 'description'));
        $this->assertSame(1, $this->prop($task, 'createdBy'));
        $this->assertSame(2, $this->prop($task, 'assignedTo'));
        $this->assertSame(3, $this->prop($task, 'assignedBy'));
        $this->assertSame('TODO', $this->prop($task, 'status'));
        $this->assertInstanceOf(DateTime::class, $this->prop($task, 'createddAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($task, 'assignedDate'));
        $this->assertInstanceOf(DateTime::class, $this->prop($task, 'updatedAt'));

        $dueDate = $this->prop($task, 'dueDate');
        $this->assertSame('2026-06-01 18:00:00', $dueDate->format('Y-m-d H:i:s'));
    }

    public function testStatusCanBeOverridden(): void
    {
        $task = new Task(1, 'T', null, 1, 0, 1, '2026-06-01', 'IN_PROGRESS');
        $this->assertSame('IN_PROGRESS', $this->prop($task, 'status'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Task::class, Task::empty());
    }
}