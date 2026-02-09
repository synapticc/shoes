<?php

// src/Repository/User/Log/LogoutReportRepository.php

namespace App\Repository\User\Log;

use App\Entity\User\Log\LogoutReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogoutReport>
 *
 * @method LogoutReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogoutReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogoutReport[]    findAll()
 * @method LogoutReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogoutReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogoutReport::class);
    }

    public function add(LogoutReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LogoutReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return LogoutReport[] Returns an array of LogoutReport objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LogoutReport
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
