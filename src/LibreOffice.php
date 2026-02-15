<?php

declare(strict_types=1);

namespace LibreOffice;

use LibreOffice\Config\OptionsResolver;
use LibreOffice\Converter\CliConverter;
use LibreOffice\Converter\CommandBuilder;
use LibreOffice\DTO\ConvertRequest;
use LibreOffice\DTO\ConvertResult;
use LibreOffice\Diagnostics\ErrorClassifier;
use LibreOffice\Profile\NullProfileStrategy;
use LibreOffice\Profile\PerJobProfileStrategy;
use LibreOffice\Profile\PerWorkerProfileStrategy;
use LibreOffice\Profile\ProfileStrategyInterface;
use LibreOffice\Profile\SharedProfileMutexStrategy;
use LibreOffice\Temp\TempManager;
use LibreOffice\Util\Platform;
use Symfony\Component\Process\Process;

final class LibreOffice
{
    /** @var array{
     *     binary:string,
     *     timeout:int,
     *     profile_strategy:string,
     *     temp_dir:string,
     *     cleanup_policy:string,
     *     worker_id:?string
     * }
     */
    private array $options;

    private readonly CliConverter $converter;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [], ?Platform $platform = null)
    {
        $platform ??= new Platform();
        $this->options = OptionsResolver::resolve($options, $platform);

        $this->converter = new CliConverter(
            platform: $platform,
            profileStrategy: $this->createProfileStrategy($this->options),
            commandBuilder: new CommandBuilder(),
            tempManager: new TempManager(),
            errorClassifier: new ErrorClassifier()
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function make(array $options = []): self
    {
        return new self($options);
    }

    public function convert(string $inputPath): PendingConversion
    {
        return new PendingConversion($this, $inputPath);
    }

    public function run(ConvertRequest $request): ConvertResult
    {
        return $this->converter->convert($request, $this->options);
    }

    /**
     * @return array{
     *     binary:string,
     *     timeout:int,
     *     profile_strategy:string,
     *     temp_dir:string,
     *     cleanup_policy:string,
     *     worker_id:?string
     * }
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return array{ok:bool, version:string, tempWritable:bool}
     */
    public function probe(): array
    {
        $tempWritable = is_writable($this->options['temp_dir']);
        $process = new Process([$this->options['binary'], '--version'], null, null, null, 20.0);
        $process->run();

        return [
            'ok' => $process->isSuccessful() && $tempWritable,
            'version' => trim($process->getOutput() . "\n" . $process->getErrorOutput()),
            'tempWritable' => $tempWritable,
        ];
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
    private function createProfileStrategy(array $options): ProfileStrategyInterface
    {
        return match ($options['profile_strategy']) {
            'none' => new NullProfileStrategy(),
            'per_job' => new PerJobProfileStrategy(),
            'per_worker' => new PerWorkerProfileStrategy((string) $options['worker_id']),
            'shared_mutex' => new SharedProfileMutexStrategy(),
            default => new NullProfileStrategy(),
        };
    }
}
