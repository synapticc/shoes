<?php

// src/Repository/Product/Product/ProductQtyPackRepository.php

namespace App\Repository\Product\Product;

use App\Entity\Product\Product\ProductQtyPack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductQtyPack>
 *
 * @method ProductQtyPack|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductQtyPack|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductQtyPack[]    findAll()
 * @method ProductQtyPack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductQtyPackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductQtyPack::class);
    }

    public function add(ProductQtyPack $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductQtyPack $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
