<?php

declare(strict_types=1);

namespace LibreOffice\DTO;

final class ConvertResult
{
    /**
     * @param array<int, string> $command
     */
    public function __construct(
        public readonly string $outputPath,
        public readonly string $inputPath,
        public readonly int $durationMs,
        public readonly string $stdout,
        public readonly string $stderr,
        public readonly array $command
    ) {
    }
}
