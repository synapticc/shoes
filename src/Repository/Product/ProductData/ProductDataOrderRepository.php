<?php

// src/Repository/Product/ProductData/ProductDataOrderRepository.php

namespace App\Repository\Product\ProductData;

use App\Entity\Product\ProductData\ProductDataOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductDataOrder>
 *
 * @method ProductDataOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductDataOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductDataOrder[]    findAll()
 * @method ProductDataOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDataOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductDataOrder::class);
    }

    public function add(ProductDataOrder $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductDataOrder $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductDataOrder[] Returns an array of ProductDataOrder objects
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

    //    public function findOneBySomeField($value): ?ProductDataOrder
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
