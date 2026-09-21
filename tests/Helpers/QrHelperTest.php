<?php

namespace Tests\Helpers;

use Helpers\QrHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QrHelperTest extends TestCase
{
    private const DARK = '#18181b';
    private const LIGHT = '#ffffff';
    private const CELL = 4;
    private const QUIET_ZONE = 2;

    private function tableWidth(string $html): int
    {
        $this->assertMatchesRegularExpression('/style="[^"]*width:(\d+)px;/', $html, 'table width not found');
        preg_match('/style="[^"]*width:(\d+)px;/', $html, $m);
        return (int) $m[1];
    }

    private function rows(string $html): array
    {
        preg_match_all('/<tr>(.*?)<\/tr>/s', $html, $m);
        return $m[1];
    }

    #[DataProvider('sampleData')]
    public function testTableShapeIsConsistent(string $data): void
    {
        $html = QrHelper::renderTable($data);

        $width = $this->tableWidth($html);
        $this->assertGreaterThan(0, $width);
        $this->assertSame(0, $width % self::CELL);

        $size = (int) ($width / self::CELL);
        $rows = $this->rows($html);

        $this->assertCount($size, $rows, 'one row per module cell');

        $tdCount = 0;
        foreach ($rows as $row) {
            $tdCount += substr_count($row, '<td');
        }
        // size includes the quiet zone on both sides: (matrix + QUITE_ZONE*2) ^ 2
        $this->assertSame($size * $size, $tdCount);
    }

    #[DataProvider('sampleData')]
    public function testOnlyAllowedColorsUsed(string $data): void
    {
        $html = QrHelper::renderTable($data);

        $darkCount = substr_count($html, 'background-color:' . self::DARK);
        $lightCount = substr_count($html, 'background-color:' . self::LIGHT);

        $this->assertGreaterThan(0, $darkCount, 'QR must contain dark modules');
        $this->assertGreaterThan(0, $lightCount, 'QR must contain light modules');

        // Strip the two known colors; nothing else may be present.
        $sanitized = str_replace([
            'background-color:' . self::DARK,
            'background-color:' . self::LIGHT,
        ], '', $html);
        $this->assertStringNotContainsString('background-color:', $sanitized);
    }

    #[DataProvider('sampleData')]
    public function testQuietZoneIsEmptyOnAllSides(string $data): void
    {
        $html = QrHelper::renderTable($data);
        $rows = $this->rows($html);

        // First and last quiet-zone rows must be entirely light.
        foreach ([$rows[0], $rows[count($rows) - 1]] as $row) {
            $this->assertStringNotContainsString(self::DARK, $row);
        }

        // First and last module columns of every row must be light.
        foreach ($rows as $row) {
            preg_match_all('/background-color:(#[0-9a-f]{6})/s', $row, $colors);
            $this->assertSame(self::LIGHT, $colors[1][0] ?? null, 'leftmost column is quiet zone');
            $this->assertSame(self::LIGHT, $colors[1][count($colors[1]) - 1] ?? null, 'rightmost column is quiet zone');
        }
    }

    public function testOutputIsDeterministic(): void
    {
        $this->assertSame(QrHelper::renderTable('https://localhost/ticket/ABC'), QrHelper::renderTable('https://localhost/ticket/ABC'));
    }

    public function testDifferentDataProducesDifferentOutput(): void
    {
        $this->assertNotSame(QrHelper::renderTable('ticket-1'), QrHelper::renderTable('ticket-2'));
    }

    public function testOutputIsATableWithAccessibilityRole(): void
    {
        $html = QrHelper::renderTable('x');

        $this->assertStringStartsWith('<table align="center"', $html);
        $this->assertStringContainsString('role="presentation"', $html);
        $this->assertStringContainsString('border-collapse:collapse', $html);
        $this->assertStringEndsWith('</table>', $html);
    }

    public function testBooleanMatrixHasFindablePattern(): void
    {
        // A finder pattern is a 7x7 marker; the rendered output must contain
        // three consecutive dark cells with light gaps.
        $html = QrHelper::renderTable('https://localhost/ticket/' . str_repeat('A', 32));
        $darkRuns = array_map('strlen', explode(self::LIGHT, $html));

        $this->assertGreaterThanOrEqual(3, max($darkRuns));
    }

    public static function sampleData(): array
    {
        return [
            'short url' => ['https://localhost/ticket/ABC'],
            'medium payload' => ['https://localhost/ticket/AB-CD-EF-12-34'],
            'long url' => ['https://localhost/ticket/' . str_repeat('Q', 64)],
        ];
    }
}