<?php

// src/Repository/Product/ProductColor/ProductColorTagsRepository.php

namespace App\Repository\Product\ProductColor;

use App\Entity\Product\ProductColor\ProductColorTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductColorTags>
 *
 * @method ProductColorTags|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductColorTags|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductColorTags[]    findAll()
 * @method ProductColorTags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductColorTagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductColorTags::class);
    }

    public function add(ProductColorTags $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductColorTags $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ProductColorTags[] Returns an array of ProductColorTags objects
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

    //    public function findOneBySomeField($value): ?ProductColorTags
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
