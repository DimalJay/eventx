<?php

namespace Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use Services\RegistrationService;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    private RegistrationService $service;

    protected function setUp(): void
    {
        $this->service = new RegistrationService();
    }

    #[DataProvider('formatRegistrationCases')]
    public function testFormatRegistrationDecodesCustomFields(array $input, $expectedCustomFields): void
    {
        $result = $this->invoke($this->service, 'formatRegistration', [$input]);

        if ($expectedCustomFields === 'decoded') {
            $this->assertSame(['meal' => 'veg'], $result['customFields']);
        } elseif ($expectedCustomFields === 'absent') {
            $this->assertArrayNotHasKey('customFields', $result);
        } else {
            $this->assertSame($expectedCustomFields, $result['customFields']);
        }
    }

    public static function formatRegistrationCases(): array
    {
        return [
            'json string decodes to array' => [
                ['id' => 1, 'customFields' => '{"meal":"veg"}'],
                'decoded',
            ],
            'already decoded array is untouched' => [
                ['id' => 2, 'customFields' => ['note' => 'hi']],
                ['note' => 'hi'],
            ],
            'invalid json stays as string' => [
                ['id' => 3, 'customFields' => 'not-json'],
                'not-json',
            ],
            'no custom fields untouched' => [
                ['id' => 4, 'status' => 'PENDING'],
                'absent',
            ],
            'json array decodes to empty array' => [
                ['id' => 5, 'customFields' => '[]'],
                [],
            ],
            'unicode content survives round trip' => [
                ['id' => 6, 'customFields' => '{"note":"héllo wörld"}'],
                ['note' => 'héllo wörld'],
            ],
            'nested already-decoded array untouched' => [
                ['id' => 7, 'customFields' => ['tags' => ['a', 'b'], 'n' => 1]],
                ['tags' => ['a', 'b'], 'n' => 1],
            ],
            'space only json is invalid' => [
                ['id' => 8, 'customFields' => '   '],
                '   ',
            ],
        ];
    }
}