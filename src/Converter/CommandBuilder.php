<?php

declare(strict_types=1);

namespace LibreOffice\Converter;

final class CommandBuilder
{
    /**
     * @return array<int, string>
     */
    public function build(
        string $binary,
        string $inputPath,
        string $targetFormat,
        string $outDir,
        ?string $userInstallationArg
    ): array {
        $command = [$binary];

        if ($userInstallationArg !== null) {
            $command[] = $userInstallationArg;
        }

        $command[] = '--headless';
        $command[] = '--convert-to';
        $command[] = $targetFormat;
        $command[] = $inputPath;
        $command[] = '--outdir';
        $command[] = $outDir;

        return $command;
    }
}
