<?php

namespace App\Command;

use App\Scan\ScanArtifactsManager;
use App\Scan\ScanDataWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

#[AsCommand(
    name: 'soonic:scan',
    description: 'scan music folder and create database',
)]
class ScanCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ScanArtifactsManager $artifactsManager,
        private readonly ScanDataWriter $dataWriter
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start_time = microtime(true);

        // - get Symfony output formatter
        $io = new SymfonyStyle($input, $output);

        // -- add custom output style
        $style = new OutputFormatterStyle('green', 'black');
        $io->getFormatter()->setStyle('soonic_info', $style);

        $style = new OutputFormatterStyle('white', '#060');
        $output->getFormatter()->setStyle('soonic_warning', $style);

        $style = new OutputFormatterStyle('white', '#a00');
        $output->getFormatter()->setStyle('soonic_error', $style);

        // -- get verbosity level
        $verbosity = $io->getVerbosity();

        $webPath = $this->artifactsManager->getPublicPath();

        try {
            $this->artifactsManager->ensureRuntimeDirectories();
            $this->artifactsManager->acquireLock();
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'already running') {
                $io->warning('already running');
            } else {
                $io->error($e->getMessage());
            }

            return Command::FAILURE;
        }

        $logFile = null;
        $sqlFile = [];

        try {
            // -- open log file
            $logFilePath = $this->artifactsManager->getLogFilePath();
            $logFile = $this->artifactsManager->openLogFile();

        // -- get entity manager
        $em = $this->entityManager;

        // -- clear media file table
        $tables = ['song', 'album', 'artist', 'artist_album'];

        if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
            $io->title('Clearing tables');
        }

        foreach ($tables as $table) {
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->write("<soonic_info> [INFO]   clear table $table .</soonic_info>");
            }

            $query = "DELETE FROM $table";
            $em->getConnection()->prepare($query)->executeStatement();
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->write('<soonic_info>.</soonic_info>');
            }

            if ($table !== 'artist_album') {
                $query = "ALTER TABLE $table AUTO_INCREMENT = 1;";
                $em->getConnection()->prepare($query)->executeStatement();
            }

            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->writeln('<soonic_info>. done</soonic_info>');
            }
        }

        if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
            $io->newLine();
        }

        /*
         * -- Scan variables ------------------------------------------------------------------------------------------
         */
        // -- folder to scan
        $root = $webPath.'/music/';

        if (!is_dir($root)) {
            $io->newLine();
            $io->error('music folder not found');
            return Command::FAILURE;
        }

        // -- file types
        $types = ['mp3', 'mp4', 'oga', 'wma', 'wav', 'mpg', 'mpc', 'm4a', 'm4p', 'flac'];
        // -- counters
        $fileCount = 0;
        $skipCount = 0;
        $loadCount = 0;
        // -- folder
        $currentFolder = null;
        $previousFolder = null;
        // -- artists ans album lists
        $songs = [];
        $artists = [];
        $artistIds = [];
        $albumId = 0;

        $albumsTags = [];

        /** @var array<int, string> $albumsSlugs */
        $albumsSlugs = [];
        /** @var array<int, string> $artistsSlugs */
        $artistsSlugs = [];

        $hasError = false;
        $hasWarning = false;

        /*
         * -- Prepare needed files ------------------------------------------------------------------------------------
         */
        // -- open sql files
        $openedSqlFiles = $this->artifactsManager->openSqlFiles($tables);
        $sqlFilesPathes = $openedSqlFiles['paths'];
        $sqlFile = $openedSqlFiles['handles'];

        // -- write headers
        $this->dataWriter->writeCsvRow($sqlFile['song'], ['id', 'album_id', 'artist_id', 'path', 'web_path', 'title', 'track_number', 'year', 'genre', 'duration']);
        $this->dataWriter->writeCsvRow($sqlFile['album'], ['id', 'name', 'album_slug', 'song_count', 'duration', 'year', 'genre', 'path', 'cover_art_path']);
        $this->dataWriter->writeCsvRow($sqlFile['artist'], ['id', 'name', 'artist_slug', 'album_count', 'cover_art_path']);
        $this->dataWriter->writeCsvRow($sqlFile['artist_album'], ['artist_id', 'album_id']);


        // -- get iterator
        try {
            $di = new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $it = new \RecursiveIteratorIterator($di);
        $getID3 = new \getID3();

        /*
         * -- SCAN ----------------------------------------------------------------------------------------------------
         */
        $io->title('Scanning');

        // -- if -vv hide progress bar
        if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $iterator = $it;
        } else {
            $iterator = $io->progressIterate($it);
        }

        foreach ($iterator as $file) {
            if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $types)) {
  
                ++$fileCount;
                $file = str_replace('\\', '/', $file);
                $currentFolder = preg_replace("|^$webPath|", '', pathinfo($file, PATHINFO_DIRNAME));

                // -- on folder change
                if ($currentFolder !== $previousFolder) {
                    if ($previousFolder !== null) {
                        if (!empty($songs)) {
                            $results = $this->dataWriter->addAlbumIds($songs, $albumId, $sqlFile['artist_album'], $sqlFile['song']);
                            $albumId = $results['album_id'];
                            $songs = $results['songs'];
                            $this->dataWriter->buildAlbumTags($songs, $albumsSlugs, $sqlFile['album'], $io, $verbosity);
                        }

                        $songs = [];
                        $albumsTags = [];
                    }
                    $previousFolder = $currentFolder;
                }

                // -- get track tags
                $fileInfo = $getID3->analyze($file);

                \getid3_lib::CopyTagsToComments($fileInfo);

                /*
                 * -- Build track tags --------------------------------------------------------------------------------
                 */
                $trackTags = [];
                $fileHasWarning = false;
                $fileWarningTags = [];

                // -- copy tags or skip file
                if (!empty($fileInfo['comments'])) {
                    $trackTags = $fileInfo['comments'];
                } elseif (!empty($fileInfo['asf']['comments'])) {
                    $trackTags = $fileInfo['asf']['comments'];
                } else {
                    $skipCount = $this->skipFile("No tags;  $file", $logFile, $skipCount, $io, $verbosity, $root);
                    $hasError = true;
                    continue;
                }

                if (!empty($fileInfo['playtime_string'])) {
                    $trackTags['duration'][0] = $fileInfo['playtime_string'];
                }
                elseif (!empty($fileInfo['playtime_seconds'])) {
                    $totalSeconds = (int) round((float) $fileInfo['playtime_seconds']);
                    $hours = intdiv($totalSeconds, 3600);
                    $minutes = intdiv($totalSeconds % 3600, 60);
                    $seconds = $totalSeconds % 60;

                    if ($hours > 0) {
                        $trackTags['duration'][0] = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
                    } else {
                        $trackTags['duration'][0] = sprintf('%d:%02d', $minutes, $seconds);
                    }
                }

                // -- store formatted tags
                $tags = [];
                $requiredTags = ['album', 'artist', 'title', 'duration'];

                foreach ($requiredTags as $requiredTag) {
                    if (!empty($trackTags[$requiredTag])) {
                        $tags[$requiredTag] = $trackTags[$requiredTag][0];
                    } else {
                        $skipCount = $this->skipFile("No $requiredTag tag $file", $logFile, $skipCount, $io, $verbosity, $root);
                        $hasError = true;
                        continue 2;
                    }
                }

                if (!empty($trackTags['track_number'])) {
                    if (!preg_match("/[^\d+$]/", $trackTags['track_number'][0])) {
                        preg_match("/(\d+)/", $trackTags['track_number'][0], $matches);
                        $tags['track_number'] = $matches[0];
                    } else {
                        $tags['track_number'] = $trackTags['track_number'][0];
                    }
                } else {
                    $fileHasWarning = true;
                    $fileWarningTags[] = 'track_number';

                    $tags['track_number'] = null;
                }

                if (empty($trackTags['year'])) {
                    if (empty($trackTags['date'])) {
                        if (empty($trackTags['creation_date'])) {
                            $fileHasWarning = true;
                            $fileWarningTags[] = 'year';
                            $tags['year'] = null;
                        } else {
                            $tags['year'] = $trackTags['creation_date'][0];
                        }
                    } else {
                        $tags['year'] = $trackTags['date'][0];
                    }
                } else {
                    $tags['year'] = $trackTags['year'][0];
                }

                if (!empty($trackTags['genre'])) {
                    $tags['genre'] = $trackTags['genre'][0];
                } else {
                    $tags['genre'] = null;
                    $fileHasWarning = true;
                    $fileWarningTags[] = 'genre';
                }

                $tags['web_path'] = preg_replace("|^$webPath|", '', $file);
                $tags['path'] = $file;

                if (!array_key_exists('artists_ids', $albumsTags)) {
                    $albumsTags['artists_ids'] = [];
                }
                $tags['artist'] = mb_strtoupper($tags['artist']);
                if (!\array_key_exists($tags['artist'], $artists)) {
                    $artists[$tags['artist']] = 0;
                    $artistIds[$tags['artist']] = count($artistIds) + 1;
                }
                $artistId = $artistIds[$tags['artist']];

                if (!in_array($artistId, $albumsTags['artists_ids'])) {
                    array_push($albumsTags['artists_ids'], $artistId);
                }
                $tags['artist_id'] = $artistId;

                $tags['album'] = ucwords(mb_strtolower($tags['album']));

                $tags['web_path'] = preg_replace("|^$webPath|", '', $file);
                $tags['path'] = $file;

                $tags['album_path'] = preg_replace("|^$webPath|", '', pathinfo($file, PATHINFO_DIRNAME));
                $tags['album_fs_path'] = pathinfo($file, PATHINFO_DIRNAME);

                if ($fileHasWarning) {
                    $hasWarning = true;
                    $this->logWarningMessage($fileWarningTags, $file, $logFile);
                    if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                        $this->printWarningMessage($fileWarningTags, $file, $root, $io);
                    }
                }

                array_push($songs, $tags);

                ++$loadCount;
            }
        }

        // -- handle last folder
        if (!empty($songs)) {
            $results = $this->dataWriter->addAlbumIds($songs, $albumId, $sqlFile['artist_album'], $sqlFile['song']);
            $albumId = $results['album_id'];
            $songs = $results['songs'];
            $this->dataWriter->buildAlbumTags($songs, $albumsSlugs, $sqlFile['album'], $io, $verbosity);
        }

        // -- output warnings
        if ($hasWarning || $hasError) {

            if ($verbosity < OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $io->warning( [
                    'some files have missing tags', 
                    'check '.$this->artifactsManager->toProjectRelativePath($logFilePath).' or run with -vv'
                ]);
            }
        }

        if ($fileCount === 0) {
            $io->warning('no audio file found');
        }

        // -- write artist tags to sql file
        // -- name,artist_slug,album_count,cover_art_path
        foreach ($artists as $artist => $albumCount) {
                $this->dataWriter->writeCsvRow($sqlFile['artist'], [
                    '',
                    $artist,
                    $this->dataWriter->slugify($artist, $artistsSlugs),
                    $albumCount,
                    '',
                ]);
        }

        /*
         * -- load data in db -----------------------------------------------------------------------------------------
         */
        if ($fileCount > 0) {
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->title('Loading db');
                $io->write("<soonic_info> [INFO]   disable checks .</soonic_info>");
            }
    
            // -- disable foreign keys checks
            $query = 'SET FOREIGN_KEY_CHECKS=0;';
            $em->getConnection()->prepare($query)->executeStatement();
    
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->write("<soonic_info>.</soonic_info>");
            }
    
            // -- enable local-infile
            $this->setLocalInfile($em, true, $io, $verbosity);
    
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->writeln("<soonic_info>. done</soonic_info>");
                $io->write("<soonic_info> [INFO]   loading data .</soonic_info>");
            }
    
            
            // -- bulk load collection
            foreach ($tables as $table) {
                $query = "LOAD DATA LOCAL INFILE '".$sqlFilesPathes[$table]."'".
                    ' INTO TABLE '.$table." CHARACTER SET UTF8 FIELDS TERMINATED BY ';' ".
                    " ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
                $em->getConnection()->prepare($query)->executeStatement();
    
                if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $io->write("<soonic_info>.</soonic_info>");
                }
            }
    
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->writeln("<soonic_info>. done</soonic_info>");
                $io->write("<soonic_info> [INFO]   restore checks .</soonic_info>");
            }
    
            // -- enable foreign keys checks
            $query = 'SET FOREIGN_KEY_CHECKS=1;';
            $em->getConnection()->prepare($query)->executeStatement();
    
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->write("<soonic_info>.</soonic_info>");
            }
    
            // -- disable local-infile
            $this->setLocalInfile($em, false, $io, $verbosity);
    
            if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->writeln("<soonic_info>. done</soonic_info>");
            }
        }

        // -- final output
        if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
            $io->title('Summary');
            $io->writeln("<soonic_info> [INFO]   analysed $fileCount files");
            $io->writeln(" [INFO]   loaded $loadCount files");
            $io->writeln(" [INFO]   skipped $skipCount files");

            $end_time = microtime(true);
            $rawDuration = $end_time - $start_time;

            if ($rawDuration < 60) {
                $duration = round($rawDuration,2);
            }
            else {
                $duration = round($rawDuration);
            }

            if ($duration < 60) {
                $output_duration = $duration.'s';
            } elseif ($duration < 3600) {
                $output_duration = gmdate('i\ms\s', (int) $duration);
            } else {
                $output_duration = gmdate('H\hi\ms\s', (int) $duration);
            }

            $io->writeln(" [INFO]   in $output_duration");
            $io->newLine();
            $io->writeln(' [INFO]   done. \o/');
        }

        return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } finally {
            $this->artifactsManager->closeHandles($sqlFile);
            if (is_resource($logFile)) {
                fclose($logFile);
            }
            $this->artifactsManager->releaseLock();
        }
    }

    private function logWarningMessage(array $warningTags, string $file, mixed $logFile): void
    {
        $warningOutput = '[warning]no ';
        foreach ($warningTags as $key => $tag) {
            $warningOutput .= "$tag ";
        }
        $warningOutput .= "tag;$file\n";
        fwrite($logFile, $warningOutput);
    }

    private function printWarningMessage(array $warningTags, string $file, string $root, SymfonyStyle $io): void
    {
        $file = str_replace($root, '', $file);
        $warningOutput = '<soonic_warning>[WARNING]</soonic_warning> no ';
        foreach ($warningTags as $key => $tag) {
            $warningOutput .= "$tag ";
        }
        $warningOutput .= "tag for $file";
        $io->writeln($warningOutput);
    }

    private function skipFile(string $error, mixed $logFile, int $skipCount, SymfonyStyle $io, int $verbosity, string $root): int
    {
        fwrite($logFile, "[error]$error;SKIPPING FILE".PHP_EOL);
        if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $error = str_replace($root, '', $error);
            $io->writeln("<soonic_error>[ERROR]  </soonic_error> $error <soonic_error>SKIPPING FILE</soonic_error>");
        }

        return ++$skipCount;
    }


    private function setLocalInfile(EntityManagerInterface $em, bool $enabled, SymfonyStyle $io, int $verbosity): void
    {
        $value = $enabled ? 1 : 0;

        try {
            $query = "SET SESSION local_infile = $value";
            $em->getConnection()->prepare($query)->executeStatement();
            return;
        } catch (\Throwable $sessionException) {
            // fallback for servers where local_infile is GLOBAL-only
        }

        try {
            $query = "SET GLOBAL local_infile = $value";
            $em->getConnection()->prepare($query)->executeStatement();
        } catch (\Throwable $globalException) {
            if ($enabled) {
                $io->warning('local_infile cannot be changed by this user; LOAD DATA LOCAL INFILE may fail depending on server config.');
            } elseif ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->writeln('<soonic_warning>[WARNING]</soonic_warning> unable to disable local_infile after import.');
            }
        }
    }
}
