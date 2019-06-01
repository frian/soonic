<?php

namespace AppBundle\Command;

use Exception;
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

        $webPath = $this->getContainer()->get('kernel')->getProjectDir().'/web';
        $lockFile = $webPath.'/soonic.lock';

        // -- exit if there is a lock file
        if (file_exists($lockFile)) {
            $output->writeln("  <info>already running");
            exit(1);
        }

        // -- create loack file
        try {
            touch($lockFile);
        }
        catch(Exception $e) {
            $output->writeln('<error>'.$e->getMessage());
            exit(1);
        }


        // -- add style
        $style = new OutputFormatterStyle('white', 'red');
        $output->getFormatter()->setStyle('error', $style);

        $style = new OutputFormatterStyle('white', 'magenta');
        $output->getFormatter()->setStyle('warning', $style);


        // -- open log file
        $logFile = $this->openFile($webPath.'/soonic.log', $output, $lockFile);


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
        $root = $webPath.'/music';
        // -- file types
        $types = array("mp3", "mp4", "oga", "wma", "wav", "mpg", "mpc", "m4a", "m4p", "flac");
        // -- counters
        $fileCount = 0;
        $skipCount = 0;
        $loadCount = 0;
        // -- folder
        $folderFileCount = 0;
        $currentFolder = null;
//        $currentFolderTags = array();
        $previousFolder = null;
        // -- artists
        $artists = array();
        $albums = array();
        $albumTags = array();

        $currentFolderFilesTags = array();
        $previousFolderFilesTags = array();

        // -- prepare mysql query
        $query = "INSERT INTO media_file (artist, title, album, year, genre, track_number, path, web_path) VALUES (?,?,?,?,?,?,?,?)";
        $statement = $em->getConnection()->prepare($query);


        // -- open media sql file
        $sqlMediaFilePath = $webPath.'/soonic-media.sql';
        $sqlMediaFile = $this->openFile($sqlMediaFilePath, $output, $lockFile);
        fwrite($sqlMediaFile, 'id,path,web_path,title,album,artist,track_number,year,genre,duration'.PHP_EOL);


        // -- open album sql file
        $sqlAlbumFilePath = $webPath.'/soonic-album.sql';
        $sqlAlbumFile = $this->openFile($sqlAlbumFilePath, $output, $lockFile);
        fwrite($sqlAlbumFile, 'id,name,artist,song_count,duration,year,genre,path,cover_art_path'.PHP_EOL);


        // -- open artist sql file
        $sqlArtistFilePath = $webPath.'/soonic-artist.sql';
        $sqlArtistFile = $this->openFile($sqlArtistFilePath, $output, $lockFile);
        fwrite($sqlArtistFile, 'id,name,album_count,cover_art_path'.PHP_EOL);


        // -- scan
        try {
            $di = new \RecursiveDirectoryIterator($root,\RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        }
        catch(Exception $e) {
            $output->writeln('<error>'.$e->getMessage());
            unlink($lockFile);
            exit(1);
        }

        $it = new \RecursiveIteratorIterator($di);

        foreach($it as $file) {
            if ( in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $types) ) {

                $fileCount++;
                $folderFileCount++;

                $hasWarning = false;
                $warningOutput = '  <warning>no ';
                $warningTags = array();
                $warningActions = array();
                $warningActionsResult = array();

                $getID3 = new \getID3;

                $fileInfo = $getID3->analyze($file);

                \getid3_lib::CopyTagsToComments($fileInfo);

                $fileInfoComments = array();

                // -- create tags array
                $tags = array();

                if (!empty($fileInfo['comments'])) {
                    $fileInfoComments = $fileInfo['comments'];
                }
                elseif (!empty($fileInfo['asf']['comments'])) {
                    $fileInfoComments = $fileInfo['asf']['comments'];
                }
                else {

                    if ($guess) {

                        $hasWarning = true;

                        $artist = $this->previousFolder($file, 2);

                        array_push($warningTags, 'artist');
                        array_push($warningActions, 'guessing artist tag');
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

                        $album = $this->previousFolder($file, 1);

                        array_push($warningTags, 'album');
                        array_push($warningActions, 'guessing album tag');
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

                        array_push($warningTags, 'title');
                        array_push($warningActions, 'guessing title name');
                        $title = pathinfo($file, PATHINFO_FILENAME);
                        $tags['title'] = $title;
                        array_push($warningActionsResult, $title);
                    }
                    else {
                        $this->printErrorMessage('no tag found', $file, $output);
                        $this->logErrorMessage('no tag found', $file, $logFile);
                        $skipCount++;
                        continue;
                    }

                }


                /*
                 * -- Handle album --------------------------------------------
                 */
                if (empty($fileInfoComments['album']) && empty($tags['album'])) {

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
                    if (!empty($fileInfoComments['album'])) {
                        $tags['album'] = $fileInfoComments['album'][0];
                    }
                }


                /*
                 * -- Handle artist -------------------------------------------
                 */
                if (empty($fileInfoComments['artist']) && empty($tags['artist'])) {

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
                    if (!empty($fileInfoComments['artist'])) {
                        $tags['artist'] = $fileInfoComments['artist'][0];
                    }
                }


                $tags['artist'] = \strtoupper($tags['artist']);
                if (!\array_key_exists($tags['artist'], $artists)) {
                    $artists[$tags['artist']] = 0;
                    $artistId = count($artists);
                    fwrite($sqlArtistFile, ''.PHP_EOL);
                }
                else {
                    $artistId = array_search($tags['artist'],array_keys($artists)) + 1;
                }
                $tags['artistId'] = $artistId;


                /*
                 * -- Handle title --------------------------------------------
                 */
                if (empty($fileInfoComments['title']) && empty($tags['title'])) {

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
                    if (!empty($fileInfoComments['title'])) {
                        $tags['title'] = $fileInfoComments['title'][0];
                    }
                }


                /*
                 * -- Handle track number -------------------------------------
                 */
                if (!empty($fileInfoComments['track_number'])) {
                    if (!\preg_match("/[^\d+$]/", $fileInfoComments['track_number'][0])) {
                        \preg_match("/(\d+)/", $fileInfoComments['track_number'][0], $matches);
                        $tags['track_number'] = $matches[0];
                    }
                    else {
                        $tags['track_number'] = $fileInfoComments['track_number'][0];
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
                if (empty($fileInfoComments['year'])) {
                    if ( !empty($fileInfoComments['date']) ) {
                        $tags['year'] = $fileInfoComments['date'][0];
                    }
                    else {
                        $hasWarning = true;
                        array_push($warningTags, 'year');
                        $tags['year'] = null;
                    }
                }
                else {
                    $tags['year'] = $fileInfoComments['year'][0];
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
                 if (!empty($fileInfoComments['genre'])) {
                     $tags['genre'] = $fileInfoComments['genre'][0];
                 }
                 else {
                     $tags['genre'] = null;
                     $hasWarning = true;
                     array_push($warningTags, 'genre');
                 }


                /*
                 * -- Handle path and web path --------------------------------
                 */
                $tags['web_path'] = preg_replace("|^$webPath|", '', $file);
                $tags['path'] = $file;


                /*
                 * -- Build album list ----------------------------------------
                 */
                $folder = preg_replace("|^$webPath|", '', pathinfo($file, PATHINFO_DIRNAME));

                // -- add albumName key
                if ( !array_key_exists( 'albumName', $currentFolderFilesTags ) ) {
                    $currentFolderFilesTags['albumName'] = array();
                }

                // -- add album(s)
                if ( !array_key_exists( $tags['album'], $currentFolderFilesTags['albumName'] )) {
                    $currentFolderFilesTags['albumName'][$tags['album']] = array();
                }

                // -- add artistName key
                if ( !array_key_exists( 'artistName', $currentFolderFilesTags['albumName'][$tags['album']] )) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['artistName'][$tags['artist']] = array();
                }

                // -- add artist(s)
                if ( !array_key_exists( $tags['artist'], $currentFolderFilesTags['albumName'][$tags['album']]['artistName'] )) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['artistName'][$tags['artist']] = array();
                }

                // -- add titles key
                if ( !array_key_exists( 'titles', $currentFolderFilesTags['albumName'][$tags['album']]['artistName'][$tags['artist']] ) ) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['artistName'][$tags['artist']]['titles'] = array();
                }

                // -- add title(s)
                array_push($currentFolderFilesTags['albumName'][$tags['album']]['artistName'][$tags['artist']]['titles'], $tags['title']);

                // -- add year key
                if ( !array_key_exists( 'years', $currentFolderFilesTags['albumName'][$tags['album']] ) ) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['years'] = array();
                }

                // -- add year(s)
                if ($tags['year'] != null) {
                    if (!in_array($tags['year'], $currentFolderFilesTags['albumName'][$tags['album']]['years'])) {
                        array_push($currentFolderFilesTags['albumName'][$tags['album']]['years'],$tags['year']);
                    }
                }

                // -- add genre key
                if ( !array_key_exists( 'genres', $currentFolderFilesTags['albumName'][$tags['album']] ) ) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['genres'] = array();
                }

                // -- add genre(s)
                if ($tags['genre'] != null) {
                    if (!in_array($tags['genre'], $currentFolderFilesTags['albumName'][$tags['album']]['genres'])) {
                        array_push($currentFolderFilesTags['albumName'][$tags['album']]['genres'],$tags['genre']);
                    }
                }

                // -- add duration key
                if ( !array_key_exists( 'durations', $currentFolderFilesTags['albumName'][$tags['album']] ) ) {
                    $currentFolderFilesTags['albumName'][$tags['album']]['durations'] = array();
                }

                // -- add durations
                if ($tags['duration'] != null) {
                    array_push($currentFolderFilesTags['albumName'][$tags['album']]['durations'],$tags['duration']);
                }

                // -- add pathes
                $currentFolderFilesTags['albumName'][$tags['album']]['web_path'] = $folder;
                $currentFolderFilesTags['albumName'][$tags['album']]['path'] = pathinfo($file, PATHINFO_DIRNAME);


                /*
                 * -- If new folder -------------------------------------------
                 */
                if ($folder != $currentFolder) {

                    $previousFolderFilesTags = array_pop($currentFolderFilesTags['albumName']);

                    if (!empty($currentFolderFilesTags['albumName'])) {
                        $this->outputAlbumInfo($currentFolderFilesTags, $sqlAlbumFile, $artists);
                    }

                    $currentFolderFilesTags = array();
                    $currentFolderFilesTags['albumName'][$tags['album']] = $previousFolderFilesTags;
                    $previousFolderFilesTags = array();
                }
                $currentFolder = $folder;


                if ($hasWarning) {
                    $this->printWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $output);
                    $this->logWarningMessage($warningTags, $warningActions, $warningActionsResult, $file, $logFile);
                }

                // -- write to sql file
                fwrite(
                    $sqlMediaFile,";"
                    .$tags['path'].";".$tags['web_path'].";".$tags['title'].";"
                    .$tags['album'].";".$tags['artistId'].";".$tags['track_number'].";"
                    .$tags['year'].";".$tags['genre'].";".$tags['duration']
                    .PHP_EOL);

                // print ";"
                //     .$tags['path'].";".$tags['web_path'].";".$tags['title'].";"
                //     .$tags['album'].";".$tags['artist'].";".$tags['track_number'].";"
                //     .$tags['year'].";".$tags['genre'].";".$tags['duration']
                //     .PHP_EOL;

                $loadCount++;
            }
        }

        // -- output last folder
        $this->outputAlbumInfo($currentFolderFilesTags, $sqlAlbumFile, $artists);

        fclose($sqlArtistFile);


        $sqlArtistFile = $this->openFile($sqlArtistFilePath, $output, $lockFile);
        fwrite($sqlArtistFile, 'id,name,album_count,cover_art_path'.PHP_EOL);

        foreach (array_keys($artists) as $artist) {
            // print ';'.$artist. ";" . $artists[$artist].';'.PHP_EOL;
            fwrite($sqlArtistFile, ';'.$artist. ";" . $artists[$artist].';'.PHP_EOL);
        }


        // -- disable foreign keys checks
        $query = "SET FOREIGN_KEY_CHECKS=0;";
        $statement = $em->getConnection()->prepare($query)->execute();

        // -- load media_file table
        $query = "LOAD DATA LOCAL INFILE '$sqlMediaFilePath'".
            " INTO TABLE media_file  FIELDS TERMINATED BY ';'  ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
        $statement = $em->getConnection()->prepare($query)->execute();

        // -- load album table
        $query = "LOAD DATA LOCAL INFILE '$sqlAlbumFilePath'".
            " INTO TABLE album  FIELDS TERMINATED BY ';'  ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
        $statement = $em->getConnection()->prepare($query)->execute();

        // -- load artist table
        $query = "LOAD DATA LOCAL INFILE '$sqlArtistFilePath'".
            " INTO TABLE artist  FIELDS TERMINATED BY ';'  ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
        $statement = $em->getConnection()->prepare($query)->execute();

        // -- disable foreign keys checks
        $query = "SET FOREIGN_KEY_CHECKS=1;";
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

        unlink($lockFile);
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
                $warningOutput .= $warningActionsResult[$key]." ";
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

    private function outputAlbumInfo($currentFolderFilesTags, $sqlAlbumFile, &$artists) {

        foreach (array_keys($currentFolderFilesTags['albumName']) as $album) {

            // print "album title      : $album\n";

            $songCount = 0;
            $albumArtist = '';
            foreach (array_keys($currentFolderFilesTags['albumName'][$album]['artistName']) as $index => $artist) {
                $albumArtist = $artist;
                // $albumArtist .= $artist.',';  // **
                // print "album artist     :     $artist\n";
                $artists[$artist]++;
                $songCount += count($currentFolderFilesTags['albumName'][$album]['artistName'][$artist]['titles']);
            }
            // $albumArtist = \preg_replace('/,$/', '', $albumArtist); // **


            $albumYear = null;
            foreach ($currentFolderFilesTags['albumName'][$album]['years'] as $year) {
                $albumYear .= $year.',';
                // print "album year       : $year\n";
            }
            $albumYear = \preg_replace('/,$/', '', $albumYear);


            $albumGenre = null;
            foreach ($currentFolderFilesTags['albumName'][$album]['genres'] as $genre) {
                $albumGenre .= $genre.',';
                // print "album genre      : $genre\n";
            }
            $albumGenre = \preg_replace('/,$/', '', $albumGenre);


            // print "album duration   : ".$this->getAlbumDuration($currentFolderFilesTags['albumName'][$album]['durations'])."\n";
            // print "song count       : $songCount\n";
            // print "album path       : ".$currentFolderFilesTags['albumName'][$album]['path']."\n";
            // print "album web path   : ".$currentFolderFilesTags['albumName'][$album]['web_path']."\n";
            // print "\n";


            //-- 'id,name,artist,song_count,duration,year,genre,path,cover_art_path'
            fwrite(
                $sqlAlbumFile,";"
                // print
                // ";"
                .$album.";'"
                .$albumArtist."';"
                .$songCount.";"
                .$this->getAlbumDuration($currentFolderFilesTags['albumName'][$album]['durations']).";"
                .$albumYear.";"
                .$albumGenre.";"
                .$currentFolderFilesTags['albumName'][$album]['web_path'].";"
                .";" // -- covert art path
                .PHP_EOL
            );
        }
        return $artists;
    }

    private function openFile($filePath, $output, $lockFile) {
        try {
            $file = fopen($filePath, 'w');
            return $file;
        }
        catch(Exception $e) {
            $output->writeln('<error>'.$e->getMessage());
            unlink($lockFile);
            exit(1);
        }
    }

    private function getAlbumDuration($durations) {
        $hrs = 0;
        $mins = 0;
        $secs = 0;
        foreach ($durations as $duration) {
            $durationParts = explode(':', $duration);
            $numDurationParts = count($durationParts);
            if ($numDurationParts === 1) {
                $secs += (int) $durationParts[0];
            }
            elseif ($numDurationParts === 2) {
                $mins += (int) $durationParts[0];
                $secs += (int) $durationParts[1];
            }
            elseif ($numDurationParts === 3) {
                $hrs += (int) $durationParts[0];
                $mins += (int) $durationParts[1];
                $secs += (int) $durationParts[2];
            }
            // Convert each 60 minutes to an hour
            if ($mins >= 60) {
                $hrs++;
                $mins -= 60;
            }
            // Convert each 60 seconds to a minute
            if ($secs >= 60) {
                $mins++;
                $secs -= 60;
            }
        }
        $hrs = $hrs > 9 ? $hrs : 0 . $hrs;
        $mins = $mins > 9 ? $mins : 0 . $mins;
        $secs = $secs > 9 ? $secs : 0 . $secs;
        $returnValue = $secs;
        if ($hrs != 0) {
            $returnValue =  $hrs.":".$mins.":".$returnValue;
        }
        else {
            if ($mins != 0) {
                $returnValue =  $mins.":".$returnValue;
            }
        }
        return $returnValue;
    }

}
