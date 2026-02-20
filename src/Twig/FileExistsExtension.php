<?php

namespace App\Twig;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExistsExtension extends AbstractExtension
{
    private Filesystem $fileSystem;
    private string $publicDir;

    /** @var array<string, bool> */
    private array $existsCache = [];

    /** @var array<string, int> */
    private array $mtimeCache = [];

    public function __construct(Filesystem $fileSystem, string $projectDir)
    {
        $this->fileSystem = $fileSystem;
        $this->publicDir = rtrim($projectDir, '/').'/public';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileExists']),
            new TwigFunction('file_mtime', [$this, 'fileMtime']),
        ];
    }

    /**
     * @param string $path Absolute path or path relative to the public directory
     *
     * @return bool True if file exists, false otherwise
     */
    public function fileExists(string $path): bool
    {
        $resolvedPath = $this->resolvePublicPath($path);
        if ($resolvedPath === null) {
            return false;
        }

        if (array_key_exists($resolvedPath, $this->existsCache)) {
            return $this->existsCache[$resolvedPath];
        }

        $exists = $this->fileSystem->exists($resolvedPath);
        $this->existsCache[$resolvedPath] = $exists;

        return $exists;
    }

    /**
     * @param string $path Absolute path or path relative to the public directory
     *
     * @return int Unix timestamp of file modification time, 0 if file does not exist
     */
    public function fileMtime(string $path): int
    {
        $resolvedPath = $this->resolvePublicPath($path);
        if ($resolvedPath === null) {
            return 0;
        }

        if (array_key_exists($resolvedPath, $this->mtimeCache)) {
            return $this->mtimeCache[$resolvedPath];
        }

        if (!$this->fileExists($resolvedPath)) {
            $this->mtimeCache[$resolvedPath] = 0;
            return 0;
        }

        $mtime = @filemtime($resolvedPath);
        $timestamp = $mtime === false ? 0 : $mtime;
        $this->mtimeCache[$resolvedPath] = $timestamp;

        return $timestamp;
    }

    private function resolvePublicPath(string $path): ?string
    {
        if ($this->fileSystem->isAbsolutePath($path)) {
            $resolved = realpath($path);
            if ($resolved === false || !$this->isInsidePublicDir($resolved)) {
                return null;
            }

            return $resolved;
        }

        $path = ltrim($path, '/');
        $candidate = $this->publicDir.'/'.$path;
        $resolved = realpath($candidate);

        if ($resolved === false || !$this->isInsidePublicDir($resolved)) {
            return null;
        }

        return $resolved;
    }

    private function isInsidePublicDir(string $absolutePath): bool
    {
        return str_starts_with($absolutePath, $this->publicDir.'/')
            || $absolutePath === $this->publicDir;
    }
}
