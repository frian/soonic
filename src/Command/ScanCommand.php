<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

#[AsCommand(
    name: 'soonic:scan',
    description: 'scan music folder and create database',
)]
class ScanCommand extends Command
{
    private const APPNAME = 'soonic';

    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(EntityManagerInterface $entityManager, string $projectDir)
    {
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
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

        // -- get fs pathes
        $webPath = str_replace('\\', '/', $this->projectDir.'/public');
        $scanDir = str_replace('\\', '/', $this->projectDir.'/var/scan');
        $lockDir = str_replace('\\', '/', $this->projectDir.'/var/lock');
        $lockFile = $lockDir.'/'.self::APPNAME.'.lock';

        if (!is_dir($scanDir) && !@mkdir($scanDir, 0775, true) && !is_dir($scanDir)) {
            $io->error('cannot create scan directory');
            return Command::FAILURE;
        }

        if (!is_dir($lockDir) && !@mkdir($lockDir, 0775, true) && !is_dir($lockDir)) {
            $io->error('cannot create lock directory');
            return Command::FAILURE;
        }
        
        // -- exit if there is a lock file
        if (file_exists($lockFile)) {
            $io->warning('already running');
            return Command::FAILURE;
        }

        // -- create lock file
        if (@touch($lockFile) === false) {
            $io->error('cannot create lock file');
            return Command::FAILURE;
        }

        $logFile = null;

        try {
            // -- open log file
            $logFilePath = $scanDir.'/'.self::APPNAME.'.log';
            $logFile = $this->openFile($logFilePath, $io);

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

            if ($table != 'albums_artists') {
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

        $albumsSlugs = [];
        $artistsSlugs = [];

        $hasError = false;
        $hasWarning = false;

        /*
         * -- Prepare needed files ------------------------------------------------------------------------------------
         */
        // -- open sql files
        $sqlFilesPathes = [];
        foreach ($tables as $table) {
            $sqlFilesPathes[$table] = str_replace('\\', '/', $scanDir.'/'.self::APPNAME.'-'.$table.'.sql');
            $sqlFile[$table] = $this->openFile($sqlFilesPathes[$table], $io);
        }

        // -- write headers
        $this->writeCsvRow($sqlFile['song'], ['id', 'album_id', 'artist_id', 'path', 'web_path', 'title', 'track_number', 'year', 'genre', 'duration']);
        $this->writeCsvRow($sqlFile['album'], ['id', 'name', 'album_slug', 'song_count', 'duration', 'year', 'genre', 'path', 'cover_art_path']);
        $this->writeCsvRow($sqlFile['artist'], ['id', 'name', 'artist_slug', 'album_count', 'cover_art_path']);
        $this->writeCsvRow($sqlFile['artist_album'], ['artist_id', 'album_id']);


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
                            $results = $this->addAlbumIds($songs, $albumId, $sqlFile['artist_album'], $sqlFile['song']);
                            $albumId = $results['album_id'];
                            $songs = $results['songs'];
                            $this->buildAlbumTags($songs, $albumsSlugs, $sqlFile['album'], $io, $verbosity);
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
                    if (!in_array('track_number', $fileWarningTags, true)) {
                        array_push($fileWarningTags, 'track_number');
                    }

                    $tags['track_number'] = null;
                }

                if (empty($trackTags['year'])) {
                    if (empty($trackTags['date'])) {
                        if (empty($trackTags['creation_date'])) {
                            $fileHasWarning = true;
                            if (!in_array('year', $fileWarningTags, true)) {
                                array_push($fileWarningTags, 'year');
                            }
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
                    if (!in_array('genre', $fileWarningTags, true)) {
                        array_push($fileWarningTags, 'genre');
                    }
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
            $results = $this->addAlbumIds($songs, $albumId, $sqlFile['artist_album'], $sqlFile['song']);
            $albumId = $results['album_id'];
            $songs = $results['songs'];
            $this->buildAlbumTags($songs, $albumsSlugs, $sqlFile['album'], $io, $verbosity);
        }

        // -- output warnings
        if ($hasWarning || $hasError) {

            if ($verbosity < OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $io->warning( [
                    'some files have missing tags', 
                    'check ' . str_replace($this->projectDir.'/', '', $logFilePath) . ' or run with -vv'
                ]);
            }
        }

        if ($fileCount === 0) {
            $io->warning('no audio file found');
        }

        // -- write artist tags to sql file
        // -- name,artist_slug,album_count,cover_art_path
        foreach ($artists as $artist => $albumCount) {
            $this->writeCsvRow($sqlFile['artist'], [
                '',
                $artist,
                $this->slugify($artist, $artistsSlugs),
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
                $output_duration = gmdate('i\ms\s', $duration);
            } else {
                $output_duration = gmdate('H\hi\ms\s', $duration);
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
            if (isset($sqlFile) && is_array($sqlFile)) {
                foreach ($sqlFile as $handle) {
                    if (is_resource($handle)) {
                        fclose($handle);
                    }
                }
            }
            if (is_resource($logFile)) {
                fclose($logFile);
            }
            if (file_exists($lockFile)) {
                @unlink($lockFile);
            }
        }
    }

    private function openFile(string $filePath, SymfonyStyle $io): mixed
    {
        $file = @fopen($filePath, 'w');
        if ($file === false) {
            $io->error("cannot open file: $filePath");
            throw new RuntimeException("cannot open file: $filePath");
        }

        return $file;
    }

    private function addAlbumIds(array $songs, int $albumId, mixed $sqlArtistAlbumFile, mixed $sqlSongFile): array
    {
        $albums = [];
        $ids = [];
        foreach ($songs as $song) {
            if (!in_array($song['album'], $albums)) {
                array_push($albums, $song['album']);
                $ids[$song['album']] = ++$albumId;
            }
        }

        $artistAlbumValues = [];
        foreach ($songs as &$song) {
            // -- set song album_id
            $song['album_id'] = $ids[$song['album']];

            // -- write songs to sql
            $this->writeCsvRow($sqlSongFile, [
                '',
                $song['album_id'],
                $song['artist_id'],
                $song['path'],
                $song['web_path'],
                $song['title'],
                $song['track_number'],
                $song['year'],
                $song['genre'],
                $song['duration'],
            ]);

            // -- set artist_album values
            $artistAlbumValue = $song['artist_id'].';'.$song['album_id'];
            if (!in_array($artistAlbumValue, $artistAlbumValues)) {
                array_push($artistAlbumValues, $artistAlbumValue);
            }
        }

        // -- write artist_album to sql
        foreach ($artistAlbumValues as $value) {
            $parts = explode(';', $value);
            $this->writeCsvRow($sqlArtistAlbumFile, [$parts[0], $parts[1]]);
        }

        return ['album_id' => $albumId, 'songs' => $songs];
    }
    
    private function buildAlbumTags(array $songs, array &$albumsSlugs, mixed $sqlAlbumFile, SymfonyStyle $io, int $verbosity): void
    {
        $albumSingleTags = ['year', 'genre', 'album_path'];
        $albumsTags = [];
        foreach ($songs as $song) {
            if (!array_key_exists('albums', $albumsTags)) {
                $albumsTags['albums'] = [];
            }
            if (!in_array($song['album'], $albumsTags['albums'])) {
                array_push($albumsTags['albums'], $song['album']);
            }

            if (!array_key_exists('artists', $albumsTags)) {
                $albumsTags['artists'] = [];
            }
            if (!in_array($song['artist'], $albumsTags['artists'])) {
                array_push($albumsTags['artists'], $song['artist']);
            }

            if (!array_key_exists('durations', $albumsTags)) {
                $albumsTags['durations'] = [];
            }
            array_push($albumsTags['durations'], $song['duration']);
        }

        foreach ($albumsTags['albums'] as $album) {
            $albumTags = [];
            foreach ($songs as $song) {
                if ($song['album'] === $album) {
                    foreach ($albumSingleTags as $tag) {
                        if (!array_key_exists($song[$tag], $albumTags)) {
                            $albumTags[$tag] = $song[$tag];
                        }
                    }

                    $albumTags['album'] = $album;

                    if (!array_key_exists('artists', $albumTags)) {
                        $albumTags['artists'] = [];
                    }
                    if (!in_array($song['artist'], $albumTags['artists'])) {
                        array_push($albumTags['artists'], $song['artist']);
                    }

                    if (!array_key_exists('durations', $albumTags)) {
                        $albumTags['durations'] = [];
                    }
                    array_push($albumTags['durations'], $song['duration']);
                }
            }
            if (count($albumTags['artists']) > 1) {
                $albumTags['artist'] = 'Various';
            } else {
                $albumTags['artist'] = $albumTags['artists'][0];
            }

            // -- write album to sql
            $this->writeCsvRow($sqlAlbumFile, [
                '',
                $albumTags['album'],
                $this->slugify($albumTags['album'], $albumsSlugs),
                count($albumTags['durations']),
                $this->getAlbumDuration($albumTags['durations']),
                $albumTags['year'],
                $albumTags['genre'],
                $albumTags['album_path'],
                '', // -- cover art path
            ]);

            if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $io->text(" added album ".$albumTags['album_path']);
            }
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

    private function slugify(string $string, array &$slugs): string
    {
        $string = mb_strtolower($string);
        $string = preg_replace('/\s+-\s+/', '-', $string);
        $string = preg_replace('/&/', 'and', $string);
        $string = preg_replace('|[\s+\/]|', '-', $string);
        $string = preg_replace('/-+/', '-', $string);

        $slug = $string;
        $slugCount = 1;
        while (in_array($slug, $slugs)) {
            $slug = $string.'-'.$slugCount;
            ++$slugCount;
        }
        array_push($slugs, $slug);

        return $slug;
    }

    private function getAlbumDuration(array $durations): string
    {
        $secs = 0;
        foreach ($durations as $duration) {
            $durationParts = explode(':', $duration);
            $numDurationParts = count($durationParts);
            if ($numDurationParts === 1) {
                $secs += (int) $durationParts[0];
            } elseif ($numDurationParts === 2) {
                $secs += (int) $durationParts[0] * 60;
                $secs += (int) $durationParts[1];
            } elseif ($numDurationParts === 3) {
                $secs += (int) $durationParts[0] * 3600;
                $secs += (int) $durationParts[1] * 60;
                $secs += (int) $durationParts[2];
            }
        }
        $secs = $secs > 9 ? $secs : 0 .$secs;

        return $secs;
    }

    private function writeCsvRow(mixed $file, array $fields): void
    {
        fputcsv($file, $fields, ';', '"', '\\');
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
