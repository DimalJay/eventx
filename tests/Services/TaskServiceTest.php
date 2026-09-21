<?php

namespace Tests\Services;

use Services\TaskService;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskService $service;

    protected function setUp(): void
    {
        $this->service = new TaskService();
    }

    public function testUpdateTaskReturnsNullForEmptyData(): void
    {
        $this->assertNull($this->service->updateTask(1, []));
    }
}