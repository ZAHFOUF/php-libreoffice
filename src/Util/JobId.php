<?php

declare(strict_types=1);

namespace LibreOffice\Util;

final class JobId
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(8));
    }
}
