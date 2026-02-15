<?php

declare(strict_types=1);

namespace LibreOffice\Util;

use Symfony\Component\VarExporter\VarExporter;

final class File
{
    public static function savePhp(string $path, array $data): void
    {
        file_put_contents($path, '<?php return ' . VarExporter::export($data) . ';');
    }

    public static function loadPhp(string $path): array
    {
        return require $path;
    }
}