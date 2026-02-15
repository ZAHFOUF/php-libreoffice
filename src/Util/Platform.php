<?php

declare(strict_types=1);

namespace LibreOffice\Util;

final class Platform
{
    public function __construct(private readonly ?string $osFamilyOverride = null)
    {
    }

    public function family(): string
    {
        return strtolower($this->osFamilyOverride ?? PHP_OS_FAMILY);
    }

    public function isWindows(): bool
    {
        return $this->family() === 'windows';
    }

    public function defaultProfileStrategy(): string
    {
        return $this->isWindows() ? 'none' : 'per_job';
    }

    /**
     * @return array<string, string>
     */
    public function defaultEnv(string $tempDir): array
    {
        if ($this->isWindows()) {
            return [];
        }

        return [
            'HOME' => $tempDir,
            'TMPDIR' => $tempDir,
        ];
    }
}
