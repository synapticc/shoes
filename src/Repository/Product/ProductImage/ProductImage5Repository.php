<?php

// src/Repository/Product/ProductImage/ProductImage5Repository.php

namespace App\Repository\Product\ProductImage;

use App\Entity\Product\ProductImage\ProductImage5;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage5>
 *
 * @method ProductImage5|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductImage5|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductImage5[]    findAll()
 * @method ProductImage5[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductImage5Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage5::class);
    }

    public function add(ProductImage5 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductImage5 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductImage5[] Returns an array of ProductImage5 objects
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

    //    public function findOneBySomeField($value): ?ProductImage5
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
