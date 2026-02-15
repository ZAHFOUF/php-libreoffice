<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Integration;

use LibreOffice\LibreOffice;
use PHPUnit\Framework\TestCase;

final class ConversionIntegrationTest extends TestCase
{
    public function testDocxToPdfConversion(): void
    {
        $run = getenv('RUN_LO_INTEGRATION') === '1';
        $loBin = getenv('LO_BIN');

        if (!$run && $loBin === false) {
            self::markTestSkipped('Set RUN_LO_INTEGRATION=1 or LO_BIN to run integration test.');
        }

        $binary = $loBin !== false ? $loBin : 'soffice';
        $fixture = __DIR__ . '/../Fixtures/sample.docx';
        if (!is_file($fixture)) {
            self::markTestSkipped('Fixture not found: tests/Fixtures/sample.docx');
        }

        $outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lo-integration-out';
        @mkdir($outDir, 0777, true);

        $result = LibreOffice::make([
            'binary' => $binary,
            'cleanup_policy' => 'keep_on_failure',
        ])->convert($fixture)->to('pdf', ['output_dir' => $outDir]);

        self::assertFileExists($result->outputPath);
        self::assertStringEndsWith('.pdf', $result->outputPath);
    }
}
