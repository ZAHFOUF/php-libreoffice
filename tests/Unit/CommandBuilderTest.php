<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Unit;

use LibreOffice\Converter\CommandBuilder;
use PHPUnit\Framework\TestCase;

final class CommandBuilderTest extends TestCase
{
    public function testIncludesUserInstallationArgWhenProvided(): void
    {
        $builder = new CommandBuilder();
        $command = $builder->build(
            binary: 'soffice',
            inputPath: '/tmp/in/file.docx',
            targetFormat: 'pdf',
            outDir: '/tmp/out',
            userInstallationArg: '-env:UserInstallation=file:///tmp/profile'
        );

        self::assertContains('-env:UserInstallation=file:///tmp/profile', $command);
        self::assertSame('soffice', $command[0]);
    }

    public function testExcludesUserInstallationArgForNullProfile(): void
    {
        $builder = new CommandBuilder();
        $command = $builder->build(
            binary: 'soffice',
            inputPath: '/tmp/in/file.docx',
            targetFormat: 'pdf',
            outDir: '/tmp/out',
            userInstallationArg: null
        );

        self::assertNotContains('-env:UserInstallation=file:///tmp/profile', $command);
        self::assertContains('--headless', $command);
    }
}
