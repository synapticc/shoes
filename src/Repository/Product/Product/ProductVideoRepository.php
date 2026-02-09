<?php

// src/Repository/Product/Product/ProductVideoRepository.php

namespace App\Repository\Product\Product;

use App\Entity\Product\Product\ProductVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductVideo>
 *
 * @method ProductVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVideo[]    findAll()
 * @method ProductVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVideo::class);
    }

    public function add(ProductVideo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductVideo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
