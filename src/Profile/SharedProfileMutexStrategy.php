<?php

declare(strict_types=1);

namespace LibreOffice\Profile;

use LibreOffice\Lock\FileMutex;

final class SharedProfileMutexStrategy implements ProfileStrategyInterface
{
    public function prepare(string $tempDir, string $jobId): ProfileContext
    {
        $base = $tempDir . DIRECTORY_SEPARATOR . 'lo-profiles';
        $profileDir = $base . DIRECTORY_SEPARATOR . 'shared';
        $lockFile = $base . DIRECTORY_SEPARATOR . 'shared.lock';

        if (!is_dir($profileDir) && !mkdir($profileDir, 0777, true) && !is_dir($profileDir)) {
            throw new \RuntimeException(sprintf('Unable to create shared profile directory: %s', $profileDir));
        }

        return new ProfileContext($profileDir, new FileMutex($lockFile), false);
    }
}
