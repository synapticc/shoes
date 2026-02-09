<?php

// src/Repository/User/UserRepository.php

namespace App\Repository\User;

use App\Entity\NoMap\Search\Search;
use App\Entity\User\User;
use App\Entity\User\UserDelete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
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
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Retrieve all users who have activated 'Delete Account',
     * (All users who have 'toBeDeleted' checked to true.).
     */
    public function usersToBeDeactivated()
    {
        // Get current time
        $now = new \DateTime('now', new \DateTimeZone('+4'));
        $query = $this->createQueryBuilder('u');
        $query
            ->innerJoin(
                UserDelete::class,
                's',
                Join::WITH,
                's.users = u.id',
            )
            ->andWhere('s.toBeDeleted = :status')
            ->setParameter('status', true)
            ->andWhere('s.dateDeletion > :date')
            ->setParameter('date', $now)
        ;

        $results = $query->getQuery()->getResult();

        return $results;
    }

    /**
     * Retrieve only active users
     * (All email address not containing the string 'deleted_').
     */
    public function activeUsers()
    {
        $query = $this->createQueryBuilder('u');
        $query
              ->andWhere('u.email NOT LIKE :inactive')
              ->setParameter('inactive', '%deleted_%')
              ->orderBy('u.updated', 'DESC')
        ;

        $results = $query->getQuery()->getResult();

        return $results;
    }

    /**
     * Search only active users (not containing 'deleted_').
     */
    public function searchActive(Search $search, $q)
    {
        // Stop searching the database if the query string is empty.
        if (empty($search->search())) {
            return [];
        }

        $txt = $search->search();
        $isUser = $q->has('_id') ? $q->get('_id') : '';

        if (!empty($isUser)) {
            // Using preg_match_all to extract numbers
            preg_match_all('/\d+/', $txt, $matches);
            // Extracted numbers
            $numbers = $matches[0];
        } else {
            // Remove special characters except space.
            $keywords = preg_replace('/[^-A-Za-z0-9""+_*@. ]/', '', $txt);
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string.
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Convert string into lowercase.
            $keywords = strtolower($keywords);
            // Arrange all words in an array.
            $keywords = explode(' ', $keywords);
        }

        $em = $this->getEntityManager();

        $dql =
          'SELECT u
         FROM App\Entity\User\User u
         LEFT JOIN u.userPhone up
         LEFT JOIN u.userEmail ue
         LEFT JOIN u.userAddress ua
         WHERE u.email NOT LIKE :deleted AND
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('deleted', '%deleted_%');

        if (!empty($isUser)) {
            $query->setDql($query->getDql().' u.id IN (:user)')
                  ->setParameter('user', $numbers);
        } else {
            $query->setDql($query->getDql().' ( ');

            foreach ($keywords as $i => $word) {
                if (end($keywords) != $word) {
                    $query->setDql($query->getDql().
                      "  (ILIKE(u.email, :word$i) = true) OR
                 (ILIKE(ue.email, :word$i) = true) OR
                 (ILIKE(ue.email2, :word$i) = true) OR
                 (ILIKE(u.firstName, :word$i) = true) OR
                 (ILIKE(u.middleName, :word$i) = true) OR
                 (ILIKE(u.lastName, :word$i) = true) OR
                 (ILIKE(ua.street, :word$i) = true) OR
                 (ILIKE(ua.street2, :word$i) = true) OR
                 (ILIKE(ua.city, :word$i) = true) OR
                 (ILIKE(up.mobile, :word$i) = true) OR
                 (ILIKE(up.landline, :word$i) = true) OR
                 ")
                    ->setParameter("word$i", '%'.$word.'%');

                    // if (is_numeric($word))
                    // {
                    //   $number = (int) $word;
                    //   $query
                    //         ->setDql( $query->getDql() .  " ua.zip = :number$i OR ")
                    //         ->setParameter("number$i", $number);
                    // }
                } else {
                    $query->setDql($query->getDql().
                        "  (ILIKE(u.email, :word$i) = true) OR
                   (ILIKE(ue.email, :word$i) = true) OR
                   (ILIKE(ue.email2, :word$i) = true) OR
                   (ILIKE(u.firstName, :word$i) = true) OR
                   (ILIKE(u.middleName, :word$i) = true) OR
                   (ILIKE(u.lastName, :word$i) = true) OR
                   (ILIKE(ua.street, :word$i) = true) OR
                   (ILIKE(ua.street2, :word$i) = true) OR
                   (ILIKE(ua.city, :word$i) = true) OR
                   (ILIKE(up.mobile, :word$i) = true) OR
                   (ILIKE(up.landline, :word$i) = true) ")
                    ->setParameter("word$i", '%'.$word.'%');

                    // if (is_numeric($word) and (strlen($word) < 5))
                    // {
                    //   $number = (int) $word;
                    //   $query
                    //     ->setDql( $query->getDql() .  " OR ua.zip = :number$i ")
                    //     ->setParameter("number$i", $number);
                    // }
                }
            }

            $query->setDql($query->getDql().' ) ');
        }

        if (!empty($search->country())) {
            $country = $search->country();
            $query
              ->setDql($query->getDql().' AND ua.country = :country ')
              ->setParameter('country', $country);
        }

        $results = $query->getResult();

        return $results;
    }

    /**
     *  Retrieve only deleted users (email containing 'deleted_').
     */
    public function findDeletedUsers()
    {
        $query = $this->createQueryBuilder('u');
        $query->andWhere('u.email LIKE :deleted')
              ->setParameter('deleted', '%deleted_%')
        ;

        $results = $query->getQuery()->getResult();

        return $results;
    }

    /**
     * Search only deleted users (email containing 'deleted_').
     */
    public function searchDeleted(Search $search, $q)
    {
        // Stop searching the database if the query string is empty.
        if (empty($search->search())) {
            return [];
        }

        $txt = $search->search();

        // Remove special characters except space.
        $keywords = preg_replace('/[^-A-Za-z0-9""+_*@. ]/', '', $txt);
        // Remove unnecessary space
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        // Strip whitespace (or other characters) from the beginning and end of a string.
        $keywords = trim((string) $keywords, "\x00.. \x1F");
        // Convert string into lowercase.
        $keywords = strtolower($keywords);
        // Arrange all words in an array.
        $keywords = explode(' ', $keywords);

        $isUser = $q->has('_id') ? $q->get('_id') : '';

        $em = $this->getEntityManager();

        $dql =
          'SELECT u
         FROM App\Entity\User\User u
         LEFT JOIN u.userPhone up
         LEFT JOIN u.userEmail ue
         LEFT JOIN u.userAddress ua
         LEFT JOIN u.userDeactivate ud
         WHERE
            ud.deactivate = :active AND
            u.email LIKE :deleted AND
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('active', true)
              ->setParameter('deleted', '%deleted_%');

        if (!empty($isUser)) {
            $query->setDql($query->getDql().' u.id IN (:user)')
                  ->setParameter('user', $keywords);
        } else {
            foreach ($keywords as $i => $word) {
                if (end($keywords) != $word) {
                    $query->setDql($query->getDql().
                      " ((ILIKE(u.email, :word$i) = true) OR
                 (ILIKE(ue.email, :word$i) = true) OR
                 (ILIKE(ue.email2, :word$i) = true) OR
                 (ILIKE(u.firstName, :word$i) = true) OR
                 (ILIKE(u.middleName, :word$i) = true) OR
                 (ILIKE(u.lastName, :word$i) = true) OR
                 (ILIKE(ua.street, :word$i) = true) OR
                 (ILIKE(ua.street2, :word$i) = true) OR
                 (ILIKE(ua.city, :word$i) = true) OR
                 (ILIKE(up.mobile, :word$i) = true) OR
                 (ILIKE(up.landline, :word$i) = true)
                 )
                 OR ")
                          ->setParameter("word$i", '%'.$word.'%');
                } else {
                    $query->setDql($query->getDql().
                        " ((ILIKE(u.email, :word$i) = true) OR
                   (ILIKE(ue.email, :word$i) = true) OR
                   (ILIKE(ue.email2, :word$i) = true) OR
                   (ILIKE(u.firstName, :word$i) = true) OR
                   (ILIKE(u.middleName, :word$i) = true) OR
                   (ILIKE(u.lastName, :word$i) = true) OR
                   (ILIKE(ua.street, :word$i) = true) OR
                   (ILIKE(ua.street2, :word$i) = true) OR
                   (ILIKE(ua.city, :word$i) = true) OR
                   (ILIKE(up.mobile, :word$i) = true) OR
                   (ILIKE(up.landline, :word$i) = true)
                  ) ")
                          ->setParameter("word$i", '%'.$word.'%');
                }
            }
        }

        if (!empty($search->country())) {
            $country = $search->country();
            $query
              ->setDql($query->getDql().' AND ua.country = :country ')
              ->setParameter('country', $country);
        }

        $results = $query->getResult();

        return $results;
    }
}
