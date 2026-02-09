<?php

// src/Repository/Product/ProductData/SimilarProductDataRepository.php

namespace App\Repository\Product\ProductData;

use App\Entity\Product\ProductData\SimilarProductData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SimilarProductData>
 *
 * @method SimilarProductData|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimilarProductData|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimilarProductData[]    findAll()
 * @method SimilarProductData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimilarProductDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimilarProductData::class);
    }

    public function add(SimilarProductData $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SimilarProductData $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return SimilarProductData[] Returns an array of SimilarProductData objects
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

    //    public function findOneBySomeField($value): ?SimilarProductData
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
