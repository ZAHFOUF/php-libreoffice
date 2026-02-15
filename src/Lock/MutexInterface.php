<?php

declare(strict_types=1);

namespace LibreOffice\Lock;

interface MutexInterface
{
    public function acquire(): void;

    public function release(): void;
}
