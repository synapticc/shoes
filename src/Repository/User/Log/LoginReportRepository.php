<?php

// src/Repository/User/Log/LoginReportRepository.php

namespace App\Repository\User\Log;

use App\Entity\NoMap\Search\Search;
use App\Entity\User\Log\LoginReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginReport>
 *
 * @method LoginReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginReport[]    findAll()
 * @method LoginReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginReport::class);
    }

    public function add(LoginReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LoginReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return LoginReport[] Returns an array of LoginReport arrays
     */
    public function login($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'lg.created';
        $user = $q->has('user') ? $q->get('user') : '';

        $dql =
        'SELECT
          lg.id, lg.userAgent, lg.ipAddress, lg.created as login,
          lo.created as logout,
          u.id as userId, u.email
         FROM App\Entity\User\Log\LoginReport lg
         LEFT JOIN lg.users u
         LEFT JOIN lg.logoutReport lo
         ';

        if (!empty($user)) {
            $dql .= ' WHERE u.id = :user ';
        }

        $dql .= " ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        if (!empty($user)) {
            $query->setParameter('user', $user);
        }

        $result = $query->getResult();

        return $result;
    }

    /**
     * @return LoginReport[] Returns an array of LoginReport arrays
     */
    public function search(Search $search, $q)
    {
        // Stop searching the database if the query string is empty.
        if (empty($search->search())) {
            return [];
        }

        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'lg.created';
        $isUser = $q->has('_id') ? $q->get('_id') : '';

        $txt = $search->search();
        // Remove special characters except space.
        $keywords = preg_replace('/[^A-Za-z0-9""+* ]/', '', $txt);
        // Remove unnecessary space
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        // Strip whitespace (or other characters) from the beginning and end of a string.
        $keywords = trim((string) $keywords, "\x00.. \x1F");
        // Convert string into lowercase.
        $keywords = strtolower($keywords);
        // Arrange all words in an array.
        $keywords = explode(' ', $keywords);

        $dql =
        'SELECT
          lg.id, lg.userAgent, lg.ipAddress, lg.created as login,
          lo.created as logout,
          u.id as userId, u.email
         FROM App\Entity\User\Log\LoginReport lg
         LEFT JOIN lg.users u
         LEFT JOIN lg.logoutReport lo
         WHERE
         ';

        $query = $em->createQuery($dql);
        if (!empty($isUser)) {
            $query->setDql($query->getDql().' u.id IN (:user)')
                  ->setParameter('user', $keywords);
        } else {
            foreach ($keywords as $i => $word) {
                if (end($keywords) != $word) {
                    $query->setDql($query->getDql().
                      " ((ILIKE(u.email, :word$i) = true) OR
                   (ILIKE(u.firstName, :word$i) = true) OR
                   (ILIKE(u.middleName, :word$i) = true) OR
                   (ILIKE(u.lastName, :word$i) = true)
                   )
                   OR ")
                          ->setParameter("word$i", '%'.$word.'%');
                } else {
                    $query->setDql($query->getDql().
                        " ((ILIKE(u.email, :word$i) = true) OR
                     (ILIKE(u.firstName, :word$i) = true) OR
                     (ILIKE(u.middleName, :word$i) = true) OR
                     (ILIKE(u.lastName, :word$i) = true)
                     ) ")
                          ->setParameter("word$i", '%'.$word.'%');
                }
            }
        }

        // $query->setDql( $query->getDql() . " ORDER BY :srt :ord")
        //       ->setParameter("srt", $sort)
        //       ->setParameter("ord", $order)
        //       ;

        $result = $query->getResult();

        return $result;
    }
}
