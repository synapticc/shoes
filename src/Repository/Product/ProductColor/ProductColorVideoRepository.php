<?php

// src/Repository/Product/ProductColor/ProductColorVideoRepository.php

namespace App\Repository\Product\ProductColor;

use App\Entity\Product\ProductColor\ProductColorVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductColorVideo>
 *
 * @method ProductColorVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductColorVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductColorVideo[]    findAll()
 * @method ProductColorVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductColorVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductColorVideo::class);
    }

    public function add(ProductColorVideo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductColorVideo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductColorVideo[] Returns an array of ProductColorVideo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProductColorVideo
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
