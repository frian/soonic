<?php

namespace App\Tests\Support;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Song;
use Doctrine\ORM\EntityManagerInterface;

final class MusicDatasetSeeder
{
    public static function seed(EntityManagerInterface $entityManager): void
    {
        $artist = (new Artist())
            ->setName('DIRE STRAITS')
            ->setArtistSlug('dire-straits')
            ->setAlbumCount(1)
            ->setCoverArtPath(null);

        $album = (new Album())
            ->setName('Dire Straits')
            ->setAlbumSlug('dire-straits')
            ->setSongCount(25)
            ->setDuration('59:59')
            ->setYear(1978)
            ->setGenre('Rock')
            ->setPath('/music/dire-straits')
            ->setCoverArtPath(null);

        $album->addArtist($artist);

        $entityManager->persist($artist);
        $entityManager->persist($album);

        for ($i = 1; $i <= 25; ++$i) {
            $title = $i === 1 ? 'SULTANS OF SWING' : sprintf('DIRE STRAITS TRACK %02d', $i);
            $song = (new Song())
                ->setPath(sprintf('/tmp/dire-straits/%02d.mp3', $i))
                ->setWebPath(sprintf('/music/dire-straits/%02d.mp3', $i))
                ->setTitle($title)
                ->setTrackNumber($i)
                ->setYear(1978)
                ->setGenre('Rock')
                ->setDuration('03:30')
                ->setArtist($artist)
                ->setAlbum($album);

            $entityManager->persist($song);
        }

        $entityManager->flush();
    }
}
