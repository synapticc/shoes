<?php

// src/Repository/Product/ProductColor/ExcludeColorRepository.php

namespace App\Repository\Product\ProductColor;

use App\Entity\Product\ProductColor\ExcludeColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExcludeColor>
 *
 * @method ExcludeColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExcludeColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExcludeColor[]    findAll()
 * @method ExcludeColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcludeColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcludeColor::class);
    }

    public function add(ExcludeColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExcludeColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ExcludeColor[] Returns an array of ExcludeColor objects
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

    //    public function findOneBySomeField($value): ?ExcludeColor
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
