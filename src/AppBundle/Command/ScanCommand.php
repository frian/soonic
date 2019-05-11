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

class ScanCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
    		->setName('soonic:scan')
    		->setDescription('scan folders')
    		->setHelp("\nscan folders and create database\n")
    		->addOption('force', null, InputOption::VALUE_NONE, 'Si définie, les modifications sont appliquées')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		// -- add style
		$style = new OutputFormatterStyle('red');
		$output->getFormatter()->setStyle('warning', $style);

		// -- get verbosity
		$verbosity = $output->getVerbosity();

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
        $root = 'web/music/misc';
        // -- file types
        $types = array("mp3", "wma", "wav", "mpg", "mpc", "m4a", "m4p", "flac");
        // -- db fields
        $required_fields = array('artist', 'title', 'album');
        $optional_fields = array('year', 'genre', 'track_number');
        $fields = array_merge($required_fields, $optional_fields);


        $query = "INSERT INTO media_file (artist, title, album, year, genre, track_number, path, web_path) VALUES (?,?,?,?,?,?,?,?)";

        $statement = $em->getConnection()->prepare($query);


        // -- scan
        $di = new \RecursiveDirectoryIterator($root,\RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $it = new \RecursiveIteratorIterator($di);

        foreach($it as $file) {
            if ( in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $types) ) {

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
                    echo $file, PHP_EOL;
                    $output->writeln('<warning>no tag found.');
                    continue;
                }


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
                    echo $file, PHP_EOL;
                    $output->writeln('<warning>no artist tag found.');
                    continue;
                }


                // -- create tags array
                $tags = array();

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

                $tags['web_path'] = preg_replace("/^web/", '', $file);
                $tags['path'] = realpath($file);

                // foreach ($tags as $tag => $value) {
                //     printf("%-12s : %s".PHP_EOL, $tag, $value);
                // }

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
                        print "no artist tag found".PHP_EOL;
                    }
                    elseif (!array_key_exists('album', $tags)) {
                        print "no album tag found".PHP_EOL;
                    }
                    echo $file, PHP_EOL;
                    print "no artist tag found".PHP_EOL;
                    print_r($fileInfo);
                }


                // INSERT INTO table_name (column1, column2, column3, ...)
                // VALUES (value1, value2, value3, ...);

                // $query = "INSERT INTO media_file (artist, title, album, year, genre, track_number, path, web_path) VALUES (?,?,?,?,?,?,?,?)";
                //
                // $statement = $em->getConnection()->prepare($query);

                // $statement->bindParam("sssiss", $tags['artist'], $tags['title'], $tags['album'], $tags['year'], $tags['genre'], $tags['track_number']);
// $tags['track_number'] = 0;

                if (!empty($tags['track_number'])) {
                    if (\preg_match("/[^\d$]/", $tags['track_number'])) {

                        \preg_match("/(\d+)/", $tags['track_number'], $matches);

                        $tags['track_number'] = $matches[0];
                    }
                }


                $statement->bindParam(1, $tags['artist'], \PDO::PARAM_STR);
                $statement->bindParam(2, $tags['title'], \PDO::PARAM_STR);
                $statement->bindParam(3, $tags['album'], \PDO::PARAM_STR);
                $statement->bindParam(4, $tags['year'], \PDO::PARAM_INT);
                $statement->bindParam(5, $tags['genre'], \PDO::PARAM_STR);
                $statement->bindParam(6, $tags['track_number'], \PDO::PARAM_STR);
                $statement->bindParam(7, $tags['path'], \PDO::PARAM_STR);
                $statement->bindParam(8, $tags['web_path'], \PDO::PARAM_STR);
                $statement->execute();

                // $mediaFile = new MediaFile();
                // $mediaFile->setArtist($tags['artist']);
                //
                // foreach ($required_fields as $field) {
                //     if (array_key_exists($field, $tags)) {
                //         $method = 'set' . ucfirst($field);
                //         $mediaFile->$method($tags[$field]);
                //     }
                //     else {
                //         print $tags['web_path'].PHP_EOL;
                //         print "no $field tag found".PHP_EOL;
                //     }
                // }
                //
                //
                // foreach ($optional_fields as $field) {
                //     if (array_key_exists($field, $tags)) {
                //
                //         // -- change field_name to fieldName for methos name
                //         if (strpos($field, '_') !== false) {
                //             $buff = explode('_', $field);
                //             $buff[1] = ucfirst($buff[1]);
                //             $methodName = implode('', $buff);
                //             $method = 'set' . ucfirst($methodName);
                //         }
                //         else {
                //             $method = 'set' . ucfirst($field);
                //         }
                //
                //         $mediaFile->$method($tags[$field]);
                //     }
                //     else {
                //         // print $tags['web_path'].PHP_EOL;
                //         // print "no $field tag found".PHP_EOL;
                //     }
                // }
                //
                // $mediaFile->setPath(realpath($file));
                // $mediaFile->setWebPath(preg_replace("/^web/", '', $file));
                //
                //
                // $em->persist($mediaFile);
                // $em->flush();
            }
        }
        $output->writeln('<info>done.');
	}
}
