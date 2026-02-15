<?php

declare(strict_types=1);

namespace LibreOffice\Exception;

use RuntimeException;

class LibreOfficeException extends RuntimeException
{
    /**
     * @param array<int, string> $command
     */
    public function __construct(
        string $message,
        public readonly array $command = [],
        public readonly string $stdout = '',
        public readonly string $stderr = '',
        public readonly ?int $exitCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
