<?php

declare(strict_types=1);

namespace LibreOffice\Profile;

final class NullProfileStrategy implements ProfileStrategyInterface
{
    public function prepare(string $tempDir, string $jobId): ProfileContext
    {
        return new ProfileContext(null);
    }
}
