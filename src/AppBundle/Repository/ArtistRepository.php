<?php
// src/AppBundle/Repository/ArtistRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ArtistRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }

    public function findByFilter($filter)
    {
        return $this->createQueryBuilder('a')
            ->where('a.name like :filter')
            ->setParameter('filter', '%'.$filter.'%')
            // ->andWhere('a.title != :null')
            // ->setParameter('null', serialize(null))
            // ->orderBy('a.album', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
