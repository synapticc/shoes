<?php

// src/Repository/Product/Product/ProductPricingRepository.php

namespace App\Repository\Product\Product;

use App\Entity\Product\Product\ProductPricing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductPricing>
 *
 * @method ProductPricing|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductPricing|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductPricing[]    findAll()
 * @method ProductPricing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPricingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductPricing::class);
    }

    public function add(ProductPricing $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductPricing $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
