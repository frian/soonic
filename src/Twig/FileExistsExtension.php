<?php

namespace App\Twig;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExistsExtension extends AbstractExtension
{
    private $fileSystem;
    private $projectDir;

    public function __construct(Filesystem $fileSystem, string $projectDir)
    {
        $this->fileSystem = $fileSystem;
        $this->projectDir = $projectDir;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileExists']),
            new TwigFunction('file_mtime', [$this, 'fileMtime']),
        ];
    }

    /**
     * @param string An absolute or relative to public folder path
     *
     * @return bool True if file exists, false otherwise
     */
    public function fileExists(string $path): bool
    {
        if (!$this->fileSystem->isAbsolutePath($path)) {
            $path = "{$this->projectDir}/public/{$path}";
        }

        return $this->fileSystem->exists($path);
    }

    /**
     * @param string An absolute or relative to public folder path
     *
     * @return int Unix timestamp of file modification time, 0 if file does not exist
     */
    public function fileMtime(string $path): int
    {
        if (!$this->fileSystem->isAbsolutePath($path)) {
            $path = "{$this->projectDir}/public/{$path}";
        }

        if (!$this->fileSystem->exists($path)) {
            return 0;
        }

        $mtime = @filemtime($path);

        return $mtime === false ? 0 : $mtime;
    }
}
