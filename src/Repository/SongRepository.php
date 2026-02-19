<?php

namespace App\Repository;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * @method Song|null find($id, $lockMode = null, $lockVersion = null)
 * @method Song|null findOneBy(array $criteria, array $orderBy = null)
 * @method Song[]    findAll()
 * @method Song[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Song::class);
    }

    /**
     * @return Song[]
     */
    public function findByKeyword(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        try {
            return $this->findByKeywordFullText($keyword);
        } catch (Throwable) {
            // Fallback used when FULLTEXT indexes are missing or unsupported.
            return $this->findByKeywordLike($keyword);
        }
    }

    /**
     * @return Song[]
     */
    private function findByKeywordLike(string $keyword): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.album', 'al')
            ->addSelect('al')
            ->leftJoin('s.artist', 'ar')
            ->addSelect('ar')
            ->where('s.title LIKE :keyword')
            ->orWhere('al.name LIKE :keyword')
            ->orWhere('ar.name LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->orderBy('ar.name', 'ASC')
            ->addOrderBy('al.name', 'ASC')
            ->addOrderBy('s.trackNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Song[]
     */
    private function findByKeywordFullText(string $keyword): array
    {
        $terms = preg_split('/\s+/', mb_strtolower($keyword), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($terms === []) {
            return [];
        }

        $tokens = array_filter($terms, static fn (string $term): bool => mb_strlen($term) >= 2);
        if ($tokens === []) {
            return $this->findByKeywordLike($keyword);
        }

        $booleanQuery = implode(' ', array_map(static fn (string $term): string => sprintf('+%s*', $term), $tokens));

        $connection = $this->getEntityManager()->getConnection();
        $ids = $connection->fetchFirstColumn(
            <<<'SQL'
SELECT s.id
FROM song s
INNER JOIN album al ON al.id = s.album_id
INNER JOIN artist ar ON ar.id = s.artist_id
WHERE MATCH(s.title) AGAINST (:query IN BOOLEAN MODE)
   OR MATCH(al.name) AGAINST (:query IN BOOLEAN MODE)
   OR MATCH(ar.name) AGAINST (:query IN BOOLEAN MODE)
ORDER BY ar.name ASC, al.name ASC, s.track_number ASC
SQL
            ,
            ['query' => $booleanQuery]
        );

        if ($ids === []) {
            return [];
        }

        $ids = array_map('intval', $ids);
        $songs = $this->createQueryBuilder('s')
            ->leftJoin('s.album', 'al')
            ->addSelect('al')
            ->leftJoin('s.artist', 'ar')
            ->addSelect('ar')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $orderMap = array_flip($ids);
        usort(
            $songs,
            static fn (Song $a, Song $b): int => ($orderMap[$a->getId()] ?? PHP_INT_MAX) <=> ($orderMap[$b->getId()] ?? PHP_INT_MAX)
        );

        return $songs;
    }

    /**
     * @return Song[]
     */
    public function findByArtistAndAlbum(string $artist, string $album): array
    {
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

    /**
     * @return Song[]
     */
    public function getRandom(int $number): array
    {
        if ($number <= 0) {
            return [];
        }

        $maxId = (int) $this->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if (!$maxId) {
            $maxId = 1;
        }

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.album', 'al')
            ->addSelect('al')
            ->leftJoin('s.artist', 'ar')
            ->addSelect('ar')
            ;


        $randoms = [];

        if ($maxId < $number) {
            $number = $maxId;
        }

        for ($i = 1; $i <= $number; ++$i) {
            $qb->orWhere('s.id = :num_'.$i);

            $random = random_int(1, $maxId);
            while (in_array($random, $randoms, true)) {
                $random = random_int(1, $maxId);
            }
            array_push($randoms, $random);

            $qb->setParameter('num_'.$i, $random);
        }

        return $qb->getQuery()->getResult();
    }
}
