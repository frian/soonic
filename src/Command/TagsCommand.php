<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'soonic:tags',
    description: 'Display all detected tags for an audio file.'
)]
class TagsCommand extends Command
{
    public function __construct(
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to an audio file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rawPath = trim((string) $input->getArgument('file'));
        if ($rawPath === '') {
            $io->error('File path cannot be empty.');

            return Command::INVALID;
        }

        $path = $this->resolvePath($rawPath);
        if (!is_file($path) || !is_readable($path)) {
            $io->error(sprintf('File not found or not readable: %s', $path));

            return Command::FAILURE;
        }

        $getID3 = new \getID3();
        $fileInfo = $getID3->analyze($path);
        \getid3_lib::CopyTagsToComments($fileInfo);

        $io->title('Soonic tags');
        $io->writeln(sprintf('File: %s', $path));

        $tagsBySource = $fileInfo['tags'] ?? [];
        if (!is_array($tagsBySource) || $tagsBySource === []) {
            $io->warning('No tags found.');

            return Command::SUCCESS;
        }

        foreach ($tagsBySource as $source => $tags) {
            if (!is_array($tags)) {
                continue;
            }

            $io->section(sprintf('Source: %s', (string) $source));
            $rows = [];
            foreach ($tags as $tagName => $value) {
                $rows[] = [
                    (string) $tagName,
                    $this->formatTagValue($value),
                ];
            }

            if ($rows !== []) {
                $io->table(['Tag', 'Value'], $rows);
            } else {
                $io->writeln('No tag values.');
            }
        }

        if (!empty($fileInfo['comments']) && is_array($fileInfo['comments'])) {
            $io->section('Merged comments');
            $rows = [];
            foreach ($fileInfo['comments'] as $tagName => $value) {
                $rows[] = [
                    (string) $tagName,
                    $this->formatTagValue($value),
                ];
            }

            if ($rows !== []) {
                $io->table(['Tag', 'Value'], $rows);
            }
        }

        return Command::SUCCESS;
    }

    private function resolvePath(string $rawPath): string
    {
        if ($rawPath[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $rawPath) === 1) {
            return $rawPath;
        }

        return $this->projectDir.'/'.$rawPath;
    }

    private function formatTagValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}

