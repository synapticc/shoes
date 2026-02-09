<?php

// src/Repository/Product/ProductImage/ProductImage3Repository.php

namespace App\Repository\Product\ProductImage;

use App\Entity\Product\ProductImage\ProductImage3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage3>
 *
 * @method ProductImage3|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductImage3|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductImage3[]    findAll()
 * @method ProductImage3[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductImage3Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage3::class);
    }

    public function add(ProductImage3 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductImage3 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductImage3[] Returns an array of ProductImage3 objects
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

    //    public function findOneBySomeField($value): ?ProductImage3
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
