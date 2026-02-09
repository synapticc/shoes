<?php

// src/Repository/User/Settings/MaxItemsRepository.php

namespace App\Repository\User\Settings;

use App\Entity\User\Settings\MaxItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<MaxItems>
 */
class MaxItemsRepository extends ServiceEntityRepository
{
    // public function __construct(ManagerRegistry $registry)
    // {
    //     parent::__construct($registry, MaxItems::class);
    // }

    private const DEFAULT_LISTING = 12;
    private const MIN_LISTING = 5;
    private const MAX_LISTING = 75;

    public function __construct(ManagerRegistry $registry, private LoggerInterface $logger)
    {
        parent::__construct($registry, MaxItems::class);
    }

    /**     * Get listing items count with fallback     */
    public function getListingCount(): int
    {
        try {
            $result = (int) $this->createQueryBuilder('m')
            ->select('m.listing')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
            if (null === $result) {
                $this->logger->debug('No max items setting found, using default', ['default' => self::DEFAULT_LISTING]);

                return self::DEFAULT_LISTING;
            }            $listing = $result['listing'] ?? self::DEFAULT_LISTING;
            // Validate range
            if ($listing < self::MIN_LISTING || $listing > self::MAX_LISTING) {
                $this->logger->warning('Invalid listing value, using default', ['provided' => $listing,                    'default' => self::DEFAULT_LISTING]);

                return self::DEFAULT_LISTING;
            }

            return $listing;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching listing count', ['exception' => $e->getMessage()]);

            return self::DEFAULT_LISTING;
        }
    }

    // public function listing(): int
    // {
    //     return (int) $this->createQueryBuilder('m')
    //         ->getQuery()
    //         ->getOneOrNullResult()
    //         ->getListing()
    //     ;
    // }

    public function reviews(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->getQuery()
            ->getOneOrNullResult()
            ->getReviews()
        ;
    }

    public function recent(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->getQuery()
            ->getOneOrNullResult()
            ->getRecent()
        ;
    }

    //    /**
    //     * @return MaxItems[] Returns an array of MaxItems objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MaxItems
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
