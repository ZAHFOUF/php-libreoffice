<?php

declare(strict_types=1);

namespace LibreOffice\Diagnostics;

use LibreOffice\Exception\BinaryNotFoundException;
use LibreOffice\Exception\ConversionFailedException;
use LibreOffice\Exception\LibreOfficeException;
use LibreOffice\Exception\ProfileInitException;

final class ErrorClassifier
{
    /**
     * @param array<int, string> $command
     */
    public function fromFailure(
        array $command,
        string $stdout,
        string $stderr,
        ?int $exitCode,
        ?\Throwable $previous = null
    ): LibreOfficeException {
        $haystack = strtolower($stderr . "\n" . $stdout . "\n" . ($previous?->getMessage() ?? ''));

        if (
            str_contains($haystack, 'user installation could not be completed')
            || str_contains($haystack, 'dconf-critical')
        ) {
            return new ProfileInitException(
                'LibreOffice profile initialization failed. Ensure temp_dir is writable and use profile_strategy=per_job or shared_mutex on Linux.',
                $command,
                $stdout,
                $stderr,
                $exitCode,
                $previous
            );
        }

        if (
            str_contains($haystack, 'not recognized as an internal or external command')
            || str_contains($haystack, 'command not found')
            || str_contains($haystack, 'no such file or directory')
            || str_contains($haystack, 'executable not found')
        ) {
            return new BinaryNotFoundException(
                'LibreOffice binary was not found. Install LibreOffice and ensure "soffice" is in PATH, or provide the "binary" option.',
                $command,
                $stdout,
                $stderr,
                $exitCode,
                $previous
            );
        }

        return new ConversionFailedException(
            'LibreOffice conversion failed. Enable verbose logging to inspect stderr.',
            $command,
            $stdout,
            $stderr,
            $exitCode,
            $previous
        );
    }
}
