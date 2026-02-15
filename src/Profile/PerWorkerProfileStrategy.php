<?php

declare(strict_types=1);

namespace LibreOffice\Profile;

final class PerWorkerProfileStrategy implements ProfileStrategyInterface
{
    public function __construct(private readonly string $workerId)
    {
    }

    public function prepare(string $tempDir, string $jobId): ProfileContext
    {
        $profileDir = $tempDir . DIRECTORY_SEPARATOR . 'lo-profiles' . DIRECTORY_SEPARATOR . 'worker-' . $this->workerId;
        if (!is_dir($profileDir) && !mkdir($profileDir, 0777, true) && !is_dir($profileDir)) {
            throw new \RuntimeException(sprintf('Unable to create profile directory: %s', $profileDir));
        }

        return new ProfileContext($profileDir, null, false);
    }
}
