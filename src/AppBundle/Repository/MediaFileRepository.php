<?php
// src/AppBundle/Repository/MediaFileRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MediaFileRepository extends EntityRepository
{
    public function findByArtist($artist)
    {
        $query = $this->createQueryBuilder('s')
            ->where('s.artist LIKE :artist')
            ->setParameter('artist', '%'.$artist.'%')
            ->orderBy('s.album', 'ASC')
            ->getQuery();

        $products = $query->getResult();
        return $products;
    }
}
