<?php
// src/AppBundle/Repository/AlbumRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AlbumRepository extends EntityRepository
{
    public function findByArtist($artist)
    {
        return $this->createQueryBuilder('a')
            ->where('a.artist = :artist')
            ->setParameter('artist', $artist)
            ->andWhere('a.name != :null')
            ->setParameter('null', serialize(null))
            ->orderBy('a.year', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
