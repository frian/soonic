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
    		->addOption('force', null, InputOption::VALUE_NONE, 'Si définie, les modifications sont appliquées')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

        $start_time = microtime(true);

		// -- add style
		$style = new OutputFormatterStyle('white', 'red');
		$output->getFormatter()->setStyle('error', $style);

        $style = new OutputFormatterStyle('white', 'magenta');
		$output->getFormatter()->setStyle('warning', $style);

        // $style = new OutputFormatterStyle('default', 'default');
        // $output->getFormatter()->setStyle('normal', $style);

        // -- get verbosity
		$verbosity = $output->getVerbosity();

        // print $verbosity.PHP_EOL;
        // exit;

		// -- get entity manager
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // -- clear media file table
        $tables = array('media_file', 'album', 'artist');

        foreach ($tables as $table) {
            $output->write("  clear table $table .");

            $query = "DELETE FROM $table";
            $statement = $em->getConnection()->prepare($query)->execute();
            $output->write('.');

            $query = "ALTER TABLE $table AUTO_INCREMENT = 1;";
            $statement = $em->getConnection()->prepare($query)->execute();

            $output->writeln('. done');
        }

        /*
         * Scan variables
         */
        // -- folder to scan
        $root = 'web/music/';
        // -- file types
        $types = array("mp3", "mp4", "oga", "wma", "wav", "mpg", "mpc", "m4a", "m4p", "flac");
        // -- db fields
        $required_fields = array('artist', 'title', 'album');
        $optional_fields = array('year', 'genre', 'track_number');
        $fields = array_merge($required_fields, $optional_fields);
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

        // -- scan
        $di = new \RecursiveDirectoryIterator($root,\RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $it = new \RecursiveIteratorIterator($di);

        foreach($it as $file) {
            if ( in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $types) ) {

                $fileCount++;

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
                    $output->write('  <error>no tag found</error>');
                    echo " for ", $file;
                    $output->writeln('-> skipping file.');
                    $skipCount++;
                    continue;
                }

                // -- create tags array
                $tags = array();


                // -- build artist list
                if (!empty($fileInfoComments['artist'])) {

                    $artist = $em->getRepository('AppBundle:Artist')->findByName($fileInfoComments['artist'][0]);

                    if (!$artist) {
                        $artist = new Artist();
                        $artist->setName($fileInfoComments['artist'][0]);
                        $em->persist($artist);
                        $em->flush();
                    }
                }
                else {
                    $output->write('  <error>no artist tag found</error>');
                    echo " for ", $file, PHP_EOL;

                    $output->write('    guessing artist name : ');
                    $dir = pathinfo($file, PATHINFO_DIRNAME);
                    $buff = explode('/', $dir);
                    $tmp = array_pop($buff);
                    if (preg_match('/cd\d+/i', $tmp)) {
                        array_pop($buff);
                    }

                    $tmp = array_pop($buff);
                    if (preg_match('/cd\d+/', $tmp)) {
                        $tmp = array_pop($buff);
                    }

                    $output->writeln($tmp);

                    $output->writeln('-> skipping file.');
                    $skipCount++;
                    continue;
                }


                foreach($fields as $field) {
                    if ( !empty($fileInfoComments[$field]) ) {
                        $tags[$field] = $fileInfoComments[$field][0];
                    }
                }

                // -- check for date tag if no year tag
                if (empty($tags['year'])) {
                    if ( !empty($fileInfoComments['date']) ) {
                        $tags['year'] = $fileInfoComments['date'][0];
                    }
                }

                // echo $fileInfo['playtime_string'], " / " , $fileInfo['playtime_seconds'], PHP_EOL;

                if (!empty($fileInfo['playtime_string'])) {
                    $tags['duration'] = $fileInfo['playtime_string'];
                }
                else {
                    $tags['duration'] = null;
                    $output->write('  <warning>no playtime_string tag found.</warning>');
                    echo " for ", $file, PHP_EOL;
                }


                $tags['web_path'] = preg_replace("/^web/", '', $file);
                $tags['path'] = realpath($file);


                // -- build album list
                if (array_key_exists('artist', $tags) and array_key_exists('album', $tags)) {
                    $album = $em->getRepository('AppBundle:Album')->findBy(array('name' => $tags['album'], 'artist' => $tags['artist']));
                    if (!$album) {
                        $album = new Album();
                        $album->setName($tags['album']);
                        $album->setArtist($tags['artist']);
                        $em->persist($album);
                        $em->flush();
                    }
                }
                else {
                    if (!array_key_exists('artist', $tags)) {
                        print "1 no artist tag found".PHP_EOL;
                    }
                    elseif (!array_key_exists('album', $tags)) {

                        $output->write('  <warning>no album tag found</warning>');
                        echo " for ", $file, PHP_EOL;

                        $output->write('    guessing album name : ');
                        $dir = pathinfo($file, PATHINFO_DIRNAME);
                        $buff = explode('/', $dir);
                        $album = array_pop($buff);
                        if (preg_match('/cd\d+/i', $album)) {
                            $album = array_pop($buff);
                        }
                        $output->writeln($album);
                    }
                }


                if (!empty($tags['track_number'])) {
                    if (\preg_match("/[^\d$]/", $tags['track_number'])) {

                        \preg_match("/(\d+)/", $tags['track_number'], $matches);

                        $tags['track_number'] = $matches[0];
                    }
                }

                if (empty($tags['genre'])) {
                    $tags['genre'] = null;
                }
                if (empty($tags['year'])) {
                    $tags['year'] = null;
                }
                if (empty($tags['track_number'])) {
                    $tags['track_number'] = null;
                }
                if (empty($tags['title'])) {
                    $tags['title'] = null;
                }
                if (empty($tags['album'])) {
                    $tags['album'] = null;
                }

                fwrite(
                    $fp, ",".$tags['path'].",".$tags['title'].",".$tags['album'].",".
                    $tags['artist'].",".$tags['track_number'].",".$tags['year'].",".
                    $tags['genre'].",".$tags['web_path'].",".$tags['duration'].PHP_EOL);

                $loadCount++;

            }
        }
        $query = "LOAD DATA LOCAL INFILE '/home/lpa/atinfo/www/subsonic/web/soonic.sql'  INTO TABLE media_file  FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 ROWS;";
        $statement = $em->getConnection()->prepare($query)->execute();

        $output->writeln("analysed $fileCount files.");
        $output->writeln("loaded $loadCount files");
        $output->writeln("skipped $skipCount files");

        $end_time = microtime(true);
        $duration = $end_time - $start_time;

        if ($duration < 60) {
            $output_duration = gmdate('s\s', $duration);
        }
        elseif ($duration < 3660) {
            $output_duration = gmdate('i\ms\s', $duration);
        }
        else {
            $output_duration = gmdate('H\hi\ms\s', $duration);
        }

        $output->writeln("in $output_duration");

        $output->writeln('<info>done.');
	}
}
