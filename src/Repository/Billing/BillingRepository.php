<?php

// src/Repository/Billing/BillingRepository.php

namespace App\Repository\Billing;

use App\Entity\Billing\Billing;
use App\Entity\NoMap\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Billing>
 */
class BillingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billing::class);
    }

    /**
     * @return Order[] returns an array of Order arrays
     *                 and NOT Order objects
     */
    public function invoices($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'b.purchaseDate';
        $customer = $q->has('customer') ? $q->get('customer') : '';

        $dql =
        'SELECT
          o.order_id, b.purchaseDate, b.invoiceTotal as total,
          b.id, b.invoicePath, b.invoiceThumbnail as thumbnail,
          u.id as userId,u.email, u.title, u.firstName, u.lastName,
          ui.image as pic

         FROM App\Entity\Billing\Billing b
         LEFT JOIN b.orderBilling o
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
        ';

        if (!empty($customer)) {
            $dql .= ' WHERE u.id = :customer ';
        }

        $dql .= " ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        if (!empty($customer)) {
            $query->setParameter('customer', $customer);
        }
        $results = $query->getResult();

        return $results;
    }

    public function search(Search $search, $q): array
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'b.purchaseDate';

        // Remove special characters except space
        $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
        // Remove unnecessary space
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        // Strip whitespace (or other characters) from the beginning and end of a string
        $keywords = trim((string) $keywords, "\x00.. \x1F");
        // Arrange all words in an array
        $keywords = explode(' ', $keywords);

        $condition = ' (';
        foreach ($keywords as $i => $word) {
            if (end($keywords) == $word) {
                $condition .= "
              (ILIKE(u.email, :word$i) = true) OR
              (ILIKE(u.firstName, :word$i) = true) OR
              (ILIKE(u.lastName, :word$i) = true) OR

              (ILIKE(pr.name, :word$i) = true) OR
              (ILIKE(pr.description, :word$i) = true) OR
              (ILIKE(pc.color, :word$i) = true)
              )
              ";
            } else {
                $condition .= "
              (ILIKE(u.email, :word$i) = true) OR
              (ILIKE(u.firstName, :word$i) = true) OR
              (ILIKE(u.lastName, :word$i) = true) OR

              (ILIKE(pr.name, :word$i) = true) OR
              (ILIKE(pr.description, :word$i) = true) OR
              (ILIKE(pc.color, :word$i) = true) OR
              ";
            }
        }

        if (!empty($search->startDate()) and !empty($search->startDate())) {
            $condition .= ' AND b.purchaseDate BETWEEN :startDate AND :endDate ';
        }

        $dql = 'SELECT DISTINCT o.order_id,
          b.purchaseDate, b.invoiceTotal as total,
          b.id, b.invoicePath, b.invoiceThumbnail as thumbnail,
          u.id as userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic

         FROM App\Entity\Billing\Billing b

         LEFT JOIN b.orderBilling o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui

         WHERE '.$condition;

        $dql .= " ORDER BY $sort $order ";
        $query = $em->createQuery($dql);

        if (!empty($search->startDate()) and !empty($search->startDate())) {
            $query
              ->setParameter('startDate', $search->startDate()->format('Y-m-d H:i:s'))
              ->setParameter('endDate', $search->endDate()->format('Y-m-d H:i:s'));
        }

        foreach ($keywords as $i => $word) {
            $query->setParameter("word$i", '%'.strtolower($word).'%');
        }

        $result = [];
        $result = $query->getResult();

        return $result;
    }

    //    /**
    //     * @return Billing[] Returns an array of Billing objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Billing
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
