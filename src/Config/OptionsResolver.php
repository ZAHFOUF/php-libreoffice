<?php

declare(strict_types=1);

namespace LibreOffice\Config;

use LibreOffice\Exception\InvalidOptionException;
use LibreOffice\Util\File;
use LibreOffice\Util\Path;
use LibreOffice\Util\Platform;

final class OptionsResolver
{
    private const PROFILE_STRATEGIES = ['none', 'per_job', 'per_worker', 'shared_mutex'];
    private const CLEANUP_POLICIES = ['always', 'keep_on_failure'];
    private const GLOBAL_OPTIONS_PATH = __DIR__ . '/global_options.php';
    /**
     * @param array<string, mixed> $options
     * @return array{
     *     binary:string,
     *     timeout:int,
     *     profile_strategy:string,
     *     temp_dir:string,
     *     cleanup_policy:string,
     *     worker_id:?string
     * }
     */
    public static function resolve(array $options, Platform $platform): array
    {
        $resolved = [
            'binary' => isset($options['binary']) ? (string) $options['binary'] : self::getBinary(),
            'timeout' => isset($options['timeout']) ? (int) $options['timeout'] : 120,
            'profile_strategy' => isset($options['profile_strategy']) ? (string) $options['profile_strategy'] : $platform->defaultProfileStrategy(),
            'temp_dir' => isset($options['temp_dir']) ? (string) $options['temp_dir'] : sys_get_temp_dir(),
            'cleanup_policy' => isset($options['cleanup_policy']) ? (string) $options['cleanup_policy'] : 'always',
            'worker_id' => isset($options['worker_id']) ? (string) $options['worker_id'] : null,
        ];

        if ($resolved['binary'] === '') {
            throw new InvalidOptionException('Option "binary" must be a non-empty string.');
        }
        if ($resolved['timeout'] <= 0) {
            throw new InvalidOptionException('Option "timeout" must be greater than zero seconds.');
        }
        if (!in_array($resolved['profile_strategy'], self::PROFILE_STRATEGIES, true)) {
            throw new InvalidOptionException(sprintf(
                'Option "profile_strategy" must be one of: %s.',
                implode(', ', self::PROFILE_STRATEGIES)
            ));
        }
        if (!in_array($resolved['cleanup_policy'], self::CLEANUP_POLICIES, true)) {
            throw new InvalidOptionException(sprintf(
                'Option "cleanup_policy" must be one of: %s.',
                implode(', ', self::CLEANUP_POLICIES)
            ));
        }

        $resolved['temp_dir'] = Path::absolute($resolved['temp_dir']);

        if (!is_dir($resolved['temp_dir']) && !mkdir($resolved['temp_dir'], 0777, true) && !is_dir($resolved['temp_dir'])) {
            throw new InvalidOptionException(sprintf('Option "temp_dir" is not creatable: %s', $resolved['temp_dir']));
        }
        if (!is_writable($resolved['temp_dir'])) {
            throw new InvalidOptionException(sprintf('Option "temp_dir" is not writable: %s', $resolved['temp_dir']));
        }

        if ($resolved['profile_strategy'] === 'per_worker' && ($resolved['worker_id'] === null || trim($resolved['worker_id']) === '')) {
            $resolved['worker_id'] = (string) getmypid();
        }

        return $resolved;
    }

    public static function saveGlobalOptions(array $data): void
    {
        $existing = self::loadGlobalOptions();
        foreach ($data as $key => $value) {
            $existing[$key] = $value;
        }
        File::savePhp(self::GLOBAL_OPTIONS_PATH, $existing);
    }

    public static function loadGlobalOptions(): array
    {
        if (!file_exists(self::GLOBAL_OPTIONS_PATH)) {
            return [];
        }
        return File::loadPhp(self::GLOBAL_OPTIONS_PATH);
    }

    public static function getGlobalOption(string $key): mixed
    {
        $options = self::loadGlobalOptions();
        return $options[$key] ?? null;
    }

    public static function setGlobalOption(string $key, mixed $value): void
    {
        $options = self::loadGlobalOptions();
        $options[$key] = $value;
        self::saveGlobalOptions($options);
    }

    public static function getBinary(): string
    {
        return self::getGlobalOption('binary') ?? 'soffice';
    }
}
