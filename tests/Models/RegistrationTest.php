<?php

namespace Tests\Models;

use DateTime;
use Models\Registration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function testConstructorStoresEventAndUser(): void
    {
        $registration = new Registration(7, 3);

        $this->assertSame(7, $registration->getEventId());
        $this->assertSame(3, $registration->getUserId());
    }

    public function testNewRegistrationIsPendingAndTimestampsSet(): void
    {
        $registration = new Registration(7, 3);

        $this->assertSame('PENDING', $this->prop($registration, 'status'));
        $this->assertInstanceOf(DateTime::class, $this->prop($registration, 'registeredAt'));
    }

    public function testSetInWaitlistChangesStatus(): void
    {
        $registration = new Registration(7, 3);
        $registration->setInWaitlist();

        $this->assertSame('WAITLIST', $this->prop($registration, 'status'));
    }

    #[DataProvider('customFieldInputs')]
    public function testEncodeCustomFields($input, ?string $expected): void
    {
        $this->assertSame($expected, Registration::encodeCustomFields($input));
    }

    #[DataProvider('customFieldInputs')]
    public function testConstructorUsesEncodedCustomFields($input, ?string $expected): void
    {
        $registration = new Registration(7, 3, $input);
        $this->assertSame($expected, $this->prop($registration, 'customFields'));
    }

    public static function customFieldInputs(): array
    {
        return [
            'null input' => [null, null],
            'empty string input' => ['', null],
            'associative array' => [['meal' => 'veg'], '{"meal":"veg"}'],
            'object input' => [(object) ['name' => 'Acme'], '{"name":"Acme"}'],
            'valid json string passes through' => ['{"foo":1}', '{"foo":1}'],
            'invalid json string becomes null' => ['not-json', null],
            'json scalar becomes null' => ['42', null],
        ];
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Registration::class, Registration::empty());
    }
}