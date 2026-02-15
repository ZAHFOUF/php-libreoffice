<?php

declare(strict_types=1);

namespace LibreOffice\Console\Command;

use LibreOffice\Util\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'lo:install',
    description: 'Attempt LibreOffice install on supported platforms.'
)]
final class InstallCommand extends Command
{
   
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $platform = new Platform();

        if ($platform->isWindows()) {
            $output->writeln('<comment>Automatic install is not supported on Windows.</comment>');
            $output->writeln('Download LibreOffice from: https://www.libreoffice.org/download/download-libreoffice/');
            return Command::FAILURE;
        }

        if (!$this->isDebianFamily()) {
            $output->writeln('<comment>Automatic install supported only on Ubuntu/Debian.</comment>');
            $output->writeln('Please install LibreOffice using your distribution package manager.');
            return Command::FAILURE;
        }

        $output->writeln('<info>Running: sudo apt-get update</info>');
        $update = new Process(['sudo', 'apt-get', 'update']);
        $update->setTimeout(600.0);
        $update->run(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if (!$update->isSuccessful()) {
            $output->writeln('<error>apt-get update failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Running: sudo apt-get install -y libreoffice</info>');
        $install = new Process(['sudo', 'apt-get', 'install', '-y', 'libreoffice']);
        $install->setTimeout(1200.0);
        $install->run(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        if (!$install->isSuccessful()) {
            $output->writeln('<error>LibreOffice installation failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>LibreOffice installed successfully.</info>');
        return Command::SUCCESS;
    }

    private function isDebianFamily(): bool
    {
        $osRelease = '/etc/os-release';
        if (!is_file($osRelease)) {
            return false;
        }

        $content = strtolower((string) file_get_contents($osRelease));
        return str_contains($content, 'id=ubuntu')
            || str_contains($content, 'id=debian')
            || str_contains($content, 'id_like=debian');
    }
}
