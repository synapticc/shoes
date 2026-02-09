<?php

// src/Repository/Review/ReviewHelpfulRepository.php

namespace App\Repository\Review;

use App\Entity\Review\ReviewHelpful;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException as NoResult;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping as Map;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReviewHelpful|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReviewHelpful|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReviewHelpful[]    findAll()
 * @method ReviewHelpful[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewHelpfulRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewHelpful::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ReviewHelpful $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(ReviewHelpful $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function helpful($reviews)
    {
        // $add = 'addScalarResult'; $set = 'setParameter';
        // $create = 'createNativeQuery'; $result = 'getScalarResult';
        //
        // $sql =
        //  "SELECT h.is_helpful AS \"helpful\"
        //   FROM review_helpful h
        //   INNER JOIN product_reviews r
        //
        //   WHERE r.rvw_id IN (:reviews)
        //   AND h.is_helpful = :status
        //   ";
        //
        // $map = new Map();
        // $map->$add('count', 'count')
        //     ->$add('rvw_id', 'id');
        //
        // $query = $em->$create($sql, $map);
        // $helpfulCount =
        //   $query->$set('status', true)
        //         ->$set('reviews', $reviews)
        //         ->$result();

        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(h) AS count, r.id
         FROM App\Entity\Review\ReviewHelpful h
         INNER JOIN h.review r
         WHERE h.isHelpful = :status
         AND r.id IN (:reviews)
         GROUP BY r.id
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('status', true)
              ->setParameter('reviews', $reviews);

        $initial = $query->getScalarResult();

        $result = [];
        foreach ($initial as $i => $review) {
            $result[$review['id']] = $review['count'];
        }

        return $result;
    }

    public function notHelpful($reviews)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(h) AS count, r.id
         FROM App\Entity\Review\ReviewHelpful h
         LEFT JOIN h.review r
         WHERE h.isHelpful = :status
         AND r.id IN (:reviews)
         GROUP BY r.id
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('status', false)
              ->setParameter('reviews', $reviews);

        $initial = $query->getScalarResult();

        $result = [];
        foreach ($initial as $i => $review) {
            $result[$review['id']] = $review['count'];
        }

        return $result;
    }

    public function helpfulCount(int $review)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(h) AS COUNT
         FROM App\Entity\Review\ReviewHelpful h
         INNER JOIN h.review r
         WHERE r.id = :review
         AND h.isHelpful = :status
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('review', $review)
              ->setParameter('status', true);
        $result = $query->getSingleScalarResult();

        if (0 === $result) {
            $result = '';
        }

        return $result;
    }

    public function notHelpfulCount($reviews)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(h) AS COUNT
         FROM App\Entity\Review\ReviewHelpful h
         INNER JOIN h.review r
         WHERE r.id = :review
         AND h.isHelpful = :status
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('review', $reviews)
              ->setParameter('status', false);

        $result = $query->getSingleScalarResult();

        if (0 === $result) {
            $result = '';
        }

        return $result;
    }

    public function checkHelpful(int $review, int $user)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(h) AS COUNT
         FROM App\Entity\Review\ReviewHelpful h
         INNER JOIN h.review r
         INNER JOIN h.users  u
         WHERE r.id = :review
         AND u.id = :user
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('review', $review)
              ->setParameter('user', $user);
        $result = $query->getOneOrNullResult()['COUNT'];

        return (0 !== $result) ? true : false;
    }

    public function checkHelpfuls($reviews, $user)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT r.id, h.id AS idh, h.isHelpful
         FROM App\Entity\Review\ReviewHelpful h
         INNER JOIN h.review r
         INNER JOIN h.users  u
         WHERE r.id IN (:reviews)
         AND u.id = :user
         ';
        $query = $em->createQuery($dql);
        $query->setParameter('reviews', $reviews)
              ->setParameter('user', $user);

        $initial = $query->getResult();

        $result = [];
        foreach ($initial as $i => $review) {
            $result[$review['id']]['id'] = $review['idh'];
            $result[$review['id']]['helpful'] = $review['isHelpful'];
        }

        return $result;
    }

    public function updateHelpful(int $id)
    {
        $em = $this->getEntityManager();
        $entity = 'App\Entity\Review\ReviewHelpful';
        $unhelpful = false;

        $dql =
        "SELECT h.isHelpful
         FROM $entity h
         WHERE h.id = :id";

        try {
            $vote =
             $em->createQuery($dql)
                ->setParameter('id', $id)
                ->setMaxResults(1)
                ->getResult(Query::HYDRATE_SINGLE_SCALAR);
        } catch (NoResult $e) {
            return ['updatedRow' => false, 'unhelpful' => $unhelpful];
        }

        if (false === $vote) {
            $unhelpful = true;
        }

        if ((null === $vote) or (false === $vote)) {
            $vote = true;
        } else {
            $vote = null;
        }

        $timezone = new \DateTimeZone('+04:00');
        $updated = new \DateTime('now', $timezone);

        $dql = "
          UPDATE $entity h
          SET h.isHelpful = :vote,
              h.updated = :updated
          WHERE h.id = :id";

        $query = $em->createQuery($dql);
        $query->setParameter('vote', $vote)
              ->setParameter('updated', $updated)
              ->setParameter('id', $id);

        $updatedRow = $query->execute(); // Returns number of updated rows

        return ['updatedRow' => $updatedRow, 'unhelpful' => $unhelpful];

        // $entityManager->clear();
    }

    // /**
    //  * @return ReviewHelpful[] Returns an array of ReviewHelpful objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReviewHelpful
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
