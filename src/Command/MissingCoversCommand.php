<?php

namespace App\Command;

use App\Scan\ScanArtifactsManager;
use App\Scan\ScanDataWriter;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'soonic:missing:covers',
    description: 'List music folders that contain audio files but no cover image',
)]
final class MissingCoversCommand extends Command
{
    /**
     * Same formats as soonic:scan (ScanCommand).
     *
     * @var array<int, string>
     */
    private const AUDIO_EXTENSIONS = ['mp3', 'mp4', 'oga', 'ogg', 'opus', 'wav', 'aac', 'm4a', 'webm', 'flac'];

    public function __construct(
        private readonly ScanArtifactsManager $artifactsManager,
        private readonly ScanDataWriter $dataWriter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $root = rtrim($this->artifactsManager->getMusicRoot(), '/');
        if (!is_dir($root)) {
            $io->error('music folder not found');

            return Command::FAILURE;
        }

        $coverFilenames = array_map('strtolower', $this->dataWriter->getAlbumCoverFilenames());

        /** @var array<string, array{has_audio: bool, has_cover: bool}> $folders */
        $folders = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
            );
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $dir = str_replace('\\', '/', (string) $file->getPath());
            $folders[$dir] ??= ['has_audio' => false, 'has_cover' => false];

            $extension = strtolower($file->getExtension());
            if (in_array($extension, self::AUDIO_EXTENSIONS, true)) {
                $folders[$dir]['has_audio'] = true;
            }

            $filename = strtolower($file->getFilename());
            if (in_array($filename, $coverFilenames, true)) {
                $folders[$dir]['has_cover'] = true;
            }
        }

        $missingCoverDirs = [];
        foreach ($folders as $dir => $flags) {
            if ($flags['has_audio'] && !$flags['has_cover']) {
                $relative = ltrim(substr($dir, strlen($root)), '/');
                $missingCoverDirs[] = $relative !== '' ? $relative : '.';
            }
        }

        sort($missingCoverDirs, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($missingCoverDirs as $relativePath) {
            $io->writeln($relativePath);
        }

        $io->newLine();
        $io->success(sprintf('%d folder(s) with music and no cover image', count($missingCoverDirs)));

        return Command::SUCCESS;
    }
}
