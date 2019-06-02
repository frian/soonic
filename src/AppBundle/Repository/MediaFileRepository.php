<?php
// src/AppBundle/Repository/MediaFileRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MediaFileRepository extends EntityRepository
{
    public function findByArtist($artist) {
        return $this->createQueryBuilder('s')
            ->where('s.artist = :artist')
            ->setParameter('artist', $artist)
            ->orderBy('s.album', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAlbum($artist, $album) {
        return $this->createQueryBuilder('s')
            ->where('s.artist = :artist')
            ->setParameter('artist', $artist)
            ->andWhere('s.album = :album')
            ->setParameter('album', $album)
            ->orderBy('s.trackNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByKeyword($keyword) {
        return $this->createQueryBuilder('s')
            ->where('s.album like :keyword')
            ->orWhere('s.title like :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->orderBy('s.artist', 'ASC')
            ->addOrderBy('s.album', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
