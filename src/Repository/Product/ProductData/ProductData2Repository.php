<?php

// src/Repository/Product/ProductData/ProductData2Repository.php

namespace App\Repository\Product\ProductData;

use App\Entity\Billing\Order;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductData\ProductData;
use App\Entity\Review\Review;
use App\Entity\User\User;
use App\Service\AttributeService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType as ArrayType;
use Doctrine\DBAL\ParameterType as Type;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Jawira\CaseConverter\Convert;

/**
 * @method ProductData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductData[]    findAll()
 * @method ProductData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductData2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private AttributeService $a)
    {
        parent::__construct($registry, ProductData::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ProductData $entity, bool $flush = true): void
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
    public function remove(ProductData $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function sortByColor(int $id, array $filter)
    {
        $query = $this
                    ->createQueryBuilder('p')
                    ->andWhere('p.product IN (:product)')
                    ->setParameter('product', $id)
                    ->innerJoin(
                        ProductColor::class,
                        'pc',
                        Join::WITH,
                        'p.color = pc.id',
                    )
        ;

        // apply price range
        $query = $query->andWhere(
            $query->expr()
              ->between(
                  'p.sellingPrice',      // price column
                  $filter['minPrice'],     // minimum price
                  $filter['maxPrice']
              )
        );     // maximum price

        // apply sizes
        if (!empty($filter['size'])) {
            $sizes = $filter['size'];
            $query
                      ->andWhere('p.size IN (:size)')
                      ->setParameter('size', $sizes)
            ;
        }

        if (!empty($filter['color'])) {
            $colors = $filter['color'];

            for ($i = 0; $i < \count($colors); ++$i) {
                $query->andWhere('pc.color LIKE :colorSet')
                      ->setParameter('colorSet', "%{$colors[$i]}%")
                ;

                $finalQuery[$i] = $query->getQuery()->getResult();
            }
            $finalQuery = array_merge(...$finalQuery);
        }

        return $finalQuery;

        // $query = $query
        //             ->andWhere($query->expr()->isNull('pc.color'));
    }

    public function sortByLast(array $filter)
    {
        $query = $this
            ->createQueryBuilder('p')
            ->andWhere('p.product IN (:product)')
            ->setParameter('product', $filter['product'])
        ;

        if (!empty($filter['supplier'])) {
            $query
              ->andWhere('p.supplier IN (:supplierData)')
              ->setParameter('supplierData', $filter['supplier'])
            ;
        }

        // retrieve the last productData (only one item)
        $query->orderBy('p.id', 'DESC')
              ->setMaxResults(1);

        if (empty($query->getQuery()->getResult())) {
            return null;
        } elseif (!empty($query->getQuery()->getResult())) {
            return $query->getQuery()->getResult()[0];
        }
    }

    public function findBySameSupplier(array $filter)
    {
        $query = $this
            ->createQueryBuilder('p')
            ->andWhere('p.product IN (:product)')
            ->setParameter('product', $filter['product'])

            ->andWhere('p.supplier IN (:supplierData)')
            ->setParameter('supplierData', $filter['supplier'])
            ->orderBy('p.id', 'DESC')
        ;

        if (empty($query->getQuery()->getResult())) {
            return null;
        } elseif (!empty($query->getQuery()->getResult())) {
            return $query->getQuery()->getResult();
        }
    }

    public function findByColor(ProductData $productData)
    {
        // $colors = explode('-',$productData->getColor()->getColor());
        // $occasion = $productData->getProduct()->getOccasion();
        // $products = $this->productRepository->findBy(['occasion' => $occasion]);
        $product = $productData->getProduct();
        $colors = $productData->getColor()->getColor();
        $id = $productData->getId();
        $colorId = $productData->getColor()->getId();

        $query = $this
            ->createQueryBuilder('p')
            ->andWhere('p.product IN (:product)')
            ->setParameter('product', $product)
            ->andWhere('p.id != :id')
            ->setParameter('id', $id)
            ->innerJoin(
                ProductColor::class,
                'pc',
                Join::WITH,
                'p.color = pc.id',
            )
            ->andWhere('pc.id = :colorId')
            ->setParameter('colorId', $colorId)
            ->orderBy('pc.color', 'ASC')
        ;

        if (empty($query->getQuery()->getResult())) {
            return null;
        } elseif (!empty($query->getQuery()->getResult())) {
            return $query->getQuery()->getResult();
        }
    }

    public function search(Search $search, $q): array
    {
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'updated';
        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Arrange all words in an array
            $keywords = explode(' ', $keywords);
        }
        $brands = !empty($search->brands()) ? $search->brands() : [];
        $suppliers = !empty($search->suppliers()) ? $search->suppliers() : '';
        if (!empty($suppliers)) {
            foreach ($suppliers as $i => $supplier) {
                $suppliers[$i] = $supplier->getId();
            }
        }

        $condition = ' ';
        $conn = $this->getEntityManager()->getConnection();

        $intro =
          'SELECT
          p.pro_pvt_id AS id,
          p.selling_price AS "sellingPrice",
          p.cost_price AS "costPrice",
          ((p.selling_price - p.cost_price) / p.cost_price) AS profit,
          p.size, p.sku,
          p.qty_in_stock AS qty,
          p.created AS created, p.updated AS updated,
          pr.pro_id AS "productId",
          pr.name AS name,  pr.brand AS brand,
          pr.category AS category, pr.occasion AS occasion,
          pr.type AS type,
          pc.clr_pvt_id AS "colorId", pc.color, pc.fabric AS "fabricText",
          pc.image_md AS "imageMedium",
          s.sp_id AS "supplierId", s.name AS supplier,
          o.qty_on_order AS "qtyOnOrder", o.reorder_level AS "reorderLevel"

        FROM product_data p

        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
        LEFT JOIN supplier_data sp ON p.fk_pvt_sp_id = sp.pivot_sp_id
        LEFT JOIN suppliers s ON sp.fk_sp_id = s.sp_id
        LEFT JOIN product_data_order o ON p.fk_pro_id = o.pro_ord_id
        ';

        /* Allow natural sorting for double digits
           ex. Size 11 is higher than Size 7.
        */
        $columns = ['p.selling_price', 'p.cost_price', 'p.size'];
        $sortField = in_array($sort, $columns)
                      ? "cast($sort as double precision)" : $sort;
        $end =
          "
         GROUP BY
          p.pro_pvt_id, p.selling_price, p.cost_price, p.qty_in_stock,
          p.size, p.sku, p.qty_in_stock, p.created, p.updated,
          pr.pro_id, pr.name, pr.brand, pr.category,
          pr.occasion, pr.type, pc.clr_pvt_id, pc.color,
          pc.image_md, s.sp_id, s.name, o.qty_on_order, o.reorder_level
         ORDER BY $sortField $order NULLS LAST
        ";
        if (!empty($keywords) or !empty($brands) or !empty($suppliers)) {
            $condition .= ' WHERE ';
        }

        // Check if user is searching for product ID only
        if (!empty($keywords)) {
            if ($search->searchID()) {
                foreach ($keywords as $i => $keyword) {
                    $keywords[$i] = (int) $keyword;
                }

                $condition .= ' p.pro_pvt_id IN (?) ';
                $parameters = [$keywords];
                $type = [ArrayType::INTEGER];
            } else {
                $keyText = '';
                foreach ($keywords as $i => $keyword) {
                    $keyText = ($i != array_key_last($keywords)) ? $keyText.$keyword.'|'
                    : $keyText = $keyText.$keyword;
                }

                $parameters[] = $keyText;
                $condition .= ' pr.name ~* ? ';
                $parameters = [$keyText];
                $type = [Type::STRING,  // $keyText
                ];
            }
        }

        if (!empty($brands)) {
            if (!empty($keywords)) {
                $condition .= ' AND ';
            }

            $condition .= ' pr.brand IN (?) ';
            $parameters[] = $brands;
            $type[] = ArrayType::STRING;  // $brands
        }

        if (!empty($suppliers)) {
            if (!empty($keywords) or !empty($brands)) {
                $condition .= ' AND ';
            }

            $condition .= ' s.sp_id IN (?) ';
            $parameters[] = $suppliers;
            $type[] = ArrayType::INTEGER;  // $suppliers
        }

        $sql = $intro.$condition.$end;

        $iniResults =
            $conn->executeQuery($sql, $parameters, $type)
                 ->fetchAllAssociative();

        if (!empty($iniResults)) {
            foreach ($iniResults as $i => $product) {
                $iniResults[$i]['fabrics']
                  = json_decode($product['fabricText']);

                $iniResults[$i] = $this->$a->enrichProduct($iniResults[$i]);
            }
        }

        return $iniResults;
    }

    public function findByMirror(array $filter)
    {
        $query = $this
            ->createQueryBuilder('p')
            ->andWhere('p.product IN (:product)')
            ->setParameter('product', $filter['product'])

            ->andWhere('p.size IN (:sizes)')
            ->setParameter('sizes', $filter['size'])

            ->orderBy('p.id', 'DESC')
        ;
        if (empty($query->getQuery()->getResult())) {
            return null;
        } elseif (!empty($query->getQuery()->getResult())) {
            return $query->getQuery()->getResult();
        }
    }

    public function getLast()
    {
        return $this->createQueryBuilder('p')
            // exclude null updated columns by retrieving date above 2010-01-01
            ->andWhere('p.updated >= :date')
            ->setParameter('date', '2010-12-01')
            ->orderBy('p.updated', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()[0]
        ;
    }

    public function findByFilter(array $product, array $filter)
    {
        $query = $this
                    ->createQueryBuilder('p')
                    ->andWhere('p.product IN (:product)')
                    ->setParameter('product', $product)
                    ->innerJoin(
                        ProductColor::class,
                        'pc',
                        Join::WITH,
                        'p.color = pc.id',
                    )
                    ->leftJoin(
                        ProductColorTexture::class,
                        'tx',
                        Join::WITH,
                        'pc.texture = tx.id',
                    );

        $queryExclude = $this
                    ->createQueryBuilder('pr')
                    ->andWhere('pr.product IN (:product)')
                    ->setParameter('product', $product)
                    ->innerJoin(
                        ProductColor::class,
                        'pc',
                        Join::WITH,
                        'pr.color = pc.id',
                    )
                    ->leftJoin(
                        ProductColorTexture::class,
                        'tx',
                        Join::WITH,
                        'pc.texture = tx.id',
                    );

        $q = ['query' => $query, 'queryExclude' => $queryExclude];

        // Apply sizes
        if (!empty($filter['size'])) {
            $sizes = $filter['size'];
            foreach ($q as $p => $builder) {
                // To write p.size, 'p' is retrieved using $builder->getDqlPart('select')[0]->getParts()[0]. Alternately, the alias of the query builder can be added
                // in the array itself.
                $alias = $builder->getDqlPart('select')[0]->getParts()[0];
                $builder->andWhere($alias.'.size IN (:size)')
                        ->setParameter('size', $sizes);
            }
        }

        // Apply price range
        if (!empty($filter['priceRange'])) {
            $priceRange = $filter['priceRange'];
            foreach ($priceRange as $i => $priceValue) {
                $priceRanges[] = explode('.', (string) $priceValue);
            }

            // Since only four price ranges exist,
            // the long way (below) has been. If the list of ranges
            // increases, the $priceRanges should be iterated
            // and raw SQL should be employed.
            foreach ($q as $p => $builder) {
                $alias = $builder->getDqlPart('select')[0]->getParts()[0];
                switch (count($priceRanges)) {
                    case 1:
                        $builder->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[0][0],
                                    $priceRanges[0][1]
                                ),
                            )
                        );
                        break;

                    case 2:
                        $builder->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[0][0],
                                    $priceRanges[0][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[1][0],
                                    $priceRanges[1][1]
                                ),
                            )
                        );
                        break;

                    case 3:
                        $builder->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[0][0],
                                    $priceRanges[0][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[1][0],
                                    $priceRanges[1][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[2][0],
                                    $priceRanges[2][1]
                                ),
                            )
                        );
                        break;

                    case 4:
                        $builder->andWhere(
                            $builder->expr()->orX(
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[0][0],
                                    $priceRanges[0][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[1][0],
                                    $priceRanges[1][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[2][0],
                                    $priceRanges[2][1]
                                ),
                                $builder->expr()->between(
                                    $alias.'.sellingPrice',
                                    $priceRanges[3][0],
                                    $priceRanges[3][1]
                                ),
                            )
                        );
                        break;
                }
            }
        } elseif (empty($filter['priceRange'])) {
            // apply price range
            foreach ($q as $p => $builder) {
                $alias = $builder->getDqlPart('select')[0]->getParts()[0];
                $builder->andWhere($builder->expr()->between(
                    $alias.'.sellingPrice',      // price column
                    $filter['minPrice'],     // minimum price
                    $filter['maxPrice']
                ));     // maximum price
            }
        }

        // Apply colors
        if (!empty($filter['color']) and empty($filter['fabrics']) and empty($filter['textures'])) {
            $colors = $filter['color'];
            foreach ($colors as $i => $color) {
                $q['query']
                        ->andWhere('pc.color LIKE :colorSet')
                        ->setParameter('colorSet', "%{$color}%")
                        ->orderBy('pc.color', 'ASC')
                ;

                $iniQuery[$i] = $q['query']->getQuery()->getResult();
            }

            $iniQuery = array_merge(...$iniQuery);
        }

        if (!empty($filter['color']) and !empty($filter['fabrics']) and empty($filter['textures'])) {
            $colors = $filter['color'];
            $fabrics = $filter['fabrics'];

            foreach ($colors as $i => $color) {
                foreach ($fabrics as $j => $fabric) {
                    $q['query']
                            ->andWhere('pc.color LIKE :colorSet')
                            ->setParameter('colorSet', "%{$color}%")
                            ->andWhere('pc.fabric LIKE :fabricSet')
                            ->setParameter('fabricSet', "%{$fabric}%")
                            ->orderBy('pc.color', 'ASC')
                    ;
                    $iniQuery[$i][$j] = $q['query']->getQuery()->getResult();
                }
            }

            $iniQuery = array_merge(...array_merge(...$iniQuery));
        }

        if (!empty($filter['color']) and empty($filter['fabrics']) and !empty($filter['textures'])) {
            $colors = $filter['color'];
            $textures = $filter['textures'];

            foreach ($colors as $i => $color) {
                foreach ($textures as $j => $texture) {
                    $q['query']
                            ->andWhere('pc.color LIKE :colorSet')
                            ->setParameter('colorSet', "%{$color}%")
                            ->andWhere('tx.texture LIKE :textureSet')
                            ->setParameter('textureSet', "%{$texture}%")
                            ->orderBy('pc.color', 'ASC')
                    ;

                    $iniQuery[$i][$j] = $q['query']->getQuery()->getResult();
                }
            }

            $iniQuery = array_merge(...array_merge(...$iniQuery));
        }

        if (!empty($filter['color']) and !empty($filter['fabrics']) and !empty($filter['textures'])) {
            $colors = $filter['color'];
            $fabrics = $filter['fabrics'];
            $textures = $filter['textures'];

            foreach ($colors as $i => $color) {
                foreach ($fabrics as $j => $fabric) {
                    foreach ($textures as $k => $texture) {
                        $q['query']
                                ->andWhere('pc.color LIKE :colorSet')
                                ->setParameter('colorSet', "%{$color}%")
                                ->andWhere('pc.fabric LIKE :fabricSet')
                                ->setParameter('fabricSet', "%{$fabric}%")
                                ->andWhere('tx.texture LIKE :textureSet')
                                ->setParameter('textureSet', "%{$texture}%")
                                ->orderBy('pc.color', 'ASC')
                        ;
                        $iniQuery[$i][$j][$k] = $q['query']->getQuery()->getResult();
                    }
                }
            }

            $iniQuery = array_merge(...array_merge(...array_merge(...$iniQuery)));
        }

        if (empty($filter['color'])) {
            // Apply fabrics
            if (!empty($filter['fabrics'])) {
                $fabrics = $filter['fabrics'];
                foreach ($fabrics as $i => $fabric) {
                    $q['query']
                            ->andWhere('pc.fabric LIKE :fabricSet')
                            ->setParameter('fabricSet', "%{$fabric}%")
                            ->orderBy('pc.fabric', 'ASC')
                    ;

                    $iniQuery[$i] = $q['query']->getQuery()->getResult();
                }

                $iniQuery = array_merge(...$iniQuery);
            }

            // Apply textures
            if (!empty($filter['textures'])) {
                $textures = $filter['textures'];
                foreach ($textures as $i => $texture) {
                    $q['query']
                            ->andWhere('tx.texture LIKE :textureSet')
                            ->setParameter('textureSet', "%{$texture}%")
                            ->orderBy('tx.texture', 'ASC')
                    ;

                    $iniQuery[$i] = $q['query']->getQuery()->getResult();
                }

                $iniQuery = array_merge(...$iniQuery);
            }
        }

        if (empty($filter['color']) and empty($filter['fabrics']) and empty($filter['textures'])) {
            $iniQuery = $q['query']->getQuery()->getResult();
        }

        // Exclude colors
        if (!empty($filter['colorExclude'])) {
            $colorExclude = $filter['colorExclude'];
            foreach ($colorExclude as $j => $color) {
                $q['queryExclude']->andWhere('pc.color LIKE :colorExcludeSet')
                      ->setParameter('colorExcludeSet', "%{$color}%")
                ;

                $queryExcludeArray[] = $q['queryExclude']->getQuery()->getResult();
            }

            // Check for empty multidimensional array
            if (0 != count(array_filter($queryExcludeArray)) and !empty($iniQuery)) {
                $queryExcludeArray = array_merge(...$queryExcludeArray);
                foreach ($queryExcludeArray as $z => $excludeColor) {
                    $queryExcludeId[$z] = $excludeColor->getId();
                }

                foreach ($iniQuery as $k => $ini) {
                    $iniQueryId[$k] = $ini->getId();
                }

                $iniQueryId = array_values(array_diff($iniQueryId, $queryExcludeId));

                foreach ($iniQueryId as $m => $id) {
                    foreach ($iniQuery as $n => $ini) {
                        if ($ini->getId() == $id) {
                            $iniQueryArr[] = $ini;
                        }
                    }
                }

                if (!empty($iniQueryArr)) {
                    $iniQuery = [];
                }$iniQuery = $iniQueryArr;
            }
        }

        if (!empty($iniQuery)) {
            foreach ($iniQuery as $i => $data) {
                $iniQueryById[$data->getId()] = $data;
            }

            foreach ($iniQueryById as $i => $value) {
                $finalQuery[$iniQueryById[$i]->getColor()->getId()][$i] = $value;
            }

            return $finalQuery;
        } elseif (empty($iniQuery)) {
            $iniQuery = $query->getQuery()->getResult();

            if (!empty($iniQuery)) {
                foreach ($iniQuery as $i => $data) {
                    $iniQueryById[$data->getId()] = $data;
                }

                foreach ($iniQueryById as $i => $value) {
                    $finalQuery[$iniQueryById[$i]->getColor()->getId()][$i] = $value;
                }

                return $finalQuery;
            } elseif (empty($iniQuery)) {
                return [];
            }
        }
    }

    public function fetchByProduct(int $product)
    {
        $em = $this->getEntityManager();
        $query =
          $em
            ->createQuery(
                'SELECT
                 p.id, p.sku, p.costPrice, p.sellingPrice,
                 p.qtyInStock,
                 p.size, p.created, p.updated,
                 pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
                 s.id AS supplierId
                 -- pc.imageMedium,
                 -- img2.imageMedium AS imageMedium2,
                 -- img3.imageMedium AS imageMedium3,
                 -- img4.imageMedium AS imageMedium4,
                 -- img5.imageMedium AS imageMedium5

               FROM App\Entity\Product\ProductData\ProductData p
               LEFT JOIN p.product pr
               LEFT JOIN p.supplier sp
               LEFT JOIN sp.supplier s
               LEFT JOIN p.color pc
               LEFT JOIN pc.texture tx


               LEFT JOIN pc.productImage2 img2
               LEFT JOIN pc.productImage3 img3
               LEFT JOIN pc.productImage4 img4
               LEFT JOIN pc.productImage5 img5

               WHERE pr.id = :product
               ORDER BY p.updated
               '
            );
        $query->setParameter('product', $product);
        $result = $query->getResult();

        return $result;
    }

    public function all($q)
    {
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'updated';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $fabrics = $q->has('fabrics') ? $q->get('fabrics') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $name = $q->has('name') ? $q->get('name') : '';
        $size = $q->has('size') ? $q->get('size') : '';
        $supplier = $q->has('supplier') ? $q->get('supplier') : '';

        $conn = $this->getEntityManager()->getConnection();
        $parameters = [];
        $type = [];
        $intro =
          'SELECT
          p.pro_pvt_id AS id,
          p.selling_price AS "sellingPrice",
          p.cost_price AS "costPrice",
          ((p.selling_price - p.cost_price) / p.cost_price) AS profit,
          p.size, p.sku,
          p.qty_in_stock AS qty,
          p.created AS created, p.updated AS updated,
          pr.pro_id AS "productId",
          pr.name AS name,  pr.brand AS brand, pr.occasion AS occasion,
          pr.category AS category,
          pr.type AS type,
          pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
          pc.image_md AS "imageMedium",
          s.sp_id AS "supplierId", s.name AS supplier,
          o.qty_on_order AS "qtyOnOrder", o.reorder_level AS "reorderLevel"

        FROM product_data p

        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
        LEFT JOIN supplier_data sp ON p.fk_pvt_sp_id = sp.pivot_sp_id
        LEFT JOIN suppliers s ON sp.fk_sp_id = s.sp_id
        LEFT JOIN product_data_order o ON p.fk_pro_id = o.pro_ord_id
        ';

        /* Allow natural sorting for double digits
           ex. Size 11 is higher than Size 7.
        */
        $columns = ['p.selling_price', 'p.cost_price', 'p.size'];
        $sort = in_array($sort, $columns) ? "cast($sort as double precision)"
                : $sort;
        $end =
          "
         GROUP BY
          p.pro_pvt_id, p.selling_price, p.cost_price, p.qty_in_stock,
          p.size, p.sku, p.qty_in_stock, p.created, p.updated,
          pr.pro_id, pr.name, pr.brand, pr.category,
          pr.type, pc.clr_pvt_id, pc.color,
          pc.image_md, s.sp_id, s.name, o.qty_on_order, o.reorder_level
         ORDER BY $sort $order NULLS LAST
         ";

        $condition = '';

        if (!empty($brand)) {
            $condition .= ' pr.brand = :brand ';
            $parameters[] = $brand;
            $type[] = Type::STRING;  // $brand
        }

        if (!empty($product)) {
            $condition .= ' pr.pro_id = :product ';
            $parameters[] = $product;
            $type[] = Type::INTEGER;  // $product
        }

        if (!empty($pc)) {
            $condition .= ' pc.clr_pvt_id = :pc ';
            $parameters[] = $pc;
            $type[] = Type::INTEGER;  // $color
        }

        if (!empty($colors)) {
            $condition .= ' pc.color::text  ~* :colors ';
            $parameters[] = $colors;
            $type[] = Type::STRING;  // $colors
        }

        if (!empty($fabrics)) {
            $fabricSet = '';
            $fabrics = explode('-', $fabrics);
            foreach ($fabrics as $i => $fabric) {
                $condition .= ' (pc.fabric::text ~* ?) '.
                                ((end($fabrics) != $fabric) ? ' AND ' : '');
                $parameters[] = [$fabric];
                $type[] = ArrayType::STRING;  // $fabrics
            }
        }

        if (!empty($size)) {
            $condition .= ' p.size = :size ';
            $parameters[] = $size;
            $type[] = Type::STRING;  // $size
        }

        if (!empty($supplier)) {
            $condition .= ' s.sp_id = :supplier ';
            $parameters[] = $supplier;
            $type[] = Type::INTEGER;  // $supplier
        }

        if (!empty($brand) or !empty($product)
           or !empty($name) or !empty($pc)
           or !empty($colors) or !empty($fabrics)
           or !empty($size) or !empty($supplier)) {
            $sql = "$intro WHERE $condition $end";
        } else {
            $sql = $intro.$end;
        }

        $results = $conn->executeQuery($sql, $parameters, $type)
                        ->fetchAllAssociative();

        if (!empty($results)) {
            foreach ($results as $i => $product) {
                $results[$i] = $this->$a->enrichProduct($results[$i]);
            }
        }

        return $results;
    }

    public function supplier($supplier, $q)
    {
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'updated';
        // $pc = $q->has('pc') ? $q->get('pc') : '';
        // $colors = $q->has('colors') ? $q->get('colors') : '';
        // $fabrics = $q->has('fabrics') ? '"'.str_replace('-', '","' , $q->get('fabrics') ).'"' : '';
        // $brand = $q->has('brand') ? $q->get('brand') : '';
        // $product = $q->has('product') ? $q->get('product') : '';
        // $name = $q->has('name') ? $q->get('name') : '';
        // $size = $q->has('size') ? $q->get('size') : '';
        // $supplier = $q->has('supplier') ? $q->get('supplier') : '';

        $conn = $this->getEntityManager()->getConnection();
        $parameters = [];
        $type = [];
        $intro =
          'SELECT
          p.pro_pvt_id AS id,
          p.selling_price AS "sellingPrice",
          p.cost_price AS "costPrice",
          ((p.selling_price - p.cost_price) / p.cost_price) AS profit,
          p.size, p.sku,
          p.qty_in_stock AS qty,
          p.created AS created, p.updated AS updated,
          pr.pro_id AS "productId",
          pr.name AS name,  pr.brand AS brand,
          pr.category AS category, pr.occasion AS occasion,
          pr.type AS type,
          pc.clr_pvt_id AS "colorId", pc.color, pc.fabric AS "fabricText",
          pc.image_md AS "imageMedium",
          s.sp_id AS "supplierId", s.name AS supplier,
          o.qty_on_order AS "qtyOnOrder", o.reorder_level AS "reorderLevel"

        FROM product_data p

        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
        LEFT JOIN supplier_data sp ON p.fk_pvt_sp_id = sp.pivot_sp_id
        LEFT JOIN suppliers s ON sp.fk_sp_id = s.sp_id
        LEFT JOIN product_data_order o ON p.fk_pro_id = o.pro_ord_id
        ';

        /* Allow natural sorting for double digits
           ex. Size 11 is higher than Size 7.
        */
        $columns = ['p.selling_price', 'p.cost_price', 'p.size'];
        $sort = in_array($sort, $columns) ? "cast($sort as double precision)"
                : $sort;
        $end =
          "
         GROUP BY
          p.pro_pvt_id, p.selling_price, p.cost_price, p.qty_in_stock,
          p.size, p.sku, p.qty_in_stock, p.created, p.updated,
          pr.pro_id, pr.name, pr.brand, pr.category,
          pr.occasion, pr.type, pc.clr_pvt_id, pc.color,
          pc.image_md, s.sp_id, s.name, o.qty_on_order, o.reorder_level
         ORDER BY $sort $order NULLS LAST
         ";

        $condition = '';

        if (!empty($brand)) {
            $condition .= ' pr.brand = :brand ';
            $parameters[] = $brand;
            $type[] = Type::STRING;  // $brand
        }

        if (!empty($product)) {
            $condition .= ' pr.pro_id = :product ';
            $parameters[] = $product;
            $type[] = Type::INTEGER;  // $product
        }

        if (!empty($pc)) {
            $condition .= ' pc.clr_pvt_id = :pc ';
            $parameters[] = $pc;
            $type[] = Type::INTEGER;  // $color
        }

        if (!empty($colors)) {
            $condition .= ' pc.color::text IN (:colors) ';
            $parameters[] = $colors;
            $type[] = Type::STRING;  // $colors
        }

        if (!empty($fabrics)) {
            $condition .= ' pc.fabric::text  ~* :fabrics ';
            $parameters[] = $fabrics;
            $type[] = Type::STRING;  // $fabrics
        }

        if (!empty($size)) {
            $condition .= ' p.size = :size ';
            $parameters[] = $size;
            $type[] = Type::STRING;  // $size
        }

        if (!empty($supplier)) {
            $condition .= ' s.sp_id = :supplier ';
            $parameters[] = $supplier->getId();
            $type[] = Type::INTEGER;  // $supplier
        }

        if (!empty($brand) or !empty($product)
           or !empty($name) or !empty($pc)
           or !empty($colors) or !empty($fabrics)
           or !empty($size) or !empty($supplier)) {
            $sql = "$intro WHERE $condition $end";
        } else {
            $sql = $intro.$end;
        }

        $results = $conn->executeQuery($sql, $parameters, $type)
                        ->fetchAllAssociative();

        if (!empty($results)) {
            foreach ($results as $i => $product) {
                $results[$i]['fabrics']
                  = json_decode($product['fabricText']);

                $results[$i] = $this->$a->enrichProduct($results[$i]);
            }
        }

        return $results;
    }

    public function details(int $product)
    {
        $em = $this->getEntityManager();
        $dql =
          'SELECT
         p.id,
         pr.id  AS productId, pr.name,
         pr.brand, pr.category, pr.occasion, pr.type,
         v.videoUrl,
         p.costPrice, p.sellingPrice, p.qtyInStock, p.size, p.sku,
         pc.id AS colorId , pc.color, pc.fabric,
         s.id AS supplierId, s.name AS supplierName,
         o.qtyOnOrder,o.reorderLevel,

         pc.imageMedium,
         img2.imageMedium AS imageMedium2,
         img3.imageMedium AS imageMedium3,
         img4.imageMedium AS imageMedium4,
         img5.imageMedium AS imageMedium5,
         p.created, p.updated

       FROM App\Entity\Product\ProductData\ProductData p

       INNER JOIN p.product pr
       LEFT JOIN pr.video v
       LEFT JOIN p.supplier sp
       LEFT JOIN p.productDataOrder o
       LEFT JOIN sp.supplier s
       LEFT JOIN p.color pc
       LEFT JOIN pc.productImage2 img2
       LEFT JOIN pc.productImage3 img3
       LEFT JOIN pc.productImage4 img4
       LEFT JOIN pc.productImage5 img5

       WHERE pr.id = :product
       ORDER BY p.created DESC, p.updated DESC
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('product', $product);

        $results = [];
        $results = $query->getResult();

        if (empty($results)) {
            return ['set' => [], 'color' => [], 'size' => []];
        }

        if (!empty($results)) {
            foreach ($results as $i => $result) {
                $results[$i] = $this->$a->enrichProduct($result);
            }

            foreach ($results as $i => $result) {
                $qtyIdSet[$result['colorId']][] =
                $result['qtyInStock'];

                $sizeIdSet[$result['size']][] =
                $result['qtyInStock'];

                $totalQtySet[] = $result['qtyInStock'];

                $sizeSet[$result['size']]['costPrice'] =
                $result['costPrice'];

                $sizeSet[$result['size']]['sellingPrice'] =
                $result['sellingPrice'];

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

                $qtySet[$result['colorId']]['name'] =
                $result['name'];
            }

            foreach ($qtyIdSet as $i => $qty) {
                $qtySet[$i]['qty'] = array_sum($qty);
            }

            foreach ($sizeIdSet as $i => $qty) {
                $sizeSet[$i]['qty'] = array_sum($qty);
            }

            ksort($sizeSet);

            $totalQty = array_sum($totalQtySet);
            $colorSet['colors'] = $qtySet;
            $colorSet['totalQty'] = $totalQty;
        }

        return ['set' => $results, 'color' => $colorSet, 'size' => $sizeSet];
    }

    public function cartItem(int $product)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT
                 p.id, p.size, p.sellingPrice, p.qtyInStock,
                 pr.id  AS productId, pr.name, pr.brand,
                 pr.category, pr.occasion, pr.type,
                 pc.id AS colorId , pc.color, pc.fabric,
                 pc.imageSmall

               FROM App\Entity\Product\ProductData\ProductData p

               INNER JOIN p.product pr
               INNER JOIN p.color pc
               WHERE p.id = :product
               ';
        $query = $em->createQuery($dql)
                    ->setParameter('product', $product);

        $result = $this->$a->enrichProduct($query->getOneOrNullResult());

        return $result;
    }

    public function reviews(User $user)
    {
        $user_id = $user->getId();
        $em = $this->getEntityManager();
        $dql = 'SELECT
         i.quantity, p.sellingPrice,  p.size,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
         pc.imageMedium, u.email as reviewer,
         r.id as review

       FROM App\Entity\Product\ProductData\ProductData p

       LEFT JOIN p.product pr
       LEFT JOIN p.items i
       LEFT JOIN i.orderRef o
       LEFT JOIN p.color pc
       LEFT JOIN pc.texture tx

       LEFT JOIN pr.productReviews r

       LEFT JOIN r.users u
       -- LEFT JOIN u.orders ord

       WHERE u.id = :id
       AND o.status = :status  -- Item has been paid for.
       -- AND rev.id != :id  --- No review yet has been posted
       -- AND rev.id IS NULL  --- No review yet has been posted
       -- AND o.order_id != ord.order_id
       ORDER BY pr.name
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('id', $user_id)
              ->setParameter('status', Order::STATUS_PAID)
        ;
        $iniReviews = $query->getResult();

        if (!empty($iniReviews)) {
            foreach ($iniReviews as $i => $product) {
                $iniReviews[$i] = $this->$a->enrichProduct($product);
            }
        }

        $results = [];
        foreach ($iniReviews as $i => $product) {
            $colorKeys = [
                'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageMedium', 'quantity', 'sellingPrice',
            ];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['productId']][$product['color'].'-'.$product['colorId']][$product['size']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['productId']][$key] = $product[$key];
                }
            }

            $results[$product['productId']]['thumbnails'] = $productColors[$product['productId']];
        }

        /* Cumulate each color's quantity, respective item sub-total
           and each color's total */
        foreach ($results as $i => $result) {
            foreach ($result['thumbnails'] as $j => $thumbnail) {
                $total = 0;
                $qty = 0;
                foreach ($thumbnail as $k => $val) {
                    $subTotal = $val['quantity'] * $val['sellingPrice'];
                    $results[$i]['thumbnails'][$j][$k]['subTotal'] = $subTotal;
                    $total += $subTotal;
                    $qty += $val['quantity'];
                }

                $results[$i]['thumbnails'][$j] = array_values($thumbnail);
                $results[$i]['thumbnails'][$j] = $results[$i]['thumbnails'][$j][0];
                $results[$i]['thumbnails'][$j]['total'] = $total;
                $results[$i]['thumbnails'][$j]['qty'] = $qty;
            }
        }

        return $results;
    }

    public function thumbnails(Review $review)
    {
        $review_id = $review->getId();
        $user_id = $review->getUsers()->getId();

        $em = $this->getEntityManager();
        $dql = 'SELECT
         i.quantity, p.sellingPrice, p.size,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
         pc.imageMedium

       FROM App\Entity\Product\ProductData\ProductData p

       LEFT JOIN p.product pr
       LEFT JOIN pr.productReviews r
       LEFT JOIN p.items i
       LEFT JOIN i.orderRef o
       LEFT JOIN o.users us
       LEFT JOIN p.color pc
       LEFT JOIN pc.texture tx


       WHERE us.id = :user AND r.id = :review
       ORDER BY pr.id ASC
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('user', $user_id)
              ->setParameter('review', $review_id);

        $iniReviews = $query->getResult();
        if (!empty($iniReviews)) {
            foreach ($iniReviews as $i => $product) {
                $iniReviews[$i] = $this->$a->enrichProduct($product);
            }
        }

        $results = [];
        foreach ($iniReviews as $i => $product) {
            $colorKeys = [
                'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageMedium', 'quantity', 'sellingPrice',
            ];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['productId']][$product['color'].'-'.$product['colorId']][$product['size']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['productId']][$key] = $product[$key];
                }
            }

            $results[$product['productId']]['thumbnails'] = $productColors[$product['productId']];
        }

        $results = $this->$a->enrichProduct(array_values($results)[0]);

        /* Cumulate each color's quantity, respective item sub-total
           and each color's total */
        foreach ($results['thumbnails'] as $j => $thumbnail) {
            $total = 0;
            $qty = 0;
            foreach ($thumbnail as $k => $val) {
                $subTotal = $val['quantity'] * $val['sellingPrice'];
                $results['thumbnails'][$j][$k]['subTotal'] = $subTotal;
                $total += $subTotal;
                $qty += $val['quantity'];
            }

            $results['thumbnails'][$j] = array_values($thumbnail);
            $results['thumbnails'][$j] = $results['thumbnails'][$j][0];
            $results['thumbnails'][$j]['total'] = $total;
            $results['thumbnails'][$j]['qty'] = $qty;
        }

        return $results;
    }

    public function thumbnailNew(User $user, Product $product)
    {
        $user_id = $user->getId();
        $product_id = $product->getId();
        $em = $this->getEntityManager();
        $dql = 'SELECT
         i.quantity, p.sellingPrice, p.size,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
         pc.imageMedium

       FROM App\Entity\Product\ProductData\ProductData p

       LEFT JOIN p.product pr
       LEFT JOIN pr.productReviews r
       LEFT JOIN p.items i
       LEFT JOIN i.orderRef o
       LEFT JOIN o.users us
       LEFT JOIN p.color pc
       LEFT JOIN pc.texture tx


       WHERE us.id = :user
       AND pr.id = :product
       -- AND r.id IS NULL
       ORDER BY pr.id ASC
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('user', $user_id)
              ->setParameter('product', $product_id);

        $iniReviews = $query->getResult();
        foreach ($iniReviews as $i => $review) {
            $iniReviews[$i] = $this->$a->enrichProduct($review);
        }

        $results = [];
        foreach ($iniReviews as $i => $product) {
            $colorKeys = [
                'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageMedium', 'quantity', 'size', 'sellingPrice',
            ];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['productId']][$product['color'].'-'.$product['colorId']][$product['size']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['productId']][$key] = $product[$key];
                }
            }

            $results[$product['productId']]['thumbnails'] = $productColors[$product['productId']];
        }

        $results = array_values($results)[0];

        /* Cumulate each color's quantity, respective item sub-total
           and each color's total */
        foreach ($results['thumbnails'] as $j => $thumbnail) {
            $total = 0;
            $qty = 0;
            foreach ($thumbnail as $k => $val) {
                $subTotal = $val['quantity'] * $val['sellingPrice'];
                $results['thumbnails'][$j][$k]['subTotal'] = $subTotal;
                $total += $subTotal;
                $qty += $val['quantity'];
            }

            $results['thumbnails'][$j] = array_values($thumbnail);
            $results['thumbnails'][$j] = $results['thumbnails'][$j][0];
            $results['thumbnails'][$j]['total'] = $total;
            $results['thumbnails'][$j]['qty'] = $qty;
        }

        return $results;
    }

    public function available(int $product)
    {
        $sql =
          'SELECT
            -- p,i,
            -- p.pro_pvt_id, p.qty_in_stock,
            -- i.ord_itm_id,
            pc.clr_pvt_id AS "colorId",
            SUM(p.qty_in_stock) as "totalQty"

          FROM product_data p

          LEFT JOIN products pr ON p.fk_pro_id = pr.pro_id
          INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
          LEFT JOIN order_item i ON i.fk_pro_id = p.pro_pvt_id

          WHERE pr.pro_id = ?
          AND i.ord_itm_id IS NULL

          GROUP BY
            pc.clr_pvt_id
        ';
        $sqlCart =
          'SELECT
          -- p,i,
          -- p.pro_pvt_id, p.qty_in_stock, i.quantity,
          -- i.ord_itm_id,
          pc.clr_pvt_id AS "colorId",
          (SUM(p.qty_in_stock) - SUM( i.quantity)) as "cartQty"

        FROM product_data p

        LEFT JOIN products pr ON p.fk_pro_id = pr.pro_id
        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN order_item i ON i.fk_pro_id = p.pro_pvt_id
        -- LEFT JOIN "orders" o ON i.fk_ord_id = o.ord_id

        WHERE pr.pro_id = ?
        AND i.ord_itm_id IS  NOT NULL

        GROUP BY
          pc.clr_pvt_id,p.qty_in_stock
      ';
        $c = $this->getEntityManager()->getConnection();
        $fetch = 'fetchAllAssociative';

        $parameter = [$product];
        $type = [Type::INTEGER,    // product
        ];
        $results = $c->executeQuery($sql, $parameter, $type)->$fetch();
        $resultCart = $c->executeQuery($sqlCart, $parameter, $type)->$fetch();

        $available = [];
        if (!empty($results)) {
            foreach ($results as $i => $item) {
                $available[$item['colorId']] = $item;
            }
        }

        return $available;
    }

    public function checkColor(int $product): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql =
          'SELECT pc.clr_pvt_id
         FROM product_data p
         INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
         INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
         WHERE pr.pro_id = ?
         GROUP BY pc.clr_pvt_id
        ';

        $parameters = [$product];
        $type = [
            Type::INTEGER,       // $product
        ];

        $iniResult =
            $conn->executeQuery($sql, $parameters, $type)
                 ->fetchAllAssociative();

        $results = [];
        if (!empty($iniResult)) {
            foreach ($iniResult as $i => $result) {
                $results[$i] = $result['clr_pvt_id'];
            }
        }

        return $results;
    }

    public function checkProduct(int $product)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(p) AS COUNT
         FROM App\Entity\Product\ProductData\ProductData p
         LEFT JOIN p.product pr
         WHERE pr.id = :product
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('product', $product);
        $result = $query->getOneOrNullResult()['COUNT'];

        return (0 === $result) ? true : false;
    }

    public function checkSupplier(int $supplier)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(p) AS COUNT
         FROM App\Entity\Product\ProductData\ProductData p
         LEFT JOIN p.supplier sp
         LEFT JOIN sp.supplier s
         WHERE s.id = :supplier
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('supplier', $supplier);
        $result = $query->getOneOrNullResult()['COUNT'];

        return (0 === $result) ? true : false;
    }
}
