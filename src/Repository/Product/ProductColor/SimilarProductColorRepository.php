<?php

// src/Repository/Product/ProductColor/SimilarProductColorRepository.php

namespace App\Repository\Product\ProductColor;

use App\Entity\Product\ProductColor\SimilarProductColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SimilarProductColor>
 *
 * @method SimilarProductColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimilarProductColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimilarProductColor[]    findAll()
 * @method SimilarProductColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimilarProductColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimilarProductColor::class);
    }

    public function add(SimilarProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SimilarProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return SimilarProductColor[] Returns an array of SimilarProductColor objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SimilarProductColor
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
