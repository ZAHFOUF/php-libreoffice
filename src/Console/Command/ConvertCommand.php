<?php

declare(strict_types=1);

namespace LibreOffice\Console\Command;

use LibreOffice\LibreOffice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ConvertCommand extends Command
{
    protected static $defaultName = 'lo:convert';
    protected static $defaultDescription = 'Convert Office document to another format using LibreOffice CLI.';

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'Input document path')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Target format', 'pdf')
            ->addOption('out', null, InputOption::VALUE_OPTIONAL, 'Output directory')
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'Timeout in seconds', '120')
            ->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'Profile strategy: none|per_job|per_worker|shared_mutex')
            ->addOption('temp-dir', null, InputOption::VALUE_OPTIONAL, 'Temporary directory')
            ->addOption('binary', null, InputOption::VALUE_OPTIONAL, 'LibreOffice binary', 'soffice')
            ->addOption('worker-id', null, InputOption::VALUE_OPTIONAL, 'Worker id for per_worker strategy')
            ->addOption('keep-temp', null, InputOption::VALUE_NONE, 'Keep temp files on failure');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = [
            'binary' => (string) $input->getOption('binary'),
            'timeout' => (int) $input->getOption('timeout'),
            'cleanup_policy' => $input->getOption('keep-temp') ? 'keep_on_failure' : 'always',
        ];

        if ($input->getOption('profile') !== null) {
            $options['profile_strategy'] = (string) $input->getOption('profile');
        }
        if ($input->getOption('temp-dir') !== null) {
            $options['temp_dir'] = (string) $input->getOption('temp-dir');
        }
        if ($input->getOption('worker-id') !== null) {
            $options['worker_id'] = (string) $input->getOption('worker-id');
        }

        $libreOffice = new LibreOffice($options);
        $overrides = [];
        if ($input->getOption('out') !== null) {
            $overrides['output_dir'] = (string) $input->getOption('out');
        }

        try {
            $result = $libreOffice->convert((string) $input->getArgument('input'))->to((string) $input->getOption('to'), $overrides);

            $output->writeln(sprintf('<info>Done in %d ms</info>', $result->durationMs));
            $output->writeln(sprintf('<info>Output:</info> %s', $result->outputPath));

            if ($output->isVerbose()) {
                $output->writeln('<comment>Command:</comment> ' . implode(' ', $result->command));
                if ($result->stderr !== '') {
                    $output->writeln('<comment>stderr:</comment>');
                    $output->writeln($result->stderr);
                }
            }
            if ($output->isVeryVerbose() && $result->stdout !== '') {
                $output->writeln('<comment>stdout:</comment>');
                $output->writeln($result->stdout);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Conversion failed.</error>');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
