<?php

declare(strict_types=1);

namespace LibreOffice;

use LibreOffice\DTO\ConvertRequest;
use LibreOffice\DTO\ConvertResult;

final class PendingConversion
{
    public function __construct(
        private readonly LibreOffice $libreOffice,
        private readonly string $inputPath
    ) {
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function to(string $targetFormat, array $overrides = []): ConvertResult
    {
        return $this->libreOffice->run(new ConvertRequest($this->inputPath, $targetFormat, $overrides));
    }
}
