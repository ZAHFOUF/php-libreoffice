<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Integration;

use LibreOffice\LibreOffice;
use PHPUnit\Framework\TestCase;

final class ConversionIntegrationTest extends TestCase
{
    public function testDocxToPdfConversion(): void
    {
        $fixture = __DIR__ . '/templates/test.docx';
        if (!is_file($fixture)) {
            var_dump($fixture);
            self::markTestSkipped('Fixture not found: tests/templates/test.docx');
        }

        $outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lo-integration-out';
        @mkdir($outDir, 0777, true);

        $result = LibreOffice::make()->convert($fixture)->to('pdf', ['output_dir' => $outDir]);

        var_dump($result->outputPath);
        self::assertFileExists($result->outputPath);
        self::assertStringEndsWith('.pdf', $result->outputPath);
    }
}
