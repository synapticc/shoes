<?php

// src/Repository/Product/ProductImage/ProductImage4Repository.php

namespace App\Repository\Product\ProductImage;

use App\Entity\Product\ProductImage\ProductImage4;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage4>
 *
 * @method ProductImage4|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductImage4|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductImage4[]    findAll()
 * @method ProductImage4[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductImage4Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage4::class);
    }

    public function add(ProductImage4 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductImage4 $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductImage4[] Returns an array of ProductImage4 objects
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

    //    public function findOneBySomeField($value): ?ProductImage4
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
