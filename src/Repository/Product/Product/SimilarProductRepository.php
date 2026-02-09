<?php

// src/Repository/Product/Product/SimilarProductRepository.php

namespace App\Repository\Product\Product;

use App\Entity\Product\Product\SimilarProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SimilarProduct>
 *
 * @method SimilarProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimilarProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimilarProduct[]    findAll()
 * @method SimilarProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimilarProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimilarProduct::class);
    }

    public function add(SimilarProduct $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SimilarProduct $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
