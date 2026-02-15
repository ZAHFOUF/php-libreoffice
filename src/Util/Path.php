<?php

declare(strict_types=1);

namespace LibreOffice\Util;

final class Path
{
    public static function isAbsolute(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }

    public static function absolute(string $path): string
    {
        if (self::isAbsolute($path)) {
            return self::normalize($path);
        }

        return self::normalize((string) getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    public static function normalize(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $prefix = '';

        if ((bool) preg_match('/^[A-Za-z]:/', $path) === true) {
            $prefix = substr($path, 0, 2);
            $path = substr($path, 2);
        } elseif (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $prefix = DIRECTORY_SEPARATOR;
            $path = ltrim($path, DIRECTORY_SEPARATOR);
        }

        $parts = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }

        $normalized = implode(DIRECTORY_SEPARATOR, $parts);
        if ($prefix !== '' && !str_ends_with($prefix, DIRECTORY_SEPARATOR) && $normalized !== '') {
            return $prefix . DIRECTORY_SEPARATOR . $normalized;
        }

        return $prefix . $normalized;
    }

    public static function baseName(string $path): string
    {
        $basename = pathinfo($path, PATHINFO_BASENAME);

        return preg_replace('/[^A-Za-z0-9._-]/', '_', $basename) ?? 'document';
    }

    public static function extension(string $path): string
    {
        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    }
}
