<?php

namespace App\Command;

use App\Entity\Radio;
use App\Repository\RadioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'soonic:add:radios',
    description: 'Import radios from a .csv or .m3u file.'
)]
class AddRadiosCommand extends Command
{
    private const FORMAT_AUTO = 'auto';
    private const FORMAT_CSV = 'csv';
    private const FORMAT_M3U = 'm3u';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RadioRepository $radioRepository,
        private readonly ValidatorInterface $validator,
        private readonly string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to .csv/.m3u file')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'auto|csv|m3u', self::FORMAT_AUTO)
            ->addOption('delimiter', 'd', InputOption::VALUE_REQUIRED, 'CSV delimiter', ',')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate and preview without writing to database');
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

        $format = strtolower((string) $input->getOption('format'));
        if (!in_array($format, [self::FORMAT_AUTO, self::FORMAT_CSV, self::FORMAT_M3U], true)) {
            $io->error('Invalid --format. Allowed values: auto, csv, m3u.');

            return Command::INVALID;
        }

        if ($format === self::FORMAT_AUTO) {
            $format = $this->detectFormat($path);
        }

        $entries = match ($format) {
            self::FORMAT_CSV => $this->parseCsv($path, (string) $input->getOption('delimiter')),
            self::FORMAT_M3U => $this->parseM3u($path),
            default => [],
        };

        if ($entries === []) {
            $io->warning(sprintf('No radio entry detected in %s.', $path));

            return Command::SUCCESS;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $added = 0;
        $skipped = 0;
        $errors = 0;
        $seenStreams = [];

        foreach ($entries as $index => $entry) {
            $name = trim((string) ($entry['name'] ?? ''));
            $streamUrl = trim((string) ($entry['streamUrl'] ?? ''));
            $homepageUrl = trim((string) ($entry['homepageUrl'] ?? ''));

            if ($name === '' || $streamUrl === '') {
                ++$skipped;
                $io->warning(sprintf('Entry #%d skipped: missing name or stream URL.', $index + 1));
                continue;
            }

            $radio = new Radio();
            $radio->setName($name);
            $radio->setStreamUrl($streamUrl);
            $radio->setHomepageUrl($homepageUrl === '' ? null : $homepageUrl);

            $normalizedStream = (string) $radio->getStreamUrl();
            $streamKey = strtolower($normalizedStream);
            if (isset($seenStreams[$streamKey])) {
                ++$skipped;
                $io->warning(sprintf('Entry #%d skipped: duplicate stream URL in file (%s).', $index + 1, $normalizedStream));
                continue;
            }
            $seenStreams[$streamKey] = true;

            if ($this->radioRepository->findOneBy(['streamUrl' => $normalizedStream]) !== null) {
                ++$skipped;
                $io->note(sprintf('Entry #%d skipped: stream URL already exists (%s).', $index + 1, $normalizedStream));
                continue;
            }

            if ($this->radioRepository->findOneBy(['name' => $radio->getName()]) !== null) {
                ++$skipped;
                $io->note(sprintf('Entry #%d skipped: name already exists (%s).', $index + 1, (string) $radio->getName()));
                continue;
            }

            $violations = $this->validator->validate($radio);
            if (count($violations) > 0) {
                ++$errors;
                $messages = [];
                foreach ($violations as $violation) {
                    $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
                }
                $io->warning(sprintf('Entry #%d invalid: %s', $index + 1, implode(' | ', $messages)));
                continue;
            }

            if (!$dryRun) {
                $this->entityManager->persist($radio);
            }

            ++$added;
        }

        if (!$dryRun && $added > 0) {
            $this->entityManager->flush();
        }

        $io->section('Import summary');
        $io->writeln(sprintf('Format: %s', $format));
        $io->writeln(sprintf('File: %s', $path));
        $io->writeln(sprintf('Added: %d', $added));
        $io->writeln(sprintf('Skipped: %d', $skipped));
        $io->writeln(sprintf('Invalid: %d', $errors));
        if ($dryRun) {
            $io->warning('Dry-run mode: nothing was written to database.');
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function resolvePath(string $rawPath): string
    {
        if ($rawPath[0] === '/' || preg_match('/^[A-Za-z]:[\\\/]/', $rawPath) === 1) {
            return $rawPath;
        }

        return $this->projectDir.'/'.$rawPath;
    }

    private function detectFormat(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'csv' => self::FORMAT_CSV,
            'm3u', 'm3u8' => self::FORMAT_M3U,
            default => self::FORMAT_CSV,
        };
    }

    /**
     * @return array<int, array{name: string, streamUrl: string, homepageUrl: string}>
     */
    private function parseCsv(string $path, string $delimiter): array
    {
        $entries = [];
        $delimiter = $delimiter !== '' ? $delimiter[0] : ',';

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter, '"', '\\');

        $headerMap = null;
        foreach ($file as $row) {
            if (!is_array($row) || $row === [null]) {
                continue;
            }

            $row = array_map(static fn ($v) => trim((string) $v), $row);
            if ($row === [] || ($row[0] ?? '') === '') {
                continue;
            }

            if ($headerMap === null && $this->looksLikeHeader($row)) {
                $headerMap = $this->buildHeaderMap($row);
                continue;
            }

            if ($headerMap !== null) {
                $name = $row[$headerMap['name']] ?? '';
                $streamUrl = $row[$headerMap['streamUrl']] ?? '';
                $homepageUrl = $headerMap['homepageUrl'] !== null ? ($row[$headerMap['homepageUrl']] ?? '') : '';
            } else {
                $name = $row[0] ?? '';
                $streamUrl = $row[1] ?? '';
                $homepageUrl = $row[2] ?? '';
            }

            $entries[] = [
                'name' => $name,
                'streamUrl' => $streamUrl,
                'homepageUrl' => $homepageUrl,
            ];
        }

        return $entries;
    }

    /**
     * @return array<int, array{name: string, streamUrl: string, homepageUrl: string}>
     */
    private function parseM3u(string $path): array
    {
        $entries = [];
        $pendingName = null;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '#EXTINF')) {
                $parts = explode(',', $line, 2);
                $pendingName = isset($parts[1]) ? trim($parts[1]) : null;
                continue;
            }

            if (str_starts_with($line, '#')) {
                continue;
            }

            $url = $line;
            $name = $pendingName ?: $this->guessNameFromUrl($url);

            $entries[] = [
                'name' => $name,
                'streamUrl' => $url,
                'homepageUrl' => '',
            ];

            $pendingName = null;
        }

        return $entries;
    }

    /**
     * @param array<int, string> $row
     */
    private function looksLikeHeader(array $row): bool
    {
        $normalized = array_map(static fn (string $value): string => strtolower(trim($value)), $row);

        return in_array('name', $normalized, true)
            && (in_array('streamurl', $normalized, true) || in_array('stream_url', $normalized, true) || in_array('stream', $normalized, true) || in_array('url', $normalized, true));
    }

    /**
     * @param array<int, string> $header
     *
     * @return array{name: int, streamUrl: int, homepageUrl: ?int}
     */
    private function buildHeaderMap(array $header): array
    {
        $map = [];
        foreach ($header as $index => $col) {
            $key = strtolower(str_replace([' ', '-'], ['','_'], trim($col)));
            $map[$key] = $index;
        }

        $nameIndex = $map['name'] ?? 0;
        $streamIndex = $map['streamurl']
            ?? $map['stream_url']
            ?? $map['stream']
            ?? $map['url']
            ?? 1;
        $homepageIndex = $map['homepageurl']
            ?? $map['homepage_url']
            ?? $map['homepage']
            ?? $map['site']
            ?? null;

        return [
            'name' => $nameIndex,
            'streamUrl' => $streamIndex,
            'homepageUrl' => $homepageIndex,
        ];
    }

    private function guessNameFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            return $host;
        }

        return 'Unknown radio';
    }
}
