<?php

declare(strict_types=1);

namespace LibreOffice\Util;

use LibreOffice\Exception\InvalidOptionException;

final class FileUri
{
    public static function fromAbsolutePath(string $absolutePath, ?string $osFamily = null): string
    {
        if (!Path::isAbsolute($absolutePath)) {
            throw new InvalidOptionException(sprintf('Expected absolute path for file URI conversion, got "%s".', $absolutePath));
        }

        $family = strtolower($osFamily ?? PHP_OS_FAMILY);
        $path = str_replace('\\', '/', $absolutePath);
        $segments = array_map('rawurlencode', explode('/', $path));
        $encoded = implode('/', $segments);
        $encoded = str_replace('%3A', ':', $encoded);

        if ($family === 'windows') {
            if (!str_starts_with($encoded, '/')) {
                $encoded = '/' . $encoded;
            }

            return 'file://' . $encoded;
        }

        if (!str_starts_with($encoded, '/')) {
            $encoded = '/' . ltrim($encoded, '/');
        }

        return 'file://' . $encoded;
    }
}
