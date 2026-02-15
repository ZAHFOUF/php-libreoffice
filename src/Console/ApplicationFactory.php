<?php

declare(strict_types=1);

namespace LibreOffice\Console;

use LibreOffice\Console\Command\ConvertCommand;
use LibreOffice\Console\Command\InstallCommand;
use LibreOffice\Console\Command\ProbeCommand;
use Symfony\Component\Console\Application;

final class ApplicationFactory
{
    public static function create(): Application
    {
        $app = new Application('php-libreoffice', '1.0.0');
        $app->add(new ProbeCommand());
        $app->add(new InstallCommand());
        $app->add(new ConvertCommand());

        return $app;
    }
}
