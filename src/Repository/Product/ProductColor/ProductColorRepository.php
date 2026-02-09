<?php

// src/Repository/Product/ProductColor/ProductColorRepository.php

namespace App\Repository\Product\ProductColor;

use App\Controller\_Utils\Attributes;
use App\Entity\Product\ProductColor\ProductColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductColor>
 *
 * @method ProductColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductColor[]    findAll()
 * @method ProductColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductColorRepository extends ServiceEntityRepository
{
    use Attributes;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductColor::class);
    }

    public function add(ProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductColor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByColor(?int $id)
    {
        $query = $this->createQueryBuilder('pc');
        $query->andWhere('pc.id = :id')
              ->setParameter('id', $id);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param int $pc ProductColor ID
     *
     * @return ProductColor Returns an array version of the ProductColor
     */
    public function fetch(int $pc)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT
          pr.id, pr.name, pr.brand,
          pr.category,pr.occasion, pr.type,
          pc.id AS colorId, pc.color, pc.fabric, tx.texture,
          pc.imageMedium

         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.texture tx
         INNER JOIN pc.product pr

         WHERE pc.id = :pc
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('pc', $pc);
        $initialResult = $query->getOneOrNullResult();
        $result = $this->fullName($initialResult);

        return $result;
    }

    public function quantity(int $product)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT
          pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
          p.costPrice, p.sellingPrice, p.qtyInStock,

          pc.imageMedium,
          img2.imageMedium AS imageMedium2,
          img3.imageMedium AS imageMedium3,
          img4.imageMedium AS imageMedium4,
          img5.imageMedium AS imageMedium5

         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.texture tx
         INNER JOIN pc.product pr

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN pr.qtyPack q
         LEFT JOIN pc.productData p

         WHERE pr.id = :product
         ';

        $query = $entityManager->createQuery($dql);
        $query->setParameter('product', $product);
        $initialResult = $query->getResult();

        $colorSet = [];
        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $res) {
                $initialResult[$i] = $this->fullName($res);
            }

            foreach ($initialResult as $i => $result) {
                $qtyIdSet[$result['colorId']][] =
                $result['qtyInStock'];

                $totalQtySet[] = $result['qtyInStock'];

                $qtySet[$result['colorId']]['colorId'] =
                $result['colorId'];

                $qtySet[$result['colorId']]['color'] =
                $result['colors_full'];

                $qtySet[$result['colorId']]['fabric'] =
                $result['fabrics_full'];

                $qtySet[$result['colorId']]['image'] =
                $result['imageMedium'];
                $qtySet[$result['colorId']]['image2'] =
                $result['imageMedium2'];
                $qtySet[$result['colorId']]['image3'] =
                $result['imageMedium3'];
                $qtySet[$result['colorId']]['image4'] =
                $result['imageMedium4'];
                $qtySet[$result['colorId']]['image5'] =
                $result['imageMedium5'];
            }

            foreach ($qtyIdSet as $i => $qty) {
                $qtySet[$i]['qty'] = array_sum($qty);
            }

            $totalQty = array_sum($totalQtySet);
            $colorSet['colors'] = $qtySet;
            $colorSet['totalQty'] = $totalQty;
        }

        return $colorSet;
    }

    public function thumbnail(int $id)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT
           pr.id, pr.name, pr.brand,
           pr.category,pr.occasion, pr.type,
           q.qtyPack,
           pc.id AS colorId ,pc.color, pc.fabric, tx.texture,

          pc.imageSmall, img2.imageSmall AS imageSmall2,
          img3.imageSmall AS imageSmall3, img4.imageSmall AS imageSmall4,
          img5.imageSmall AS imageSmall5

         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.texture tx
         INNER JOIN pc.product pr

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN pr.qtyPack q

         WHERE pc.id = :id
         ';

        $query = $entityManager->createQuery($dql);
        $query->setParameter('id', $id);
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function thumbnailSet(int $id)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT
           pr.id, pr.name, pr.brand,
           pr.category,pr.occasion, pr.type,
           q.qtyPack,
           pc.id AS colorId, pc.color, pc.fabric, tx.texture,

          pc.imageSmall, img2.imageSmall AS imageSmall2,
          img3.imageSmall AS imageSmall3, img4.imageSmall AS imageSmall4,
          img5.imageSmall AS imageSmall5

         FROM App\Entity\Product\ProductColor\ProductColor pc
         INNER JOIN pc.product pr
         LEFT JOIN pr.productColor pc
         LEFT JOIN pc.texture tx
         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN pr.qtyPack q

         WHERE pc.id = :id
         ORDER BY pc.color ASC
         ';

        $query = $entityManager->createQuery($dql);
        $query->setParameter('id', $id);
        $iniResult = $query->getResult();
        foreach ($iniResult as $i => $result) {
            $results[$i] = $this->fullName($result);
        }

        return $results;
    }

    public function fetchByProduct(int $product)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
           pc.id AS colorId ,pc.color, pc.fabric, tx.texture

         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.texture tx
         INNER JOIN pc.product pr
         WHERE pr.id = :product
         ';
        $query = $em->createQuery($dql);

        $query->setParameter('product', $product);
        $initialResult = $query->getResult();

        foreach ($initialResult as $i => $initial) {
            $result[$initial['color']] = $initial['colorId'];
        }

        return $result;
    }

    // Determine if a productColor pcan be excluded for this Product.
    // If the ProducColor has already been added as a Similar ProductColor, then it cannot be added.
    public function checkProduct(int $pc, int $new_pc)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT pc
         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.similarProductColor sm
         LEFT JOIN sm.excludeProductColors ex
         LEFT JOIN ex.color expc

         WHERE pc.id = :productColor
         AND JSON_TEXT(sm.sort) LIKE :newProductColor
         ';
        $query = $em->createQuery($dql);
        $query->setParameter('productColor', $pc)
              ->setParameter('newProductColor', '%'.$new_pc.'%')
        ;

        $initialResult = $query->getResult();
        $checkProduct = !empty($initialResult);

        return $checkProduct;
    }

    // Determine if a productColor pcan be excluded for this particular Product.
    // If a Product color has already been added as a Similar ProductColor,
    // then it cannot be added.
    public function checkExclude(int $pc, int $new_pc)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT pc
         FROM App\Entity\Product\ProductColor\ProductColor pc
         LEFT JOIN pc.similarProductColor sm
         LEFT JOIN sm.excludeProductColors ex
         LEFT JOIN ex.color expc

         WHERE pc.id = :productColor
         AND expc.id = :newProductColor
         ';
        $query = $em->createQuery($dql);
        $query->setParameter('productColor', $pc)
              ->setParameter('newProductColor', $new_pc)
        ;
        $initialResult = $query->getResult();
        $checkExclude = !empty($initialResult);

        return $checkExclude;
    }

    //    /**
    //     * @return ProductColor[] Returns an array of ProductColor objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProductColor
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
