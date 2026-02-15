<?php

declare(strict_types=1);

namespace LibreOffice\Profile;

interface ProfileStrategyInterface
{
    public function prepare(string $tempDir, string $jobId): ProfileContext;
}
