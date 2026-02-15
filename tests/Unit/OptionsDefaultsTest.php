<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Unit;

use LibreOffice\Config\OptionsResolver;
use LibreOffice\Util\Platform;
use PHPUnit\Framework\TestCase;

final class OptionsDefaultsTest extends TestCase
{
    public function testWindowsDefaultsToNoProfileStrategy(): void
    {
        $options = OptionsResolver::resolve([], new Platform('Windows'));
        self::assertSame('none', $options['profile_strategy']);
    }

    public function testLinuxDefaultsToPerJobProfileStrategy(): void
    {
        $options = OptionsResolver::resolve([], new Platform('Linux'));
        self::assertSame('per_job', $options['profile_strategy']);
    }
}
