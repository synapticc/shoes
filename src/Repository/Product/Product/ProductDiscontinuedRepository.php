<?php

// src/Repository/Product/Product/ProductDiscontinuedRepository.php

namespace App\Repository\Product\Product;

use App\Entity\Product\Product\ProductDiscontinued;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductDiscontinued>
 *
 * @method ProductDiscontinued|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductDiscontinued|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductDiscontinued[]    findAll()
 * @method ProductDiscontinued[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDiscontinuedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductDiscontinued::class);
    }

    public function add(ProductDiscontinued $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductDiscontinued $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
