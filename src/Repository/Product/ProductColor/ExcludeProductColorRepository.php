<?php

// src/Repository/Product/ProductColor/ExcludeProductColorRepository.php

namespace App\Repository\Product\ProductColor;

use App\Entity\Product\ProductColor\ExcludeProductColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExcludeProductColor>
 *
 * @method ExcludeProductColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExcludeProductColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExcludeProductColor[]    findAll()
 * @method ExcludeProductColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcludeProductColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcludeProductColor::class);
    }

    public function add(ExcludeProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExcludeProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ExcludeProductColor[] Returns an array of ExcludeProductColor objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ExcludeProductColor
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
