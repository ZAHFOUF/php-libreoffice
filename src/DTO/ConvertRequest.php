<?php

declare(strict_types=1);

namespace LibreOffice\DTO;

final class ConvertRequest
{
    /**
     * @param array<string, mixed> $overrides
     */
    public function __construct(
        public readonly string $inputPath,
        public readonly string $targetFormat,
        public readonly array $overrides = []
    ) {
    }
}
