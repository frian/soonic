<?php

namespace App\Scan;

use RuntimeException;

final class ScanArtifactsManager
{
    private const APPNAME = 'soonic';

    public function __construct(private readonly string $projectDir)
    {
    }

    public function getPublicPath(): string
    {
        return str_replace('\\', '/', $this->projectDir.'/public');
    }

    public function getMusicRoot(): string
    {
        return $this->getPublicPath().'/music/';
    }

    public function getScanDir(): string
    {
        return str_replace('\\', '/', $this->projectDir.'/var/scan');
    }

    public function getLockDir(): string
    {
        return str_replace('\\', '/', $this->projectDir.'/var/lock');
    }

    public function getLockFilePath(): string
    {
        return $this->getLockDir().'/'.self::APPNAME.'.lock';
    }

    public function getLogFilePath(): string
    {
        return $this->getScanDir().'/'.self::APPNAME.'.log';
    }

    public function ensureRuntimeDirectories(): void
    {
        $scanDir = $this->getScanDir();
        if (!is_dir($scanDir) && !@mkdir($scanDir, 0775, true) && !is_dir($scanDir)) {
            throw new RuntimeException('cannot create scan directory');
        }

        $lockDir = $this->getLockDir();
        if (!is_dir($lockDir) && !@mkdir($lockDir, 0775, true) && !is_dir($lockDir)) {
            throw new RuntimeException('cannot create lock directory');
        }
    }

    public function acquireLock(): void
    {
        $lockFile = $this->getLockFilePath();
        if (file_exists($lockFile)) {
            throw new RuntimeException('already running');
        }

        if (@touch($lockFile) === false) {
            throw new RuntimeException('cannot create lock file');
        }
    }

    public function releaseLock(): void
    {
        $lockFile = $this->getLockFilePath();
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
    }

    /**
     * @return resource
     */
    public function openLogFile()
    {
        return $this->openFile($this->getLogFilePath());
    }

    /**
     * @param array<int, string> $tables
     *
     * @return array{paths: array<string, string>, handles: array<string, mixed>}
     */
    public function openSqlFiles(array $tables): array
    {
        $paths = [];
        $handles = [];
        $scanDir = $this->getScanDir();

        foreach ($tables as $table) {
            $path = str_replace('\\', '/', $scanDir.'/'.self::APPNAME.'-'.$table.'.sql');
            $paths[$table] = $path;
            $handles[$table] = $this->openFile($path);
        }

        return ['paths' => $paths, 'handles' => $handles];
    }

    /**
     * @param array<string, mixed> $handles
     */
    public function closeHandles(array $handles): void
    {
        foreach ($handles as $handle) {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function toProjectRelativePath(string $path): string
    {
        return str_replace($this->projectDir.'/', '', $path);
    }

    /**
     * @return resource
     */
    private function openFile(string $path)
    {
        $file = @fopen($path, 'w');
        if ($file === false) {
            throw new RuntimeException("cannot open file: $path");
        }

        return $file;
    }
}
