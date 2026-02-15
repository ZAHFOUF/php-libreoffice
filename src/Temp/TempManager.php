<?php

declare(strict_types=1);

namespace LibreOffice\Temp;

use LibreOffice\Util\Path;

final class TempManager
{
    /**
     * @return array{jobRoot:string,inDir:string,outDir:string}
     */
    public function createJobDirectories(string $tempDir, string $jobId): array
    {
        $jobRoot = Path::normalize($tempDir . DIRECTORY_SEPARATOR . 'lo-job-' . $jobId);
        $inDir = $jobRoot . DIRECTORY_SEPARATOR . 'in';
        $outDir = $jobRoot . DIRECTORY_SEPARATOR . 'out';

        $this->mkdir($inDir);
        $this->mkdir($outDir);

        return [
            'jobRoot' => $jobRoot,
            'inDir' => $inDir,
            'outDir' => $outDir,
        ];
    }

    public function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->cleanupDir($path);
                continue;
            }
            @unlink($path);
        }

        @rmdir($dir);
    }

    private function mkdir(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Unable to create directory: %s', $path));
        }
    }
}
