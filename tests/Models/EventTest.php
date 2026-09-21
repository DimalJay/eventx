<?php

namespace Tests\Models;

use DateTime;
use Models\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventTest extends TestCase
{
    private function makeEvent(array $overrides = []): Event
    {
        $defaults = [
            'Tech Conf', 'online', 'A big conf', '2026-12-01 09:00:00', '2026-12-03 18:00:00',
            'Colombo', 42, 'cover.jpg', true, 100, 25.50, '2026-11-20 23:59:59', null,
            false, 'Technology',
        ];
        foreach ($overrides as $key => $value) {
            $defaults[$key] = $value;
        }
        return new Event(...array_values($defaults));
    }

    public function testConstructorStoresCoreFields(): void
    {
        $event = $this->makeEvent();

        $this->assertSame('Tech Conf', $this->prop($event, 'title'));
        $this->assertSame('online', $this->prop($event, 'eventType'));
        $this->assertSame('Technology', $this->prop($event, 'category'));
        $this->assertSame('Colombo', $this->prop($event, 'location'));
        $this->assertSame(42, $this->prop($event, 'organizerId'));
        $this->assertSame('cover.jpg', $this->prop($event, 'coverImage'));
        $this->assertSame(25.50, $this->prop($event, 'ticketPrice'));
        $this->assertSame(100, $this->prop($event, 'capacity'));
    }

    public function testConstructorParsesDatetimes(): void
    {
        $event = $this->makeEvent();

        $this->assertInstanceOf(DateTime::class, $this->prop($event, 'startDate'));
        $this->assertInstanceOf(DateTime::class, $this->prop($event, 'endDate'));
        $this->assertInstanceOf(DateTime::class, $this->prop($event, 'regDeadline'));
        $this->assertSame('2026-12-01 09:00:00', $this->prop($event, 'startDate')->format('Y-m-d H:i:s'));
    }

    public function testDefaults(): void
    {
        $event = $this->makeEvent([13 => true]);

        $this->assertTrue($this->prop($event, 'isPublic'));
        $this->assertTrue($this->prop($event, 'waitlistEnabled'));
        $this->assertSame('upcoming', $this->prop($event, 'status'));
    }

    public function testGetCapacity(): void
    {
        $this->assertSame(100, $this->makeEvent()->getCapacity());
        $this->assertSame(0, $this->makeEvent([9 => 0])->getCapacity());
    }

    #[DataProvider('customFieldInputs')]
    public function testEncodeCustomFields($input, ?string $expected): void
    {
        $this->assertSame($expected, Event::encodeCustomFields($input));
    }

    public static function customFieldInputs(): array
    {
        return [
            'null input' => [null, null],
            'empty string input' => ['', null],
            'associative array' => [['meal' => 'veg'], '{"meal":"veg"}'],
            'nested array' => [['tags' => ['a', 'b']], '{"tags":["a","b"]}'],
            'object input' => [(object) ['name' => 'Acme'], '{"name":"Acme"}'],
            'valid json string passes through' => ['{"foo":1}', '{"foo":1}'],
            'invalid json string becomes null' => ['not-json', null],
            'json scalar becomes null' => ['42', null],
        ];
    }
}