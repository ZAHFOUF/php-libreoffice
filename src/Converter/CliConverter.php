<?php

declare(strict_types=1);

namespace LibreOffice\Converter;

use LibreOffice\DTO\ConvertRequest;
use LibreOffice\DTO\ConvertResult;
use LibreOffice\Diagnostics\ErrorClassifier;
use LibreOffice\Exception\OutputNotGeneratedException;
use LibreOffice\Profile\ProfileStrategyInterface;
use LibreOffice\Temp\TempManager;
use LibreOffice\Util\JobId;
use LibreOffice\Util\Path;
use LibreOffice\Util\Platform;
use Symfony\Component\Process\Process;

final class CliConverter
{
    public function __construct(
        private readonly Platform $platform,
        private readonly ProfileStrategyInterface $profileStrategy,
        private readonly CommandBuilder $commandBuilder,
        private readonly TempManager $tempManager,
        private readonly ErrorClassifier $errorClassifier
    ) {
    }

    /**
     * @param array{
     *     binary:string,
     *     timeout:int,
     *     profile_strategy:string,
     *     temp_dir:string,
     *     cleanup_policy:string,
     *     worker_id:?string
     * } $options
     */
    public function convert(ConvertRequest $request, array $options): ConvertResult
    {
        $jobId = JobId::generate();
        $inputAbsolute = Path::absolute($request->inputPath);

        if (!is_file($inputAbsolute)) {
            throw new \InvalidArgumentException(sprintf('Input file does not exist: %s', $inputAbsolute));
        }

        $dirs = $this->tempManager->createJobDirectories($options['temp_dir'], $jobId);
        $profile = $this->profileStrategy->prepare($options['temp_dir'], $jobId);
        $mutex = $profile->mutex;

        $copiedInput = $dirs['inDir'] . DIRECTORY_SEPARATOR . Path::baseName($inputAbsolute);
        if (!copy($inputAbsolute, $copiedInput)) {
            throw new \RuntimeException(sprintf('Failed to copy input file into temp dir: %s', $copiedInput));
        }

        $command = $this->commandBuilder->build(
            $options['binary'],
            $copiedInput,
            $request->targetFormat,
            $dirs['outDir'],
            $profile->userInstallationArgument()
        );

        $start = microtime(true);
        $stdout = '';
        $stderr = '';
        $failed = false;
        $exitCode = null;

        try {
            if ($mutex !== null) {
                $mutex->acquire();
            }

            $process = new Process($command, null, $this->platform->defaultEnv($options['temp_dir']), null, (float) $options['timeout']);
            $process->run();
            $stdout = $process->getOutput();
            $stderr = $process->getErrorOutput();
            $exitCode = $process->getExitCode();

            if (!$process->isSuccessful()) {
                $failed = true;
                throw $this->errorClassifier->fromFailure($command, $stdout, $stderr, $exitCode);
            }

            $expected = $dirs['outDir'] . DIRECTORY_SEPARATOR . pathinfo($copiedInput, PATHINFO_FILENAME) . '.' . strtolower($request->targetFormat);
            if (!is_file($expected)) {
                $failed = true;
                throw new OutputNotGeneratedException(
                    'LibreOffice process succeeded but output file was not generated.',
                    $command,
                    $stdout,
                    $stderr,
                    $exitCode
                );
            }

            $finalOutput = $this->finalizeOutput($expected, $request->overrides);
            $duration = (int) round((microtime(true) - $start) * 1000);

            return new ConvertResult(
                outputPath: Path::absolute($finalOutput),
                inputPath: $inputAbsolute,
                durationMs: $duration,
                stdout: $stdout,
                stderr: $stderr,
                command: $command
            );
        } catch (\Throwable $e) {
            if (!$failed) {
                throw $this->errorClassifier->fromFailure($command, $stdout, $stderr, $exitCode, $e);
            }
            throw $e;
        } finally {
            if ($mutex !== null) {
                $mutex->release();
            }
            $this->cleanup($dirs['jobRoot'], $profile->profileDir, $profile->cleanupProfileDir, $options['cleanup_policy'], $failed);
        }
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function finalizeOutput(string $tempOutput, array $overrides): string
    {
        $targetDir = isset($overrides['output_dir']) ? Path::absolute((string) $overrides['output_dir']) : dirname($tempOutput);

        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new \RuntimeException(sprintf('Unable to create output directory: %s', $targetDir));
        }

        $finalOutput = $targetDir . DIRECTORY_SEPARATOR . basename($tempOutput);
        if (realpath(dirname($tempOutput)) === realpath($targetDir)) {
            return $tempOutput;
        }

        if (!copy($tempOutput, $finalOutput)) {
            throw new \RuntimeException(sprintf('Unable to copy output file to: %s', $finalOutput));
        }

        return $finalOutput;
    }

    private function cleanup(string $jobRoot, ?string $profileDir, bool $cleanupProfileDir, string $cleanupPolicy, bool $failed): void
    {
        if ($cleanupPolicy === 'keep_on_failure' && $failed) {
            return;
        }

        $this->tempManager->cleanupDir($jobRoot);
        if ($cleanupProfileDir && $profileDir !== null) {
            $this->tempManager->cleanupDir($profileDir);
        }
    }
}
