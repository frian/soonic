<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use AppBundle\Entity\MediaFile;

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
        $query = 'DELETE FROM media_file';
        $statement = $em->getConnection()->prepare($query)->execute();

        $query = 'ALTER TABLE media_file AUTO_INCREMENT = 1;';
        $statement = $em->getConnection()->prepare($query)->execute();


        // -- scan variables
        $root = 'web/music/albums';
        $types = array("mp3", "wma", "wav", "mpg", "mpc", "m4a", "m4p", "flac");
        $fields = array('artist', 'title', 'album', 'year', 'genre', 'track_number', );
        $artists = array();


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
                    // --build artist list
                    if (!empty($fileInfo['comments']['artist'])) {
                        if (!in_array($fileInfo['comments']['artist'][0], $artists)) {
                            array_push($artists, $fileInfo['comments']['artist'][0]);
                        }
                    }

                    $tags = array();

                    foreach($fields as $field) {
                        if ( !empty($fileInfo['comments'][$field]) ) {
                            $tags[$field] = $fileInfo['comments'][$field][0];
                        }
                    }
                }
                else {

                    if (!empty($fileInfo['asf']['comments'])) {
                        // --build artist list
                        if (!empty($fileInfo['asf']['comments']['artist'])) {
                            if (!in_array($fileInfo['asf']['comments']['artist'][0], $artists)) {
                                array_push($artists, $fileInfo['asf']['comments']['artist'][0]);
                            }
                        }

                        $tags = array();

                        foreach($fields as $field) {
                            if ( !empty($fileInfo['asf']['comments'][$field]) ) {
                                $tags[$field] = $fileInfo['asf']['comments'][$field][0];
                            }
                        }
                    }
                    else {
                        echo $file, PHP_EOL;
                        $output->writeln('<warning>no tag found.');
                        // print_r($fileInfo);
                    }

                }


                if (empty($tags['year'])) {
                    if ( !empty($fileInfo['comments']['date']) ) {
                        $tags['year'] = $fileInfo['comments']['date'][0];
                    }
                }


                $tags['web_path'] = preg_replace("/^web/", '', $file);
                $tags['path'] = realpath($file);


                // foreach ($tags as $tag => $value) {
                //     printf("%-12s : %s".PHP_EOL, $tag, $value);
                // }


                $mediaFile = new MediaFile();
                $mediaFile->setArtist($tags['artist']);
                $mediaFile->setTitle($tags['title']);
                $mediaFile->setAlbum($tags['album']);

                if (array_key_exists('year', $tags)) {
                    $mediaFile->setYear($tags['year']);
                }

                if (array_key_exists('genre', $tags)) {
                    $mediaFile->setGenre($tags['genre']);
                }

                if (array_key_exists('track_number', $tags)) {
                    $mediaFile->setTrackNumber($tags['track_number']);
                }

                $mediaFile->setPath(realpath($file));
                $mediaFile->setWebPath(preg_replace("/^web/", '', $file));


                $em->persist($mediaFile);
                $em->flush();

                echo PHP_EOL;

            }
        }



		// if --force persist
		// if ($input->getOption('force')) {
		// 	$em->flush();
		// }


		// $output->writeln('nothing to do');
        $output->writeln('<info>done.');
        // $output->writeln('<warning>done.');
	}
}
