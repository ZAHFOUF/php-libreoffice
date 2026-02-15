<?php

declare(strict_types=1);

namespace LibreOffice\Console\Command;

use LibreOffice\Config\OptionsResolver;
use LibreOffice\Util\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'lo:probe',
    description: 'Check LibreOffice (soffice) availability and basic environment.'
)]
class ProbeCommand extends Command
{
   

    protected function configure(): void
    {
        $this
            ->addOption('binary', null, InputOption::VALUE_REQUIRED, 'Path to soffice binary', 'soffice')
            ->addOption('temp-dir', null, InputOption::VALUE_REQUIRED, 'Temporary directory', sys_get_temp_dir());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $binary = (string) $input->getOption('binary');
        $tempDir = (string) $input->getOption('temp-dir');
        $platform = new Platform();

        $output->writeln(sprintf('<info>Binary:</info> %s', $binary));
        $output->writeln(sprintf('<info>Temp dir:</info> %s', $tempDir));

        $tempWritable = is_dir($tempDir) ? is_writable($tempDir) : @mkdir($tempDir, 0777, true);
        if (!$tempWritable) {
            $output->writeln('<error>Temp directory is not writable.</error>');
            return Command::FAILURE;
        }

        try {
            $process = new Process([$binary, '--version'], null, $platform->defaultEnv($tempDir), null, 20.0);
            $process->mustRun();
            $output->writeln('<info>LibreOffice detected.</info>');
            $output->writeln(trim($process->getOutput() . "\n" . $process->getErrorOutput()));
            OptionsResolver::saveGlobalOptions([
                'binary' => $binary,
                'temp_dir' => $tempDir,
            ]);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>LibreOffice binary not found or not executable.</error>');
            $output->writeln('Install LibreOffice and ensure "soffice" is in PATH, or pass --binary=/path/to/soffice.');
            if ($output->isVerbose()) {
                $output->writeln($e->getMessage());
            }
            return Command::FAILURE;
        }
    }
}
