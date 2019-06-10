<?php
// src/AppBundle/Repository/MediaFileRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MediaFileRepository extends EntityRepository {

    public function findByArtistAndAlbum($artist, $album) {
        return $this->createQueryBuilder('s')
            ->join('s.artist', 'ar')
            ->join('s.album', 'al')
            ->where('ar.name = :artist')
            ->andWhere('al.name = :album')
            ->setParameter('artist', $artist)
            ->setParameter('album', $album)
            ->orderBy('s.trackNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByKeyword($keyword) {
        return $this->createQueryBuilder('s')
            ->join('s.album', 'al')
            ->join('s.artist', 'ar')
            ->where('s.title like :keyword')
            ->orWhere('al.name like :keyword')
            ->orWhere('ar.name like :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->orderBy('s.artist', 'ASC')
            ->addOrderBy('s.album', 'ASC')
            ->addOrderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
