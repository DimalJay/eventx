<?php

namespace Tests\Models;

use DateTime;
use Models\Notification;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $notification = new Notification('Title', 'Message', 5, 'Alert', '{"a":1}', true);

        $this->assertSame('Title', $this->prop($notification, 'title'));
        $this->assertSame('Message', $this->prop($notification, 'message'));
        $this->assertSame(5, $this->prop($notification, 'userId'));
        $this->assertSame('Alert', $this->prop($notification, 'type'));
        $this->assertSame('{"a":1}', $this->prop($notification, 'extras'));
        $this->assertSame('read', $this->prop($notification, 'status'));
        $this->assertTrue($this->prop($notification, 'isRead'));
        $this->assertInstanceOf(DateTime::class, $this->prop($notification, 'createdAt'));
    }

    public function testStatusIsReadWhenIsReadFlagSet(): void
    {
        $notification = new Notification('T', 'M', 1, 'General', null, true);
        $this->assertSame('read', $this->prop($notification, 'status'));
    }

    public function testStatusIsUnreadByDefault(): void
    {
        $notification = new Notification('T', 'M', 1);
        $this->assertSame('unread', $this->prop($notification, 'status'));
        $this->assertFalse($this->prop($notification, 'isRead'));
        $this->assertSame('General', $this->prop($notification, 'type'));
    }

    public function testNewNotificationIsUnread(): void
    {
        $this->assertSame('unread', $this->prop(Notification::empty(), 'status'));
    }
}