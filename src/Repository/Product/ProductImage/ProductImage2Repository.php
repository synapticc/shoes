<?php

// src/Repository/Product/ProductImage/ProductImage2Repository.php

namespace App\Repository\Product\ProductImage;

use App\Entity\Product\ProductImage\ProductImage2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage2>
 *
 * @method ProductImage2|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductImage2|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductImage2[]    findAll()
 * @method ProductImage2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductImage2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage2::class);
    }

    public function add(ProductImage2 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductImage2 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductImage2[] Returns an array of ProductImage2 objects
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

    //    public function findOneBySomeField($value): ?ProductImage2
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
