<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Unit;

use LibreOffice\Util\FileUri;
use PHPUnit\Framework\TestCase;

final class FileUriTest extends TestCase
{
    public function testWindowsPathConvertsToFileUri(): void
    {
        $uri = FileUri::fromAbsolutePath('C:\\Temp\\My Docs\\profile', 'Windows');
        self::assertSame('file:///C:/Temp/My%20Docs/profile', $uri);
    }

    public function testLinuxPathConvertsToFileUri(): void
    {
        $uri = FileUri::fromAbsolutePath('/tmp/my docs/profile', 'Linux');
        self::assertSame('file:///tmp/my%20docs/profile', $uri);
    }
}
