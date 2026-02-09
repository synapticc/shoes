<?php

// src/Repository/Supplier/SupplierRepository.php

namespace App\Repository\Supplier;

use App\Entity\NoMap\Search\Search;
use App\Entity\Supplier\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Supplier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supplier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supplier[]    findAll()
 * @method Supplier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private ORM $_em,
    ) {
        parent::__construct($registry, Supplier::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Supplier $entity, bool $flush = true): void
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
    public function remove(Supplier $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Supplier[] Returns an array of Product objects
     */
    public function search(Search $search): array
    {
        if (empty($search->search())) {
            return [];
        }

        // Remove special characters except space
        $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
        // Remove unnecessary space
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        // Strip whitespace (or other characters) from the beginning and end of a string
        $keywords = trim((string) $keywords, "\x00.. \x1F");

        if (!empty($search)) {
            $query = $this
                ->createQueryBuilder('s')
                ->orderBy('s.id', 'ASC')
                ->orWhere('(ILIKE(s.name, :word) = true)')
                ->orWhere('(ILIKE(s.details, :word) = true)')
                ->orWhere('(ILIKE(s.email, :word) = true)')
                ->orWhere('(ILIKE(s.street, :word) = true)')
                ->orWhere('(ILIKE(s.city, :word) = true)')
                ->orWhere('(ILIKE(s.country, :word) = true)')
                ->setParameter('word', "%$keywords%")
            ;

            if (!empty($search->country())) {
                $country = $search->country();
                $query
                  ->andWhere('s.countryCode = :country')
                  ->setParameter('country', $country);
            }

            $results = $query->getQuery()->getResult();

            return $results;
        }
    }
}
