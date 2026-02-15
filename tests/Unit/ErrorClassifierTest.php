<?php

declare(strict_types=1);

namespace LibreOffice\Tests\Unit;

use LibreOffice\Diagnostics\ErrorClassifier;
use LibreOffice\Exception\BinaryNotFoundException;
use LibreOffice\Exception\ConversionFailedException;
use LibreOffice\Exception\ProfileInitException;
use PHPUnit\Framework\TestCase;

final class ErrorClassifierTest extends TestCase
{
    public function testMapsProfileErrorToProfileInitException(): void
    {
        $classifier = new ErrorClassifier();
        $exception = $classifier->fromFailure(
            ['soffice'],
            '',
            'User installation could not be completed',
            1
        );

        self::assertInstanceOf(ProfileInitException::class, $exception);
    }

    public function testMapsBinaryErrorToBinaryNotFoundException(): void
    {
        $classifier = new ErrorClassifier();
        $exception = $classifier->fromFailure(
            ['soffice'],
            '',
            'soffice: command not found',
            127
        );

        self::assertInstanceOf(BinaryNotFoundException::class, $exception);
    }

    public function testMapsUnknownErrorToConversionFailedException(): void
    {
        $classifier = new ErrorClassifier();
        $exception = $classifier->fromFailure(
            ['soffice'],
            '',
            'Unknown conversion error',
            1
        );

        self::assertInstanceOf(ConversionFailedException::class, $exception);
    }
}
