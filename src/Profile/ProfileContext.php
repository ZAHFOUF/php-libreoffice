<?php

declare(strict_types=1);

namespace LibreOffice\Profile;

use LibreOffice\Lock\MutexInterface;
use LibreOffice\Util\FileUri;

final class ProfileContext
{
    public function __construct(
        public readonly ?string $profileDir,
        public readonly ?MutexInterface $mutex = null,
        public readonly bool $cleanupProfileDir = false
    ) {
    }

    public function userInstallationArgument(): ?string
    {
        if ($this->profileDir === null) {
            return null;
        }

        return '-env:UserInstallation=' . FileUri::fromAbsolutePath($this->profileDir);
    }
}
