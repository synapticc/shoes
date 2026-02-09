<?php

// src/Repository/User/Reset/ResetEmailRequestRepository.php

namespace App\Repository\User\Reset;

use App\Entity\User\Reset\ResetEmailRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResetEmailRequest>
 *
 * @method ResetEmailRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetEmailRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetEmailRequest[]    findAll()
 * @method ResetEmailRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetEmailRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetEmailRequest::class);
    }

    public function add(ResetEmailRequest $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResetEmailRequest $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ResetEmailRequest[] Returns an array of ResetEmailRequest objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ResetEmailRequest
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
