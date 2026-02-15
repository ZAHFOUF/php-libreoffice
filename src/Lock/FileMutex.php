<?php

declare(strict_types=1);

namespace LibreOffice\Lock;

use RuntimeException;

final class FileMutex implements MutexInterface
{
    /** @var resource|null */
    private $handle = null;

    public function __construct(private readonly string $lockFile)
    {
    }

    public function acquire(): void
    {
        $dir = dirname($this->lockFile);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Unable to create lock directory: %s', $dir));
        }

        $handle = fopen($this->lockFile, 'c+');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Unable to open lock file: %s', $this->lockFile));
        }

        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException(sprintf('Unable to lock file: %s', $this->lockFile));
        }

        $this->handle = $handle;
    }

    public function release(): void
    {
        if ($this->handle === null) {
            return;
        }

        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->handle = null;
    }
}
