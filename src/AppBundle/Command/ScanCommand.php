<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use AppBundle\Entity\MediaFile;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Album;

require_once(dirname(__FILE__).'/../../../vendor/james-heinrich/getid3/getid3/getid3.php');


class ScanCommand extends ContainerAwareCommand {

	protected function configure() {
		$this
    		->setName('soonic:scan')
    		->setDescription('scan folders')
    		->setHelp("\nscan folders and create database\n")
    		->addOption('guess', null, InputOption::VALUE_NONE, 'If defined, guess tags')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

        $start_time = microtime(true);

		// -- add style
		$style = new OutputFormatterStyle('white', 'red');
		$output->getFormatter()->setStyle('error', $style);

        $style = new OutputFormatterStyle('white', 'magenta');
		$output->getFormatter()->setStyle('warning', $style);


        // -- get verbosity
		$verbosity = $output->getVerbosity();

        // get --guess option
        $guess = $input->getOption('guess');

		// -- get entity manager
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // -- clear media file table
        $tables = array('media_file', 'album', 'artist');

        foreach ($tables as $table) {
            if ($verbosity >= 128) {
                $output->write("  clear table $table .");
            }

            $query = "DELETE FROM $table";
            $statement = $em->getConnection()->prepare($query)->execute();
            if ($verbosity >= 128) {
                $output->write('.');
            }

            $query = "ALTER TABLE $table AUTO_INCREMENT = 1;";
            $statement = $em->getConnection()->prepare($query)->execute();

            if ($verbosity >= 128) {
                $output->writeln('. done');
            }
        }

        /*
         * -- Scan variables
         */
        // -- folder to scan
        $root = 'web/music/test';
        // -- file types
        $types = array("mp3", "mp4", "oga", "wma", "wav", "mpg", "mpc", "m4a", "m4p", "flac");
        // -- counters
        $fileCount = 0;
        $skipCount = 0;
        $loadCount = 0;

        // -- prepare mysql query
        $query = "INSERT INTO media_file (artist, title, album, year, genre, track_number, path, web_path) VALUES (?,?,?,?,?,?,?,?)";
        $statement = $em->getConnection()->prepare($query);

        // -- open sql file
        $sqlFile = dirname(__FILE__).'/../../../web/soonic.sql';
        $fp = fopen($sqlFile, 'w');
        fwrite($fp, 'id,path,title,album,artist,track_number,year,genre,web_path,duration'.PHP_EOL);

        // -- open log file
        $logFilePath = dirname(__FILE__).'/../../../web/soonic.log';
        $logFile = fopen($logFilePath, 'w');

        // -- scan
        $di = new \RecursiveDirectoryIterator($root,\RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $it = new \RecursiveIteratorIterator($di);

        foreach($it as $file) {
            if ( in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $types) ) {

                $fileCount++;

                $hasWarning = false;
                $warningOutput = '  <warning>no ';
                $warningTags = array();
                $warningActions = array();
                $warningActionsResult = array();

                $getID3 = new \getID3;

                $fileInfo = $getID3->analyze($file);

                \getid3_lib::CopyTagsToComments($fileInfo);

                $fileInfoComments = array();

                if (!empty($fileInfo['comments'])) {
                    $fileInfoComments = $fileInfo['comments'];
                }
                elseif (!empty($fileInfo['asf']['comments'])) {
                    $fileInfoComments = $fileInfo['asf']['comments'];
                }
                else {
                    $this->printErrorMessage('no tag found', $file, $output);
                    $this->logErrorMessage('no tag found', $file, $logFile);
                    $skipCount++;
                    continue;
                }

                // -- create tags array
                $tags = array();


                /*
                 * -- Handle artist -------------------------------------------
                 */
                if (empty($fileInfoComments['artist'])) {

                    if ($guess) {

                        $hasWarning = true;
                        array_push($warningTags, 'artist');
                        array_push($warningActions, 'guessing artist name');

                        $artist = $this->previousFolder($file, 2);

                        if ($artist) {
                            $tags['artist'] = $artist;
                            array_push($warningActionsResult, $artist);
                        }
                        else {
                            $this->printErrorMessage('no artist tag found', $file, $output);
                            $this->logErrorMessage('no artist tag found', $file, $logFile);
                            $skipCount++;
                            continue;
                        }
                    }
                    else {
                        $this->logErrorMessage('no artist tag found', $file, $logFile);
                        $skipCount++;
                        continue;
                    }
                }
                else {
                    $tags['artist'] = $fileInfoComments['artist'][0];
                }


                $artist = $em->getRepository('AppBundle:Artist')->findByName($tags['artist']);

                if (!$artist) {
                    $artist = new Artist();
                    $artist->setName($tags['artist']);
                    $em->persist($artist);
                    $em->flush();
                }


                /*
                 * -- Handle album --------------------------------------------
                 */
                if (empty($fileInfoComments['album'])) {

                    if ($guess) {

                        $hasWarning = true;
                        array_push($warningTags, 'album');
                        array_push($warningActions, 'guessing album name');

                        $album = $this->previousFolder($file, 1);

                        if ($album) {
                            $tags['album'] = $album;
                            array_push($warningActionsResult, $album);
                        }
                        else {
                            $this->printErrorMessage('no album tag found', $file, $output);
                            $this->logErrorMessage('no album tag found', $file, $logFile);
                            $skipCount++;
                            continue;
                        }
                    }
                    else {
                        $this->printErrorMessage('no album tag found', $file, $output);
                        $this->logErrorMessage('no album tag found', $file, $logFile);
                        $skipCount++;
                        continue;
                    }

                }
                else {
                    $tags['album'] = $fileInfoComments['album'][0];
                }


                // -- build album list
                $album = $em->getRepository('AppBundle:Album')->findBy(array('name' => $tags['album'], 'artist' => $tags['artist']));
                if (!$album) {
                    $album = new Album();
                    $album->setName($tags['album']);
                    $album->setArtist($tags['artist']);
                    $em->persist($album);
                    $em->flush();
                }

                /*
                 * -- Handle title --------------------------------------------
                 */
                if (empty($fileInfoComments['title'])) {

                    if ($guess) {

                        $hasWarning = true;
                        array_push($warningTags, 'title');
                        array_push($warningActions, 'guessing title name');

                        $title = pathinfo($file, PATHINFO_FILENAME);

                        $tags['title'] = $title;
                        array_push($warningActionsResult, $title);
                    }
                    else {
                        $this->printErrorMessage('no title tag found', $file, $output);
                        $this->logErrorMessage('no title tag found', $file, $logFile);
                        $skipCount++;
                        continue;
                    }
                }
                else {
                    $tags['title'] = $fileInfoComments['title'][0];
                }

                /*
                 * -- Handle track number -------------------------------------
                 */
                 if (!empty($tags['track_number'])) {
                     if (!\preg_match("/[^\d+$]/", $tags['track_number'])) {

                         \preg_match("/(\d+)/", $tags['track_number'], $matches);

                         $tags['track_number'] = $matches[0];
                     }
                 }
                 else {
                     $hasWarning = true;
                     array_push($warningTags, 'track_number');
                     $tags['track_number'] = null;
                 }

                /*
                 * -- Handle year ---------------------------------------------
                 */
                if (empty($tags['year'])) {
                    if ( !empty($fileInfoComments['date']) ) {
                        $tags['year'] = $fileInfoComments['date'][0];
                    }
                    else {
                        $hasWarning = true;
                        array_push($warningTags, 'year');
                        $tags['year'] = null;
                    }
                }

                /*
                 * -- Handle duration -----------------------------------------
                 */
                if (!empty($fileInfo['playtime_string'])) {
                    $tags['duration'] = $fileInfo['playtime_string'];
                }
                else {
                    $tags['duration'] = null;
                    $hasWarning = true;
                    array_push($warningTags, 'duration');
                }

                /*
                 * -- Handle genre ---------------------------------------------
                 */
                if (empty($tags['genre'])) {
                    $tags['genre'] = null;
                    $hasWarning = true;
                    array_push($warningTags, 'genre');
                }

                /*
                 * -- Handle path and web path --------------------------------
                 */
                $tags['web_path'] = preg_replace("/^web/", '', $file);
                $tags['path'] = realpath($file);


                if ($hasWarning) {
                    $this->printWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $output);
                    $this->logWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $logFile);
                }

                // -- write to sql file
                fwrite(
                    $fp,";".$tags['path'].";".$tags['title'].";".$tags['album'].";".
                    $tags['artist'].";".$tags['track_number'].";".$tags['year'].";".
                    $tags['genre'].";".$tags['web_path'].";".$tags['duration'].PHP_EOL);

                $loadCount++;

            }
        }

        // -- load media_file table
        $query = "LOAD DATA LOCAL INFILE '/home/lpa/atinfo/www/subsonic/web/soonic.sql'  INTO TABLE media_file  FIELDS TERMINATED BY ';'  ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
        $statement = $em->getConnection()->prepare($query)->execute();

        // -- final output
        if ($verbosity >= 64) {
            $output->writeln("analysed $fileCount files.");
            $output->writeln("loaded $loadCount files");
            $output->writeln("skipped $skipCount files");

            $end_time = microtime(true);
            $duration = $end_time - $start_time;

            if ($duration < 60) {
                $output_duration = gmdate('s\s', $duration);
            }
            elseif ($duration < 3600) {
                $output_duration = gmdate('i\ms\s', $duration);
            }
            else {
                $output_duration = gmdate('H\hi\ms\s', $duration);
            }

            $output->writeln("in $output_duration");
        }
	}


    private function printErrorMessage($error, $file, $output) {
        $verbosity = $output->getVerbosity();
        if ($verbosity >= 64) {
            $warningOutput = '';
            $warningOutput .= "<error>$error</error>";
            $warningOutput .= " for ".$file;
            $warningOutput .= ' <error>-> skipping file.</error>';
            $output->writeln($warningOutput);
        }
    }

    private function logErrorMessage($error, $file, $logFile) {
        fwrite($logFile, "[error]$error;$file;skipping file\n");
    }


    private function printWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $output) {

        $verbosity = $output->getVerbosity();
        if ($verbosity >= 128) {
            $warningOutput = 'no ';

            foreach ($warningTags as $key => $tag) {
                $warningOutput .= "<warning>$tag</warning> ";
            }

            $warningOutput .= "tag found for $file ";

            foreach ($warningActions as $key => $action) {
                $warningOutput .= "<warning>$action</warning> ";
                $warningOutput .= $warningActionsResult[$key];
            }

            $output->writeln($warningOutput);
        }
    }

    private function logWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $logFile) {

        $warningOutput = '[warning]no ';

        foreach ($warningTags as $key => $tag) {
            $warningOutput .= "$tag ";
        }

        $warningOutput .= "tag found;$file;";

        foreach ($warningActions as $key => $action) {
            $warningOutput .= "$action;";
            $warningOutput .= $warningActionsResult[$key].";";
        }

        $warningOutput .= "\n";
        fwrite($logFile, $warningOutput);
    }


    private function previousFolder($file, $level) {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        $pathParts = explode('/', $path);
        for ($i = 1; $i <= $level ; $i++) {
            $folder = array_pop($pathParts);
            if (preg_match('/cd\d+/i', $folder)) {
                $folder = array_pop($pathParts);
            }
        }
        return $folder;
    }
}
