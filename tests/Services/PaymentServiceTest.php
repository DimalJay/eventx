<?php

namespace Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use Services\PaymentService;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->service = new PaymentService();
    }

    public function testGroupPaymentsByEventIsEmptyForNoRows(): void
    {
        $this->assertSame([], $this->invoke($this->service, 'groupPaymentsByEvent', [[]]));
    }

    public function testGroupPaymentsByEventGroupsAndOrdersByFirstSeen(): void
    {
        $rows = [
            $this->row(['eventId' => 3, 'eventTitle' => 'Alpha', 'id' => 10, 'amount' => 100.00, 'buyerFirstName' => 'John', 'buyerLastName' => 'Doe', 'buyerEmail' => 'john@x.com']),
            $this->row(['eventId' => 1, 'eventTitle' => 'Beta', 'id' => 5, 'amount' => 40.00]),
            $this->row(['eventId' => 3, 'eventTitle' => 'Alpha', 'id' => 11, 'amount' => 100.00]),
        ];

        $result = $this->invoke($this->service, 'groupPaymentsByEvent', [$rows]);

        $this->assertCount(2, $result);

        // Event 3 (first seen) comes first.
        $first = $result[0];
        $this->assertSame(3, $first['event']['id']);
        $this->assertSame('Alpha', $first['event']['title']);
        $this->assertSame(2, $first['paymentCount']);
        $this->assertEqualsWithDelta(200.00, $first['revenue'], 0.001);
        $this->assertEqualsWithDelta(10.00, $first['commission'], 0.001);
        $this->assertEqualsWithDelta(190.00, $first['payout'], 0.001);
        $this->assertCount(2, $first['payments']);
        $this->assertSame('John', $first['payments'][0]['buyer']['firstName']);
        $this->assertSame('john@x.com', $first['payments'][0]['buyer']['email']);
        $this->assertNull($first['payments'][1]['buyer']);

        // Event 1 grouped second with clean numbers.
        $second = $result[1];
        $this->assertSame(1, $second['event']['id']);
        $this->assertSame('Beta', $second['event']['title']);
        $this->assertSame(1, $second['paymentCount']);
        $this->assertEqualsWithDelta(40.00, $second['revenue'], 0.001);
        $this->assertEqualsWithDelta(2.00, $second['commission'], 0.001);
        $this->assertEqualsWithDelta(38.00, $second['payout'], 0.001);
    }

    public function testGroupPaymentsByEventExtractsBuyerOnlyWhenPresent(): void
    {
        $rows = [$this->row(['eventId' => 1, 'id' => 1, 'amount' => 10.00])];

        $result = $this->invoke($this->service, 'groupPaymentsByEvent', [$rows]);

        $this->assertNull($result[0]['payments'][0]['buyer']);
    }

    public function testGroupPaymentsByEventKeepsEventMetaAndAccumulatesDecimals(): void
    {
        $rows = [
            $this->row(['eventId' => 9, 'eventTitle' => 'Gala', 'eventStartDate' => '2026-05-01 19:00:00', 'coverImage' => 'gala.png', 'id' => 1, 'amount' => 10.25, 'buyerFirstName' => 'A', 'buyerLastName' => 'B', 'buyerEmail' => 'a@example.com']),
            $this->row(['eventId' => 9, 'eventTitle' => 'Gala', 'id' => 2, 'amount' => 10.25]),
            $this->row(['eventId' => 9, 'eventTitle' => 'Gala', 'id' => 3, 'amount' => 10.50]),
        ];

        $result = $this->invoke($this->service, 'groupPaymentsByEvent', [$rows]);
        $event = $result[0];

        $this->assertSame('Gala', $event['event']['title']);
        $this->assertSame('2026-05-01 19:00:00', $event['event']['startDate']);
        $this->assertSame('gala.png', $event['event']['coverImage']);
        $this->assertSame(3, $event['paymentCount']);
        // 10.25 + 10.25 + 10.50
        $this->assertEqualsWithDelta(31.00, $event['revenue'], 0.001);
        $this->assertEqualsWithDelta(1.55, $event['commission'], 0.001);
        $this->assertEqualsWithDelta(29.45, $event['payout'], 0.001);
    }

    // ---- frontendHost (private) ----

    protected function tearDown(): void
    {
        unset($_ENV['FRONTEND_HOST'], $_ENV['DOMAIN']);
        putenv('FRONTEND_HOST');
        putenv('DOMAIN');
    }

    public function testFrontendHostDefaultsToLocalhost(): void
    {
        $this->assertSame('localhost', $this->invoke($this->service, 'frontendHost', []));
    }

    public function testFrontendHostReadsEnv(): void
    {
        $_ENV['DOMAIN'] = 'example.com';
        $this->assertSame('example.com', $this->invoke($this->service, 'frontendHost', []));
    }

    public function testFrontendHostPrefersFrontendHostOverDomain(): void
    {
        $_ENV['FRONTEND_HOST'] = 'https://app.example.com';
        $_ENV['DOMAIN'] = 'example.com';
        $this->assertSame('https://app.example.com', $this->invoke($this->service, 'frontendHost', []));

        unset($_ENV['FRONTEND_HOST']);
        putenv('FRONTEND_HOST=app.example.com');
        $this->assertSame('app.example.com', $this->invoke($this->service, 'frontendHost', []));
    }

    public function testFrontendHostFallsBackWhenFrontendHostIsEmptyString(): void
    {
        $_ENV['FRONTEND_HOST'] = '';
        $_ENV['DOMAIN'] = 'example.com';
        $this->assertSame('example.com', $this->invoke($this->service, 'frontendHost', []));
    }

    public function testFrontendHostIgnoresFalseFromGetenv(): void
    {
        // getenv() returns false for an unset var; the chain must not
        // short-circuit on false (regression test against the false ?? x trap).
        putenv('FRONTEND_HOST');
        putenv('DOMAIN');
        $this->assertSame('localhost', $this->invoke($this->service, 'frontendHost', []));
    }

    private function row(array $data): array
    {
        return array_merge([
            'eventId' => 1,
            'eventTitle' => 'Event',
            'eventStartDate' => '2026-01-01 09:00:00',
            'coverImage' => null,
            'id' => 0,
            'amount' => 0.0,
            'paymentAt' => '2026-01-01 10:00:00',
        ], $data);
    }

    #[DataProvider('connectedValues')]
    public function testIsAccountConnected($value, bool $expected): void
    {
        $row = $value === 'absent' ? [] : ['isConnected' => $value];
        $this->assertSame($expected, $this->invoke($this->service, 'isAccountConnected', [$row]));
    }

    public static function connectedValues(): array
    {
        return [
            'string one' => ['1', true],
            'integer one' => [1, true],
            'boolean true' => [true, true],
            'string zero' => ['0', false],
            'integer zero' => [0, false],
            'boolean false' => [false, false],
            'absent key' => ['absent', false],
            'unexpected value' => ['yes', false],
        ];
    }
}