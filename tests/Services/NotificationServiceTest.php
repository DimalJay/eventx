<?php

namespace Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Services\NotificationService;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    private function format(array $row): array
    {
        $method = new ReflectionMethod(NotificationService::class, 'format');
        $method->setAccessible(true);
        return $method->invoke(null, $row);
    }

    public function testFormatMapsCompleteRow(): void
    {
        $result = $this->format([
            'id' => 5,
            'title' => 'New registration',
            'message' => 'Jo registered',
            'userId' => 7,
            'status' => 'read',
            'type' => 'Registration',
            'createdAt' => '2026-09-21 10:00:00',
            'readAt' => '2026-09-21 10:05:00',
            'isRead' => '1',
            'extras' => '{"eventId":42}',
        ]);

        $this->assertSame(5, $result['id']);
        $this->assertSame('New registration', $result['title']);
        $this->assertSame(7, $result['userId']);
        $this->assertSame('read', $result['status']);
        $this->assertSame('Registration', $result['type']);
        $this->assertSame('2026-09-21 10:00:00', $result['createdAt']);
        $this->assertSame('2026-09-21 10:05:00', $result['readAt']);
        $this->assertTrue($result['isRead']);
        $this->assertSame(['eventId' => 42], $result['extras']);
    }

    #[DataProvider('sparseRows')]
    public function testFormatAppliesDefaultsForMissingFields(array $row, array $expected): void
    {
        $result = $this->format($row);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key]);
        }
    }

    public static function sparseRows(): array
    {
        return [
            'empty row' => [
                [],
                [
                    'id' => 0,
                    'title' => '',
                    'message' => '',
                    'userId' => 0,
                    'status' => 'unread',
                    'type' => 'General',
                    'createdAt' => null,
                    'readAt' => null,
                    'isRead' => false,
                    'extras' => null,
                ],
            ],
            'no extras' => [
                ['id' => 1, 'extras' => null, 'isRead' => '0'],
                ['id' => 1, 'isRead' => false, 'extras' => null],
            ],
            'empty extras string' => [
                ['id' => 2, 'extras' => '', 'isRead' => 1],
                ['id' => 2, 'isRead' => true, 'extras' => null],
            ],
        ];
    }

    public function testBuildTaskUpdateMessageForInProgressDoesNotNeedUserLookup(): void
    {
        $service = new NotificationService();

        $message = $this->invoke($service, 'buildTaskUpdateMessage', [
            ['title' => 'Setup stage', 'status' => 'IN_PROGRESS'],
            null,
            0,
        ]);

        $this->assertSame('"Setup stage" in progress', $message);
    }

    public function testBuildTaskUpdateMessageNormalisesStatusCase(): void
    {
        $service = new NotificationService();

        $message = $this->invoke($service, 'buildTaskUpdateMessage', [
            ['title' => 'Sound check', 'status' => 'in_progress'],
            'in_progress',
            0,
        ]);

        $this->assertSame('"Sound check" in progress', $message);
    }
}