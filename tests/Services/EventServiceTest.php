<?php

namespace Tests\Services;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use Services\EventService;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    private EventService $service;

    protected function setUp(): void
    {
        $this->service = new EventService();
    }

    private function event(array $overrides = []): array
    {
        return array_merge([
            'status' => 'upcoming',
            'endDate' => 'tomorrow 10:00:00',
            'regDeadline' => null,
        ], $overrides);
    }

    public function testRegistrationOpenForUpcomingEvent(): void
    {
        $this->service->assertRegistrationOpen($this->event());
        $this->assertTrue($this->service->isRegistrationOpen($this->event()));
    }

    #[DataProvider('closedStatuses')]
    public function testClosedStatusesRejectRegistration(string $status, string $expectedMessage): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->service->assertRegistrationOpen($this->event(['status' => $status]));
    }

    public static function closedStatuses(): array
    {
        return [
            'cancelled' => ['cancelled', 'Event has been cancelled.'],
            'CANCELLED uppercase' => ['CANCELLED', 'Event has been cancelled.'],
            'completed' => ['completed', 'Event has ended.'],
            'ended' => ['ended', 'Event has ended.'],
            'closed' => ['closed', 'Event is closed.'],
            'draft' => ['draft', 'Event is not open for registration.'],
            'DRAFT mixed case' => ['DRAFT', 'Event is not open for registration.'],
        ];
    }

    public function testEndedEventByDateRejectsRegistration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event has ended.');

        $this->service->assertRegistrationOpen($this->event(['endDate' => 'yesterday 10:00:00']));
    }

    public function testPassedDeadlineRejectsRegistration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Registration deadline has passed.');

        $this->service->assertRegistrationOpen($this->event(['regDeadline' => 'yesterday 23:59:59']));
    }

    public function testEndDateBeatsDeadlineInMessagePriority(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event has ended.');

        $this->service->assertRegistrationOpen($this->event([
            'endDate' => 'yesterday 10:00:00',
            'regDeadline' => 'tomorrow 23:59:59',
        ]));
    }

    public function testIsRegistrationOpenReturnsFalseForClosedEvents(): void
    {
        $this->assertFalse($this->service->isRegistrationOpen($this->event(['status' => 'cancelled'])));
        $this->assertFalse($this->service->isRegistrationOpen($this->event(['endDate' => 'yesterday'])));
        $this->assertFalse($this->service->isRegistrationOpen($this->event(['regDeadline' => 'yesterday'])));
    }

    public function testWhitespacePaddedStatusIsTrimmed(): void
    {
        $this->service->assertRegistrationOpen($this->event(['status' => '  upcoming  ']));
        self::assertFalse($this->service->isRegistrationOpen($this->event(['status' => '  cancelled '])));
    }

    public function testMissingStatusDefaultsToUpcoming(): void
    {
        $event = $this->event();
        unset($event['status']);
        $this->assertTrue($this->service->isRegistrationOpen($event));
    }

    public function testEmptyEndDateIsIgnored(): void
    {
        $this->assertTrue($this->service->isRegistrationOpen($this->event(['endDate' => '', 'regDeadline' => ''])));
    }

    public function testDeadlineTodayInFutureIsStillOpen(): void
    {
        $this->assertTrue($this->service->isRegistrationOpen($this->event([
            'endDate' => 'tomorrow 10:00:00',
            'regDeadline' => 'tomorrow 00:00:00',
        ])));
    }

    // ---- formatEvent (private) ----

    public function testFormatEventDecodesCustomFields(): void
    {
        $event = ['id' => 1, 'customFields' => '{"venue":"Galle"}'];
        $result = $this->invoke($this->service, 'formatEvent', [$event]);

        $this->assertSame(['venue' => 'Galle'], $result['customFields']);
    }

    public function testFormatEventLeavesInvalidCustomFieldsAlone(): void
    {
        $event = ['id' => 1, 'customFields' => 'not-json'];
        $result = $this->invoke($this->service, 'formatEvent', [$event]);

        $this->assertSame('not-json', $result['customFields']);
    }

    public function testFormatEventCoercesBooleanFlags(): void
    {
        $result = $this->invoke($this->service, 'formatEvent', [
            ['waitlistEnabled' => '1', 'isPublic' => '0'],
        ]);
        $this->assertTrue($result['waitlistEnabled']);
        $this->assertFalse($result['isPublic']);

        $result = $this->invoke($this->service, 'formatEvent', [
            ['waitlistEnabled' => '0', 'isPublic' => '1'],
        ]);
        $this->assertFalse($result['waitlistEnabled']);
        $this->assertTrue($result['isPublic']);
    }

    public function testFormatEventHandlesFalseyStrings(): void
    {
        $result = $this->invoke($this->service, 'formatEvent', [
            ['waitlistEnabled' => 'false', 'isPublic' => 'false'],
        ]);

        $this->assertFalse($result['waitlistEnabled']);
        $this->assertFalse($result['isPublic']);
    }

    public function testFormatEventLeavesMissingFlagsUnchanged(): void
    {
        $result = $this->invoke($this->service, 'formatEvent', [['id' => 5]]);

        $this->assertArrayNotHasKey('waitlistEnabled', $result);
        $this->assertArrayNotHasKey('isPublic', $result);
    }
}