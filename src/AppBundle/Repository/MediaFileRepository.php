<?php
// src/AppBundle/Repository/MediaFileRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MediaFileRepository extends EntityRepository
{
    public function findByArtist($artist)
    {
        return $this->createQueryBuilder('s')
            ->where('s.artist = :artist')
            ->setParameter('artist', $artist)
            ->andWhere('s.title != :null')
            ->setParameter('null', serialize(null))
            ->orderBy('s.album', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAlbum($artist, $album)
    {
        return $this->createQueryBuilder('s')
            ->where('s.artist = :artist')
            ->setParameter('artist', $artist)
            ->andWhere('s.album = :album')
            ->setParameter('album', $album)
            ->andWhere('s.title != :null')
            ->setParameter('null', serialize(null))
            ->orderBy('s.trackNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
