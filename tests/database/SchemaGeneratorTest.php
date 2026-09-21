<?php

namespace Tests\database;

use database\SchemaGenerator;
use Models\Event;
use Models\Guest;
use Models\Ticket;
use Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SchemaGeneratorTest extends TestCase
{
    public function testCreateTableBuildsColumnDefinitions(): void
    {
        $sql = (new SchemaGenerator(User::class))->createTable();

        $this->assertStringStartsWith('CREATE TABLE IF NOT EXISTS `users`', $sql);
        $this->assertStringContainsString('`id` INT', $sql);
        $this->assertStringContainsString('AUTO_INCREMENT', $sql);
        $this->assertStringContainsString('PRIMARY KEY', $sql);
        $this->assertStringContainsString('`email` VARCHAR(50)', $sql);
        $this->assertStringContainsString('NOT NULL', $sql);
        $this->assertStringContainsString('UNIQUE', $sql);
        $this->assertStringContainsString('`lastName` VARCHAR(150)', $sql);
        $this->assertStringEndsWith('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;', $sql);
    }

    public function testCreateTableForVarcharLessColumn(): void
    {
        $sql = (new SchemaGenerator(Event::class))->createTable();

        $this->assertStringContainsString('`capacity` INT', $sql);
        $this->assertStringContainsString('`ticketPrice` INT', $sql);
        $this->assertStringContainsString('`title` VARCHAR(255)', $sql);
    }

    public function testSelectAll(): void
    {
        $this->assertSame('SELECT * FROM `users`;', (new SchemaGenerator(User::class))->selectAll());
        $this->assertSame('SELECT * FROM `events`;', (new SchemaGenerator(Event::class))->selectAll());
    }

    #[DataProvider('whereCases')]
    public function testWhere(string $class, array $conditions, string $expected): void
    {
        $this->assertSame($expected, (new SchemaGenerator($class))->where($conditions));
    }

    public static function whereCases(): array
    {
        return [
            'single condition' => [User::class, ['email' => 'a@b.com'], 'SELECT * FROM `users` WHERE `email` = :email;'],
            'multiple conditions' => [Guest::class, ['eventId' => 3, 'status' => 'invited'], 'SELECT * FROM `guests` WHERE `eventId` = :eventId AND `status` = :status;'],
            'numeric condition' => [Ticket::class, ['id' => 42], 'SELECT * FROM `tickets` WHERE `id` = :id;'],
        ];
    }

    public function testUpdateRecordUsesSeparateBindNamespaces(): void
    {
        $sql = (new SchemaGenerator(Ticket::class))->updateRecord(
            ['id' => 5],
            ['status' => 'GOING', 'ticketCode' => 'T-1']
        );

        $this->assertSame(
            'UPDATE `tickets` SET `status` = :set_status, `ticketCode` = :set_ticketCode WHERE `id` = :cond_id;',
            $sql
        );
    }

    #[DataProvider('deleteCases')]
    public function testDeleteRecord(string $class, array $conditions, string $expected): void
    {
        $this->assertSame($expected, (new SchemaGenerator($class))->deleteRecord($conditions));
    }

    public static function deleteCases(): array
    {
        return [
            'single condition' => [User::class, ['id' => 9], 'DELETE FROM `users` WHERE `id` = :id;'],
            'composite condition' => [Guest::class, ['eventId' => 3, 'email' => 'g@x.com'], 'DELETE FROM `guests` WHERE `eventId` = :eventId AND `email` = :email;'],
        ];
    }

    public function testInsertRecordUsesAllNonAutoIncrementColumnsInDeclarationOrder(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret', 'pic.png');
        $sql = (new SchemaGenerator(User::class))->insertRecord($user);

        $this->assertMatchesRegularExpression(
            '/INSERT INTO `users` \((.*?)\) VALUES \((.*?)\);/s',
            $sql,
            'insert statement shape'
        );
        preg_match('/INSERT INTO `users` \((.*?)\) VALUES \((.*?)\);/s', $sql, $m);
        $columns = array_map('trim', explode(',', $m[1]));
        $placeholders = array_map('trim', explode(',', $m[2]));

        $expectedColumns = [
            '`email`', '`firstName`', '`lastName`', '`password`', '`loginType`',
            '`isVerified`', '`profilePicture`', '`accountStatus`', '`createdAt`',
            '`phoneNumber`', '`updatedAt`', '`lastLogin`', '`verificationToken`',
            '`verificationTokenExpires`', '`resetToken`', '`resetTokenExpires`',
        ];

        $this->assertSame($expectedColumns, $columns, 'auto-increment id skipped, remaining in declaration order');
        $this->assertSame(
            array_map(fn($c) => ':' . trim($c, '`'), $columns),
            $placeholders
        );
        $this->assertStringNotContainsString('`id`', $sql);
    }

    public function testInsertRecordForTicket(): void
    {
        $ticket = new Ticket(10, 20, 'TICKET-X', 30, 40);
        $sql = (new SchemaGenerator(Ticket::class))->insertRecord($ticket);

        $this->assertStringStartsWith('INSERT INTO `tickets`', $sql);
        $this->assertStringContainsString('`eventId`', $sql);
        $this->assertStringContainsString('`ticketCode`', $sql);
        $this->assertStringNotContainsString('`id`', $sql);
        $this->assertStringContainsString(':eventId', $sql);
    }

    public function testConstructorThrowsWhenTableAttributeMissing(): void
    {
        $anon = new class {
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class is missing the #[Table] attribute.');
        new SchemaGenerator(get_class($anon));
    }

    public function testConstructorReadsTableNameFromAttribute(): void
    {
        // Round-trips the class-specific table name via generated SQL.
        $this->assertStringStartsWith('SELECT * FROM `Registrations`', (new SchemaGenerator(\Models\Registration::class))->selectAll());
        $this->assertStringStartsWith('SELECT * FROM `Payments`', (new SchemaGenerator(\Models\Payment::class))->selectAll());
        $this->assertStringStartsWith('SELECT * FROM `checkin`', (new SchemaGenerator(\Models\Checkin::class))->selectAll());
        $this->assertStringStartsWith('SELECT * FROM `feedbacks`', (new SchemaGenerator(\Models\Feedback::class))->selectAll());
    }
}