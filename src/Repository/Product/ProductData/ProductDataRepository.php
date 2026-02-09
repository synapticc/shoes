<?php

// src/Repository/Product/ProductData/ProductDataRepository.php

namespace App\Repository\Product\ProductData;

use App\Entity\Billing\Order;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductData\ProductData;
use App\Service\AttributeService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType as ArrayType;
use Doctrine\DBAL\ParameterType as Type;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method ProductData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductData[]    findAll()
 * @method ProductData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDataRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private AttributeService $a,
    ) {
        parent::__construct($registry, ProductData::class);
    }

    public function instantSearch($search): array
    {
        $em = $this->getEntityManager();

        if (!empty($search)) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search);
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");

            // Replace space with '|'
            $keywords = str_replace(' ', '|', $keywords);

            // Convert to small case.
            $keywords = strtolower($keywords);
        } else {
            return [];
        }

        $sql =
        ' SELECT DISTINCT ON(pc.clr_pvt_id)
          p.pro_pvt_id AS id,
          pr.pro_id AS "productId",
          p.pro_pvt_id AS "productDataId",
          pr.name AS "name", pr.brand AS "brand",
          pr.type AS "type",
          pc.clr_pvt_id AS "colorId", pc.color,
          pc.image_sm AS "imageSmall"
        FROM product_data p
        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
        WHERE
        ';

        $sql .= ' pr.name ~* :keywords ';
        $parameters['keywords'] = $keywords;
        $type['keywords'] = Type::STRING;

        $sql .=
          'ORDER BY pc.clr_pvt_id
         LIMIT :limit
        ';
        $conn = $this->getEntityManager()->getConnection();
        $limit = 6;
        $parameters['limit'] = $limit;
        $type['limit'] = Type::INTEGER;
        // dd($sql, $parameters, $type);
        $initialResult =
            $conn->executeQuery($sql, $parameters, $type)
                 ->fetchAllAssociative();
        // dd($initialResult);
        if (empty($initialResult)) {
            return [];
        }
        $results = [];

        foreach ($initialResult as $i => $product) {
            $results[$product['productId']][] = $product;
        }

        foreach ($results as $i => $result) {
            shuffle($results[$i]);
        }

        foreach ($results as $i => $result) {
            $results[$i] = $result[0];
            $results[$i] = $this->$a->enrichProduct($results[$i]);
        }

        return $results;
    }

    public function colors(array $productData)
    {
        $em = $this->getEntityManager();
        $id = $productData['id'];
        $product = $productData['productId'];
        $query =
          $em
            ->createQuery(
                'SELECT
                p.id, p.qtyInStock AS qty,
                pr.name, pr.brand,
                pc.id AS pcId, pc.color, pc.fabric,
                pc.imageSmall

               FROM App\Entity\Product\ProductData\ProductData p
               INNER JOIN p.product pr
               INNER JOIN p.color pc


               WHERE p.product IN (:product)
               ORDER BY p.qtyInStock DESC
               '
            );

        $query->setParameter('product', $product);
        $iniResult = $query->getResult();

        if (!empty($iniResult)) {
            foreach ($iniResult as $i => $product) {
                if (!empty($product['fabrics'])) {
                    $results[$product['pcId'].'_'.$product['color'].'_'.$product['fabrics'][0]][] = $product;
                } elseif (empty($product['fabrics'])) {
                    $results[$product['pcId'].'_'.$product['color']][] = $product;
                }
            }

            ksort($results);

            foreach ($results as $i => $products) {
                $result[$products[0]['id']] = $products[0];
            }

            foreach ($result as $i => $product) {
                $final[$i] = $this->$a->enrichProduct($product);
            }

            return $final;
        }

        return [];
    }

    public function size(array $productData)
    {
        $pc = $productData['pcId'];
        $em = $this->getEntityManager();
        $query =
          $em
            ->createQuery(
                'SELECT
                 p.id,
                 pr.name, pr.brand,
                 p.qtyInStock AS qty, p.size,
                 pc.id AS pcId

               FROM App\Entity\Product\ProductData\ProductData p
               INNER JOIN p.product pr
               INNER JOIN p.color pc
               WHERE pc.id  =  :pc
               ORDER BY p.color ASC
               '
            );
        $query->setParameter('pc', $pc);
        $iniResult = $query->getResult();

        foreach ($iniResult as $key => $product) {
            $results[$product['size']] = $product;
        }

        return $results;
    }

    public function filter(Request $request): array
    {
        $c = $this->getEntityManager()->getConnection();
        $q = $request->query;

        // Defaults
        $minPrice = $q->get('price')['min'] ?? 500;
        $maxPrice = $q->get('price')['max'] ?? 25000;
        $orderPrice = $q->get('price')['order'] ?? 'name-asc';

        $parameters = [
            'displayed' => true,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice];

        $types = [
            'displayed' => Type::BOOLEAN,
            'minPrice' => Type::INTEGER,
            'maxPrice' => Type::INTEGER];

        // Base query
        $sql = <<<SQL
            SELECT DISTINCT ON(pc.clr_pvt_id)
                p.pro_pvt_id AS id,
                p.selling_price AS "sellingPrice",
                p.size,
                p.qty_in_stock AS "qty",
                pr.pro_id AS "productId",
                pr.name, pr.brand, pr.description, pr.features,
                pr.category, pr.occasion, pr.type,
                pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
                t.texture, tg.tag,
                pc.image_md AS "imageMedium",
                img2.image_md AS "imageMedium2",
                img3.image_md AS "imageMedium3",
                img4.image_md AS "imageMedium4",
                img5.image_md AS "imageMedium5"
            FROM product_data p
            INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
            INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
            LEFT JOIN product_color_texture t ON pc.clr_pvt_id = t.fk_clr_pvt_id
            LEFT JOIN product_color_tags tg ON pc.clr_pvt_id = tg.fk_clr_pvt_id
            LEFT JOIN product_image2 img2 ON pc.clr_pvt_id = img2.fk_clr_pvt_id
            LEFT JOIN product_image3 img3 ON pc.clr_pvt_id = img3.fk_clr_pvt_id
            LEFT JOIN product_image4 img4 ON pc.clr_pvt_id = img4.fk_clr_pvt_id
            LEFT JOIN product_image5 img5 ON pc.clr_pvt_id = img5.fk_clr_pvt_id
            WHERE pr.is_displayed = :displayed
        SQL;

        // Dynamic filters
        $filters = [
            'brands' => [
                'field' => 'pr.brand',
                'values' => $q->all('brand'),
                'type' => ArrayType::STRING],

            'categories' => [
                'field' => 'pr.category',
                'values' => $q->all('category'),
                'type' => ArrayType::STRING],

            'types' => [
                'field' => 'pr.type',
                'values' => $q->all('type'),
                'type' => ArrayType::STRING],

            'sizes' => [
                'field' => 'p.size',
                'values' => $q->all('size'),
                'type' => ArrayType::INTEGER],
        ];

        foreach ($filters as $name => $f) {
            if (!empty($f['values'])) {
                $sql .= " AND {$f['field']} IN (:{$name})";
                $parameters[$name] = $f['values'];
                $types[$name] = $f['type'];
            }
        }

        // Regex filters (colors, fabrics, textures, tags)
        $this->applyRegex($sql, $parameters, $types, 'pc.color', $q->all('color'), 'colors');
        $this->applyRegex($sql, $parameters, $types, 'pc.fabric', $q->all('fabrics'), 'fabrics');
        $this->applyRegex($sql, $parameters, $types, 't.texture', $q->all('textures'), 'textures');
        $this->applyRegex($sql, $parameters, $types, 'tg.tag', $q->all('tags'), 'tags');

        // Occasions
        if ($q->has('occasion')) {
            $sql .= ' AND pr.occasion::text ~* :occasions';
            $parameters['occasions'] = implode('|', $q->all('occasion'));
            $types['occasions'] = Type::STRING;
        }

        // Full-text search
        if ($q->has('q')) {
            $terms = explode(' ', (string) $q->get('q'));
            $sql .= ' AND (';
            foreach ($terms as $i => $word) {
                $sql .= " ( to_tsvector(pr.name) @@ to_tsquery(:term{$i})
                           OR to_tsvector(pr.description) @@ to_tsquery(:term{$i})
                           OR to_tsvector(pr.features) @@ to_tsquery(:term{$i}) )";
                if ($i !== array_key_last($terms)) {
                    $sql .= ' OR';
                }
                $parameters["term{$i}"] = $word.':*';
                $types["term{$i}"] = Type::STRING;
            }
            $sql .= ' )';
        }

        // Price ranges
        if ($q->has('price_range')) {
            $ranges = $q->all('price_range');
            $sql .= ' AND (';
            foreach ($ranges as $i => $range) {
                [$low, $high] = explode('_', $range);
                $sql .= " p.selling_price BETWEEN :low{$i} AND :high{$i}";
                if ($i !== array_key_last($ranges)) {
                    $sql .= ' OR';
                }
                $parameters["low{$i}"] = (int) $low;
                $parameters["high{$i}"] = (int) $high;
                $types["low{$i}"] = Type::INTEGER;
                $types["high{$i}"] = Type::INTEGER;
            }
            $sql .= ' )';
        } else {
            $sql .= ' AND p.selling_price BETWEEN :minPrice AND :maxPrice';
        }

        // Final ORDER BY clause
        $sql .= ' ORDER BY pc.clr_pvt_id, p.qty_in_stock DESC';

        // Execute query
        $results = $c->executeQuery($sql, $parameters, $types)->fetchAllAssociative();

        // Post-processing: group colors per product
        if (!empty($results)) {
            $productSet = [];
            foreach ($results as &$product) {
                $product = $this->$a->enrichProduct($product);
                $productSet[$product['productId']][] = $product;
            }

            $colorKeys = [
                'id', 'productId', 'colorId', 'color',
                'fabrics', 'textures', 'tags',
            ];

            $colorSet = [];
            foreach ($productSet as $pid => $set) {
                foreach ($set as $product) {
                    foreach ($colorKeys as $key) {
                        if (isset($product[$key])) {
                            $colorSet[$pid][$product['colorId']][$key] = $product[$key];
                        }
                    }
                }
            }

            foreach ($results as &$product) {
                $product['colors'] = $colorSet[$product['productId']] ?? [];
            }
        }

        // Sorting (done in SQL where possible, fallback in PHP)
        switch ($orderPrice) {
            case 'price-asc':
                usort($results, fn ($a, $b) => $a['sellingPrice'] <=> $b['sellingPrice']);
                break;
            case 'price-dsc':
                usort($results, fn ($a, $b) => $b['sellingPrice'] <=> $a['sellingPrice']);
                break;
            case 'name-asc':
                usort($results, fn ($a, $b) => strcmp($a['name'], $b['name']));
                break;
            case 'name-dsc':
                usort($results, fn ($a, $b) => strcmp($b['name'], $a['name']));
                break;
            case 'color-asc':
                usort($results, fn ($a, $b) => strcmp($a['color'], $b['color']));
                break;
            case 'color-dsc':
                usort($results, fn ($a, $b) => strcmp($b['color'], $a['color']));
                break;
            case 'brand-asc':
                usort($results, fn ($a, $b) => strcmp($a['brand'], $b['brand']));
                break;
            case 'brand-dsc':
                usort($results, fn ($a, $b) => strcmp($b['brand'], $a['brand']));
                break;
            case 'relevance':
                if (!empty($results) && $q->has('q')) {
                    $terms = explode(' ', (string) $q->get('q'));
                    $ranked = [];

                    foreach ($results as $product) {
                        $score = 0;
                        foreach ($terms as $term) {
                            $term = strtolower($term);
                            if (false !== stripos($product['name'], $term)) {
                                $score += 3;
                            }
                            if (false !== stripos($product['description'], $term)) {
                                $score += 2;
                            }
                            if (false !== stripos($product['features'], $term)) {
                                ++$score;
                            }
                        }
                        $product['relevanceScore'] = $score;
                        $ranked[] = $product;
                    }

                    usort($ranked, fn ($a, $b) => $b['relevanceScore'] <=> $a['relevanceScore']);
                    $results = $ranked;
                }
                break;
        }

        return $results;
    }

    /**
     * Helper to apply regex filters consistently.
     */
    private function applyRegex(string &$sql, array &$parameters, array &$types, string $field, array $values, string $name): void
    {
        if (!empty($values)) {
            $pattern = implode('|', $values);
            $sql .= " AND {$field}::text ~* :{$name}";
            $parameters[$name] = $pattern;
            $types[$name] = Type::STRING;
        }
    }

    public function full(int $id)
    {
        $em = $this->getEntityManager();
        $query =
          $em
            ->createQuery(
                'SELECT
                 p.id,
                 pr.id  AS productId, pr.name, pr.description, pr.features,
                 pr.brand, pr.category, pr.occasion, pr.type,
                 v.videoUrl,
                 p.costPrice, p.sellingPrice, p.qtyInStock, p.size,
                 pc.id AS colorId , pc.color, pc.fabric,
                 s.id AS supplierId,
                 pc.id AS pcId , pc.color AS pcColor,
                 pc.fabric AS pcFabric, tx.texture AS pcTexture,
                 pc.imageMedium,
                 img2.imageMedium AS imageMedium2,
                 img3.imageMedium AS imageMedium3,
                 img4.imageMedium AS imageMedium4,
                 img5.imageMedium AS imageMedium5,
                 p.created

               FROM App\Entity\Product\ProductData\ProductData p

               INNER JOIN p.product pr
               LEFT JOIN p.color pc
               LEFT JOIN pc.texture tx
               LEFT JOIN pr.video v
               LEFT JOIN p.supplier sp
               LEFT JOIN sp.supplier s
               LEFT JOIN pc.productImage2 img2
               LEFT JOIN pc.productImage3 img3
               LEFT JOIN pc.productImage4 img4
               LEFT JOIN pc.productImage5 img5

               WHERE p.id = :id
               '
            );

        $query->setParameter('id', $id);
        $iniResult = $query->getResult();

        foreach ($iniResult as $i => $product) {
            $colorKeys = ['pcId', 'pcColor', 'pcFabric', 'pcTexture',
                'imageMedium', 'imageMedium2', 'imageMedium3',
                'imageMedium4', 'imageMedium5',
            ];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['id']][$product['pcId']][$key] = $product[$key];
                }
            }
            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $result[$product['id']][$key] = $product[$key];
                }
            }

            $result[$product['id']]['colors'] = $productColors[$product['id']];
        }

        $result = $this->$a->enrichProduct(array_values($result)[0]);

        return $result;
    }

    public function fetch(int $id)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
           p.id,
           pr.id  AS productId, pr.name, pr.description, pr.features,
           pr.brand, pr.category, pr.occasion, pr.type,
           d.discount, d.startDate, d.endDate,
           v.videoUrl, vc.videoUrl as vidUrl,
           p.sellingPrice, p.qtyInStock, p.size,
           pc.id AS pcId, pc.color, pc.fabric,
           tx.texture, tg.tag,

           smxc.id AS xpcid, smxc.colors as xcolors,
           exc.id AS excid, exc.color as xpcolor,
           smpc.sort AS colorSort,
           pc.image, pc.imageMedium, pc.imageSmall,

           img2.image AS image2, img2.imageMedium AS imageMedium2,
           img2.imageSmall AS imageSmall2,
           img3.image AS image3, img3.imageMedium AS imageMedium3,
           img3.imageSmall AS imageSmall3,
           img4.image AS image4, img4.imageMedium AS imageMedium4,
           img4.imageSmall AS imageSmall4,
           img5.image AS image5, img5.imageMedium AS imageMedium5,
           img5.imageSmall AS imageSmall5,

           sm.id as sid, sm.brands, sm.occasions, sm.types, sm.colors,
           sm.fabric as fabric, sm.texture as texture, sm.sizes, sm.sort,
           p.created

        FROM App\Entity\Product\ProductData\ProductData p
        INNER JOIN p.color pc
        INNER JOIN p.product pr
        LEFT JOIN pc.texture tx
        LEFT JOIN pc.tag tg
        LEFT JOIN pr.discount d
        LEFT JOIN pr.video v
        LEFT JOIN pc.video vc
        LEFT JOIN pr.similarProduct sm
        LEFT JOIN pc.similarProductColor smpc
        LEFT JOIN smpc.excludeProductColors expc
        LEFT JOIN expc.color exc
        LEFT JOIN smpc.excludeColor smxc

        LEFT JOIN pc.productImage2 img2
        LEFT JOIN pc.productImage3 img3
        LEFT JOIN pc.productImage4 img4
        LEFT JOIN pc.productImage5 img5
        WHERE p.id = :id
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $id);

        $initialResult = $query->getResult();

        $similarProductKeys =
          ['sid', 'brands', 'occasions', 'types', 'colors',
              'fabric', 'texture', 'sizes', 'sort'];

        $similarPCKeys = ['opcid'];
        $similarOtherColorKeys = ['opcid', 'ocolor', 'qtyColor'];
        $similarExcludeColorKeys = ['xpcid', 'xcolors'];
        $similarExcludePCKeys = ['excid', 'xpcolor'];

        $similarPC = [];
        $similarOC = [];
        $similarXC = [];
        $similarXPC = [];

        $keys = array_merge(
            $similarProductKeys,
            $similarPCKeys,
            $similarOtherColorKeys,
            $similarOtherColorKeys,
            $similarExcludeColorKeys,
            $similarExcludePCKeys
        );

        foreach ($initialResult as $i => $product) {
            foreach ($product as $key => $value) {
                if (in_array($key, $similarProductKeys)) {
                    $similarProduct[$key] = $product[$key];
                }
            }

            foreach ($product as $key => $value) {
                if (in_array($key, $similarPCKeys)) {
                    if (!empty($value)) {
                        $similarPC[$product['opcid']] = $product[$key];
                    }
                }
            }

            foreach ($product as $key => $value) {
                if (in_array($key, $similarOtherColorKeys)) {
                    if (!empty($value)) {
                        $similarOC[$product['opcid']][$key] = $product[$key];
                    }
                }
            }

            foreach ($product as $key => $value) {
                if (in_array($key, $similarExcludeColorKeys)) {
                    if (!empty($value)) {
                        $similarXC[$product['xpcid']][$key] = $product[$key];
                    }
                }
            }

            foreach ($product as $key => $value) {
                if (in_array($key, $similarExcludePCKeys)) {
                    if (!empty($value)) {
                        $similarXPC[$product['excid']][$key] = $product[$key];
                    }
                }
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $keys)) {
                    $result[$key] = $product[$key];
                }
            }

            $result['similarProduct'] = $similarProduct;
            $result['similarProductColor'] = $similarPC;
            $result['similarOtherColor'] = $similarOC;
            $result['similarExcludeColor'] = $similarXC;
            $result['similarExcludePC'] = $similarXPC;
        }

        $result = $this->$a->enrichProduct($result);

        return $result;
    }

    public function pc($pc)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
           p.id,
           pr.id  AS productId, pr.name, pr.description, pr.features,
           pr.brand, pr.category, pr.occasion, pr.type,
           v.videoUrl,
           p.sellingPrice, p.costPrice,
           ((p.sellingPrice - p.costPrice) / p.costPrice) AS profit,
           p.qtyInStock AS qty, p.size, p.sku,
           o.qtyOnOrder,o.reorderLevel,
           pc.id AS colorId , pc.color, pc.fabric,
           pc.image, pc.imageMedium, pc.imageSmall,
           img2.image AS image2, img2.imageMedium AS imageMedium2, img2.imageSmall AS imageSmall2,
           img3.image AS image3, img3.imageMedium AS imageMedium3, img3.imageSmall AS imageSmall3,
           img4.image AS image4, img4.imageMedium AS imageMedium4, img4.imageSmall AS imageSmall4,
           img5.image AS image5, img5.imageMedium AS imageMedium5, img5.imageSmall AS imageSmall5,
           sp.id AS supplierId, sp.name AS supplier,
           ppc.id AS ppcId , ppc.color AS pcolor, ppc.fabric AS pfabrics,
           ppc.imageMedium AS pimageMedium,
           p.created, p.updated

         FROM App\Entity\Product\ProductData\ProductData p

         LEFT JOIN p.color pc
         LEFT JOIN p.product pr
         LEFT JOIN pr.productColor ppc
         LEFT JOIN pr.video v
         LEFT JOIN p.productDataOrder o

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN p.supplier spp
         LEFT JOIN spp.supplier sp
         LEFT JOIN ppc.productImage pimg

         WHERE pc.id = :pc
         -- AND pc.id != ppc.id
         ORDER BY ppc.color ASC
         ';
        $query = $em->createQuery($dql);
        $query->setParameter('pc', $pc);
        $iniResult = $query->getResult();
        foreach ($iniResult as $i => $p) {
            $iniResult[$i] = $this->$a->enrichProduct($p);
            $iniResult[$i]['pcolors_full'] =
              $this->nameColor($iniResult[$i]['pcolor']);
        }
        $result = [];
        $pcKeys = ['ppcId', 'pcolor', 'pcolors_full', 'pfabrics', 'pimageMedium'];

        foreach ($iniResult as $i => $product) {
            foreach ($product as $key => $value) {
                if (in_array($key, $pcKeys)) {
                    $pcColors[$product['id']][$product['ppcId']][$key] = $product[$key];
                }
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $pcKeys)) {
                    $result[$product['id']][$key] = $product[$key];
                }
            }

            $result[$product['id']]['pc'] = $pcColors[$product['id']];
        }

        return $result;
    }

    public function recent($products, int $productData)
    {
        if (empty($products)) {
            return [];
        }

        $em = $this->getEntityManager();
        $dql =
        'SELECT
           p.id, p.sellingPrice, p.size,
           pr.name, pr.brand,
           pr.category, pr.occasion, pr.type,
           pc.id AS colorId , pc.color, pc.fabric,
           pc.imageMedium,
           img2.imageMedium AS imageMedium2,
           img3.imageMedium AS imageMedium3,
           img4.imageMedium AS imageMedium4,
           img5.imageMedium AS imageMedium5

         FROM App\Entity\Product\ProductData\ProductData p
         INNER JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5

         WHERE p.id IN (:products)
         AND p.id != :productData
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('products', $products);
        $query->setParameter('productData', $productData);
        $iniResult = $query->setMaxResults(10)->getResult();
        $result = [];

        // Sort according to the ids in array $id
        // NOTE: PostGres(doctrine) is currently not well
        // equipped to sort by another array.
        // Since we have to sort for only 10 items,
        // we can do it after the query.
        foreach ($products as $i => $sort) {
            foreach ($iniResult as $k => $item) {
                if ($item['id'] == $sort) {
                    array_push($result, $item);
                }
            }
        }

        foreach ($result as $i => $product) {
            $result[$i] = $this->$a->enrichProduct($product);
        }

        return $result;
    }

    public function similar(array $product): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $category = $product['category'];
        $excludeColor = [$product['pcId']];
        $smp = $product['similarProduct'];
        // $sort = $smp['sort'];
        $sort = (!empty($smp['sort'])) ? $smp['sort'] : [];
        $count = array_count_values($sort);
        $pcID = array_filter($sort, 'is_numeric');

        $b = 'brand';
        $_b = 'brands';
        $o = 'occasion';
        $_o = 'occasions';
        $t = 'type';
        $_t = 'types';
        $c = 'color';
        $_c = 'colors';
        $f = 'fabric';
        $_f = 'fabrics';
        $te = 'texture';
        $_te = 'textures';
        $s = 'sizes';
        $bt = 'brand-type';
        $bc = 'brand-color';
        $bo = 'brand-occasion';
        $bto = 'brand-type-occasion';
        $boc = 'brand-occasion-color';
        $bot = 'brand-occasion-type';
        $bct = 'brand-color-type';
        $ct = 'color-type';
        $co = 'color-occasion';
        $cf = 'color-fabric';
        $cte = 'color-texture';
        $ft = 'fabric-type';
        $fte = 'fabric-texture';
        $bsel = 'best_seller';
        $brev = 'best_review';
        $pc = 'similarProductColor';
        $oc = 'similarOtherColor';
        $xc = 'similarExcludeColor';
        $xpc = 'similarExcludePC';
        $cs = 'colorSort';
        $occasionText = '';
        $ft = 'features';
        $dsc = 'description';

        $resultBr = [];
        $resultOc = [];
        $resultCo = [];
        $resultTy = [];
        $resultFa = [];
        $resultTe = [];
        $resultSi = [];
        $resultBrTy = [];
        $resultBrCo = [];
        $resultBrCoTy = [];
        $resultBrOc = [];
        $resultBrOcTy = [];
        $resultBrOcCo = [];
        $resultCoTy = [];
        $resultCoOc = [];
        $resultCoFa = [];
        $resultCoTe = [];
        $resultFaTy = [];
        $resultFaTe = [];
        $results = [];
        $sorted = [];
        $resultPC = [];
        $resultSmCo = [];
        $resultSmProCo = [];
        $resultFt = [];
        $resultDsc = [];

        $brands = !empty($smp[$_b]) ? $smp[$_b] : $product[$b];
        $types = !empty($smp[$_t]) ? $smp[$_t] : $product[$t];
        $occasions = !empty($smp[$_o]) ? $smp[$_o] : $product[$o];

        $description = !empty($product[$dsc]) ? $product[$dsc] : '';
        $features = !empty($product[$ft]) ? $product[$ft] : '';

        /* Color specificity
          1) Its own colors
          2) Similar colors added to each ProductColor.
          3) Similar colors added to each Product (parent level).
        */
        $colors = explode('-', $product[$c]);
        $fabrics = $smp[$f];
        $textures = !empty($smp[$_te]) ? $smp[$_te] : '';

        $similarPC = !empty($pc) ? $product[$pc] : '';
        $similarOC = !empty($oc) ? $product[$oc] : '';
        $similarXC = !empty($xc) ? $product[$xc] : '';
        $similarXPC = !empty($xpc) ? $product[$xpc] : '';
        $similarColors = !empty($cs) ? $product[$cs] : '';

        /* Extract quantity for each paramter or combination of parameters */

        // SINGLE PARAMETER
        // Quantity for brand only
        $qtyBr = (int) isset($count[$b]) ? $count[$b] : 0;
        // Quantity for occasion only
        $qtyOc = (int) isset($count[$o]) ? $count[$o] : 0;
        // Quantity for type only
        $qtyTy = (int) isset($count[$t]) ? $count[$t] : 0;
        // Quantity for color only
        $qtyCo = (int) isset($count[$c]) ? $count[$c] : 0;
        // Quantity for fabric only
        $qtyFa = (int) isset($count[$f]) ? $count[$f] : 0;
        // Quantity for texture only
        $qtyTe = (int) isset($count[$te]) ? $count[$te] : 0;
        // Quantity for size only
        $qtySz = (int) isset($count[$s]) ? $count[$s] : 0;

        /* COMBINATION OF PARAMETERS */
        // Quantity for brand and type
        $qtyBrTy = (int) isset($count[$bt]) ? $count[$bt] : 0;
        // Quantity for brand and color
        $qtyBrCo = (int) isset($count[$bc]) ? $count[$bc] : 0;
        // Quantity for brand and color and type
        $qtyBrCoTy = (int) isset($count[$bct]) ? $count[$bct] : 0;
        // Quantity for brand and occasion
        $qtyBrOc = (int) isset($count[$bo]) ? $count[$bo] : 0;
        // Quantity for brand and occasion and type
        $qtyBrOcTy = (int) isset($count[$bot]) ? $count[$bot] : 0;
        // Quantity for brand and occasion and color
        $qtyBrOcCo = (int) isset($count[$boc]) ? $count[$boc] : 0;

        // Quantity for color and type
        $qtyCoTy = (int) isset($count[$ct]) ? $count[$ct] : 0;
        // Quantity for color and occasion
        $qtyCoOc = (int) isset($count[$co]) ? $count[$co] : 0;
        // Quantity for color and fabric
        $qtyCoFa = (int) isset($count[$cf]) ? $count[$cf] : 0;
        // Quantity for color and texture
        $qtyCoTe = (int) isset($count[$cte]) ? $count[$cte] : 0;
        // Quantity for fabric and type
        $qtyFaTy = (int) isset($count[$ft]) ? $count[$ft] : 0;
        // Quantity for fabric and texture
        $qtyFaTe = (int) isset($count[$fte]) ? $count[$fte] : 0;

        // Quantity for features
        $qtyFt = (int) isset($count[$ft]) ? $count[$ft] : 0;
        // Quantity for description
        $qtyDsc = (int) isset($count[$dsc]) ? $count[$dsc] : 0;

        $intro =
          'SELECT distinct on(pc.clr_pvt_id)
          p.pro_pvt_id AS id,
          p.selling_price AS "sellingPrice",
          p.size,
          p.qty_in_stock AS qty,
          pr.pro_id AS productId,
          pr.name AS name,  pr.brand AS brand,
          pr.category AS category, pr.occasion AS occasion,
          pr.type AS type,
          d.discount, d.start_date, d.end_date,
          pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
          pc.image_md AS "imageMedium"

        FROM product_data p

        INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
        LEFT JOIN product_discount d ON pr.pro_id = d.fk_pro_id
        ';
        $end =
          ' GROUP BY
          p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
          pr.name,  pr.brand, d.discount,
          d.start_date, d.end_date, pc.clr_pvt_id,
          pc.image_md,
          pc.color
        LIMIT ?
          -- ORDER BY id ASC
        ';

        if (!empty($similarColors)) {
            $productColorKeys = [];
            $colorKeys = [];
            $similarKeys = array_count_values($similarColors);
            /* Separate colors from product ID */
            foreach ($similarKeys as $key => $clr) {
                if ('string' == gettype($key)) {
                    $colorKeys[$key] = $clr;
                }

                if ('integer' == gettype($key)) {
                    $productColorKeys[] = $key;
                }
            }

            if (!empty($colorKeys)) {
                foreach ($colorKeys as $color => $qtyColor) {
                    $condition =
                      'WHERE pr.category = ?
               AND pc.clr_pvt_id NOT IN (?)
               AND
                ( pr.brand IN (?) OR
                  pr.type IN (?)
                )
               ';
                    $condition = $condition.' AND pc.color ~~ ?  ';
                    $sqlColor = $intro.$condition.$end;
                    $parameters = [$category, $excludeColor, $brands,
                        $types, $color, $qtyColor];
                    $type = [Type::STRING,       // $category
                        ArrayType::INTEGER, // $excludeColor
                        ArrayType::STRING,  // $brands
                        ArrayType::STRING,  // $types
                        Type::STRING,       // $color
                        Type::INTEGER,       // $qtyColor
                    ];
                    $iniColor[$color][] =
                        $conn->executeQuery($sqlColor, $parameters, $type)
                             ->fetchAllAssociative();
                }

                if (!empty($iniColor)) {
                    foreach ($iniColor as $i => $productSet) {
                        foreach ($productSet as $j => $product) {
                            if (!empty($product)) {
                                $excludeColor[] = $product[0]['colorId'];
                                $resultSmCo[] =
                                  ['list' => $i,
                                      'product' => $this->$a->enrichProduct($product[0])];
                            }
                        }
                        $excludeColor = array_unique($excludeColor);
                    }
                }
            }

            if (!empty($productColorKeys)) {
                foreach ($productColorKeys as $i => $id) {
                    $condition = ' WHERE pc.clr_pvt_id = ? ';
                    $sqlProductColor = $intro.$condition.$end;
                    $parameters = [$id, 1];
                    $type = [Type::INTEGER,    // $id
                        Type::INTEGER];  // LIMIT

                    $iniProductColor[$id] =
                        $conn->executeQuery($sqlProductColor, $parameters, $type)
                             ->fetchAllAssociative();
                }

                if (!empty($iniProductColor)) {
                    foreach ($iniProductColor as $i => $product) {
                        $excludeColor[] = $product[0]['colorId'];
                        $resultSmProCo[] =
                          ['list' => $i,
                              'product' => $this->$a->enrichProduct($product[0])];
                    }
                    $excludeColor = array_unique($excludeColor);
                }
            }

            $unsorted = array_merge($resultSmCo, $resultSmProCo);
            $sortKeys = array_count_values($similarColors);

            foreach ($unsorted as $key => $product) {
                $unsortSet[$product['list']][] = $product['product'];
            }

            foreach ($sortKeys as $list => $keys) {
                foreach ($unsorted as $k => $unsort) {
                    if ($list == $unsort['list']) {
                        $sorted[$unsort['product']['id'].'-'.$list] = $unsort['product'];
                    }
                }
            }

            $unsorted = [];
            $sortKeys = [];
            $unsortSet = [];
        }

        if (!empty($qtyBr) && !empty($brands)) {
            $condition =
             ' WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           ';

            $sqlBrand = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands, $qtyBr];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $colorId
                ArrayType::STRING,  // $brands
                Type::INTEGER,       // $qtyBrand
            ];
            $iniBr =
                $conn->executeQuery($sqlBrand, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBr as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBr[$i] =
                  ['list' => 'brand',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyOc) && !empty($occasions)) {
            $condition =
             ' WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.occasion::text ~* ?
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
           LIMIT ?
            -- ORDER BY id ASC
          ';

            foreach ($occasions as $i => $occasion) {
                $occasionText = ($i != array_key_last($occasions)) ? $occasionText.$occasion.'|'
                : $occasionText = $occasionText.$occasion;
            }

            $sqlOccasion = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $occasionText, $qtyOc];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,  // $occasionText
                Type::INTEGER,       // $qtyOccasion
            ];
            $iniOc =
                $conn->executeQuery($sqlOccasion, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniOc as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBr[$i] =
                  ['list' => 'occasion',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyTy) && !empty($types)) {
            $condition =
             ' WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.type IN (?)
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
          LIMIT ?
            -- ORDER BY id ASC
          ';
            $sqlType = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $types, $qtyTy];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $types
                Type::INTEGER,       // $qtyType
            ];
            $iniTy =
                $conn->executeQuery($sqlType, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultTy[$i] =
                  ['list' => 'type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtySz) && !empty($sizes)) {
            $condition =
             ' WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND p.size = (?)
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
           LIMIT ?
            -- ORDER BY id ASC
          ';
            $sqlSize = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $types, $qtySz];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $types
                Type::INTEGER,       // $qtySize
            ];
            $iniSz =
                $conn->executeQuery($sqlType, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniSz as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultSz[$i] =
                  ['list' => 'size',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyCo) && !empty($colors)) {
            $condition =
            ' WHERE pr.category = ?
          AND pc.clr_pvt_id NOT IN (?)
         ';

            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $condition = $condition.' AND pc.color ~* ?  ';
            $sqlColor = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $colorSet, $qtyCo];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyColor
            ];
            $iniCo =
                $conn->executeQuery($sqlColor, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniCo as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultOc[$i] =
                  ['list' => 'color',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyFa) && !empty($fabrics)) {
            $condition =
            ' WHERE pr.category = ?
          AND pc.clr_pvt_id NOT IN (?)
         ';

            $fabricSet = '';
            foreach ($fabrics as $i => $fabric) {
                $fabricSet = ($i != array_key_last($fabrics)) ? $fabricSet.$fabric.'|'
                : $fabricSet = $fabricSet.$fabric;
            }

            $condition = $condition.' AND pc.fabric::text  ~* ?  ';
            $sqlFabric = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $fabricSet, $qtyFa];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $fabricSet
                Type::INTEGER,       // $qtyFaBrics
            ];
            $iniFa =
                $conn->executeQuery($sqlFabric, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniFa as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultFa[$i] =
                  ['list' => 'fabric',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyTe) && !empty($textures)) {
            $condition =
            ' WHERE pr.category = ?
          AND pc.clr_pvt_id NOT IN (?)
         ';

            $textureSet = '';
            foreach ($textures as $i => $texture) {
                $textureSet = ($i != array_key_last($textures)) ? $textureSet.$texture.'|'
                : $textureSet = $textureSet.$texture;
            }

            $condition = $condition.' AND t.texture::text  ~* ?  ';
            $sqlTexture = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $textureSet, $qtyTe];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $textureSet
                Type::INTEGER,       // $qtyTexture
            ];
            $iniTe =
                $conn->executeQuery($sqlTexture, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniTe as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultTe[$i] =
                  ['list' => 'texture',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrOc) && !empty($brands) && !empty($occasions)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           AND pr.occasion::text ~* ?
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
           LIMIT ?
            -- ORDER BY id ASC
          ';

            foreach ($occasions as $i => $occasion) {
                $occasionText = ($i != array_key_last($occasions)) ? $occasionText.$occasion.'|'
                : $occasionText = $occasionText.$occasion;
            }

            $sqlBrandOccasion = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands, $occasionText, $qtyBrOc];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                Type::STRING,  // $occasionText
                Type::INTEGER,       // $qtyBrandOccasion
            ];
            $iniBrOc =
                $conn->executeQuery($sqlBrandOccasion, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrOc as $i => $product) {
                $excludeColor[] = $product['colorId'];
                // $resultBrOc['brand-occasion'][] = $this->$a->enrichProduct($product);
                $resultBrOc[$i] =
                  ['list' => 'brand-occasion',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrTy) && !empty($brands) && !empty($types)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           AND pr.type IN (?)
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
           LIMIT ?
            -- ORDER BY id ASC
          ';
            $sqlBrandType = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands, $types, $qtyBrTy];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                ArrayType::STRING,  // $types
                Type::INTEGER,       // $qtyBrTy
            ];
            $iniBrTy =
                $conn->executeQuery($sqlBrandType, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBrTy[$i] =
                  ['list' => 'brand-type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrCo) && !empty($brands) && !empty($colors)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $condition = $condition.' AND pc.color  ~* ?  ';
            $sqlBrandColor = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands, $colorSet, $qtyBrCo];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyBrandColor
            ];
            $iniBrCo =
                $conn->executeQuery($sqlBrandColor, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrCo as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBrCo[$i] =
                  ['list' => 'brand-color',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrCoTy) && !empty($brands)
            && !empty($colors) && !empty($types)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           AND pr.type IN (?)
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $condition = $condition.' AND pc.color  ~* ?  ';
            $sqlBrCoTy = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands,
                $types, $colorSet, $qtyBrCoTy];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                ArrayType::STRING,  // $types
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyBrCoTy
            ];
            $iniBrCoTy =
                $conn->executeQuery($sqlBrCoTy, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrCoTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBrCoTy[$i] =
                  ['list' => 'brand-color-type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrOcTy) && !empty($brands)
            && !empty($occasions) && !empty($types)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           AND pr.type IN (?)
           AND pr.occasion::text ~* ?
           ';
            $end =
              'GROUP BY
            p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
            pr.name,  pr.brand, pc.clr_pvt_id,
            d.discount,
            d.start_date, d.end_date,
            pc.image_md,
            pc.color
           LIMIT ?
            -- ORDER BY id ASC
          ';

            foreach ($occasions as $i => $occasion) {
                $occasionText = ($i != array_key_last($occasions)) ? $occasionText.$occasion.'|'
                : $occasionText = $occasionText.$occasion;
            }

            $sqlBrandOccasionType = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands,
                $occasionText, $types, $qtyBrOcTy];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                ArrayType::STRING,  // $types
                Type::STRING,  // $occasionText
                Type::INTEGER,       // $qtyBrandOccasionType
            ];
            $iniBrOcTy =
                $conn->executeQuery($sqlBrandOccasionType, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrOcTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBrOcTy[$i] =
                  ['list' => 'brand-occasion-type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyBrOcCo) && !empty($brands)
            && !empty($occasions) && !empty($colors)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.brand IN (?)
           AND pr.occasion::text ~* ?
           AND pc.color  ~* ?
           ';

            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            foreach ($occasions as $i => $occasion) {
                $occasionText = ($i != array_key_last($occasions)) ? $occasionText.$occasion.'|'
                : $occasionText = $occasionText.$occasion;
            }

            $sqlBrandOccasionColor = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $brands, $occasionText,
                $colorSet, $qtyBrOcCo];
            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $brands
                Type::STRING,  // $occasionText
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyBrandOccasionColor
            ];
            $iniBrOcCo =
                $conn->executeQuery($sqlBrandOccasionColor, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniBrOcCo as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultBrOcCo[$i] =
                  ['list' => 'brand-occasion-color',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyCoTy) && !empty($colors) && !empty($types)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.type IN (?)
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $condition = $condition.' AND pc.color  ~* ?  ';
            $sqlCoTy = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $types, $colorSet, $qtyCoTy];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $types
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyCoTy
            ];
            $iniCoTy =
                $conn->executeQuery($sqlCoTy, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniCoTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultCoTy[$i] =
                  ['list' => 'color-type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyCoOc) && !empty($colors) && !empty($occasions)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.occasion::text ~* ?
           AND pc.color  ~* ?
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            foreach ($occasions as $i => $occasion) {
                $occasionText = ($i != array_key_last($occasions)) ? $occasionText.$occasion.'|'
                : $occasionText = $occasionText.$occasion;
            }

            $sqlColorOccasion = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $occasionText, $colorSet, $qtyCoOc];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,  // $occasionText
                Type::STRING,       // $colorSet
                Type::INTEGER,       // $qtyColorOccasion
            ];
            $iniCoOc =
                $conn->executeQuery($sqlColorOccasion, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniCoOc as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultCoOc[$i] =
                  ['list' => 'color-occasion',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyCoFa) && !empty($colors) && !empty($fabrics)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $fabricSet = '';
            foreach ($fabrics as $i => $fabric) {
                $fabricSet = ($i != array_key_last($fabrics)) ? $fabricSet.$fabric.'|'
                : $fabricSet = $fabricSet.$fabric;
            }

            $condition = $condition.' AND pc.color ~* ? AND pc.fabric::text ~* ? ';
            $sqlColorFabric = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $colorSet, $fabricSet, $qtyCoFa];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $colorSet
                Type::STRING,       // $fabricSet
                Type::INTEGER,       // $qtyColorFabric
            ];
            $iniCoFa =
                $conn->executeQuery($sqlColorFabric, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniCoFa as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultCoFa[$i] =
                  ['list' => 'color-fabric',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyCoTe) && !empty($colors) && !empty($textures)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           ';
            $colorSet = '';
            foreach ($colors as $i => $color) {
                $colorSet = ($i != array_key_last($colors)) ? $colorSet.$color.'|'
                : $colorSet = $colorSet.$color;
            }

            $textureSet = '';
            foreach ($textures as $i => $texture) {
                $textureSet = ($i != array_key_last($textures)) ? $textureSet.$texture.'|'
                : $textureSet = $textureSet.$texture;
            }

            $condition = $condition.' AND pc.color  ~* ? AND pc.texture  ~* ?  ';
            $sqlColorTexture = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $colorSet, $textureSet, $qtyCoTe];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $colorSet
                Type::STRING,       // $textureSet
                Type::INTEGER,       // $qtyColorTexture
            ];
            $iniCoTe =
                $conn->executeQuery($sqlColorTexture, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniCoTe as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultCoTe[$i] =
                  ['list' => 'color-texture',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyFaTy) && !empty($fabrics) && !empty($types)) {
            $condition =
              'WHERE pr.category = ?
           AND pc.clr_pvt_id NOT IN (?)
           AND pr.type IN (?)
           ';
            $fabricSet = '';
            foreach ($fabrics as $i => $fabric) {
                $fabricSet = ($i != array_key_last($fabrics)) ? $fabricSet.$fabric.'|'
                : $fabricSet = $fabricSet.$fabric;
            }

            $condition = $condition.' AND pc.fabric::text  ~* ?  ';
            $sqlFabricType = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $types, $fabricSet, $qtyFaTy];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                ArrayType::STRING,  // $types
                Type::STRING,       // $fabricSet
                Type::INTEGER,       // $qtyFabricType
            ];
            $iniFaTy =
                $conn->executeQuery($sqlFabricType, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniFaTy as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultFaTy[$i] =
                  ['list' => 'fabric-type',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyFaTe) && !empty($fabrics) && !empty($textures)) {
            $condition =
              ' WHERE pr.category = ?
            AND pc.clr_pvt_id NOT IN (?)
           ';

            $fabricSet = '';
            foreach ($fabrics as $i => $fabric) {
                $fabricSet = ($i != array_key_last($fabrics)) ? $fabricSet.$fabric.'|'
                : $fabricSet = $fabricSet.$fabric;
            }

            $textureSet = '';
            foreach ($textures as $i => $texture) {
                $textureSet = ($i != array_key_last($textures)) ? $textureSet.$texture.'|'
                : $textureSet = $textureSet.$texture;
            }

            $condition = $condition.' AND pc.fabric::text  ~* ? AND pc.texture::text  ~* ?  ';
            $sqlFabricTexture = $intro.$condition.$end;
            $parameters = [$category, $excludeColor, $fabricSet, $textureSet, $qtyFaTe];

            $type = [Type::STRING,       // $category
                ArrayType::INTEGER, // $excludeColor
                Type::STRING,       // $fabricSet
                Type::STRING,       // $textureSet
                Type::INTEGER,       // $qtyFabricTexture
            ];
            $iniFaTe =
                $conn->executeQuery($sqlFabricTexture, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniFaTe as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultFaTe[$i] =
                  ['list' => 'fabric-texture',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyDsc) && !empty($description)) {
            $condition =
              ' WHERE pr.category = ?
              AND pc.clr_pvt_id NOT IN (?)
             ';

            // Remove special characters
            $description = preg_replace('/[^A-Za-z0-9\\-]/', ' ', $description);
            // Replace multiple consecutive whitespace characters with a single space
            $word = preg_replace('/\\s+/', ' ', $description);
            // Remove beginning and end whitespace
            $description = trim($description);
            // Replace whitespace with '|'
            $description = preg_replace('/\s+/', '|', $description);

            $parameters = [$description, $description,
                $category, $excludeColor, $qtyDsc];
            $type = [Type::STRING, // $description
                Type::STRING, // $description
                Type::STRING, // $category
                ArrayType::INTEGER, // $excludeColor
                Type::INTEGER,
            ];

            $sqlDescription =
              'SELECT distinct on(rank, pc.clr_pvt_id)
              p.pro_pvt_id AS id,
              p.selling_price AS "sellingPrice",
              p.size,
              p.qty_in_stock AS qty,
              pr.pro_id AS productId,
              pr.name AS name,  pr.brand AS brand,
              pr.category AS category, pr.occasion AS occasion,
              pr.type AS type,
              d.discount, d.start_date, d.end_date,
              pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
              pc.image_md AS "imageMedium",
              ts_rank(pr.search_vector, to_tsquery(?)) AS rank

            FROM product_data p

            INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
            INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
            LEFT JOIN product_discount d ON pr.pro_id = d.fk_pro_id

            WHERE to_tsvector(pr.description) @@ to_tsquery(?)
            AND pr.category = ?
            AND pc.clr_pvt_id NOT IN (?)
            GROUP BY
              p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
              pr.name,  pr.brand, d.discount,
              d.start_date, d.end_date, pc.clr_pvt_id,
              pc.image_md,
              pc.color
            ORDER BY rank DESC, pc.clr_pvt_id ASC
            LIMIT ?
            ';

            $iniDsc =
                  $conn->executeQuery($sqlDescription, $parameters, $type)
                       ->fetchAllAssociative();

            foreach ($iniDsc as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultDsc[$i] =
                  ['list' => 'description',
                      'product' => $this->$a->enrichProduct($product)];
            }
            $excludeColor = array_unique($excludeColor);
        }

        if (!empty($qtyFt) && !empty($features)) {
            $condition =
              ' WHERE pr.category = ?
              AND pc.clr_pvt_id NOT IN (?)
             ';

            $featureSet = '';
            foreach ($features as $i => $feature) {
                $featureSet = $featureSet.' '.$feature;
            }

            // Example => "Knit|mesh|synthetic|upper|Lace-up|closure|Soft|fresh|foam|x"

            // Remove special characters
            $word = preg_replace('/[^A-Za-z0-9\\-]/', ' ', $featureSet);
            // Replace multiple consecutive whitespace characters with a single space
            $word = preg_replace('/\\s+/', ' ', $word);
            // Remove beginning and end whitespace
            $word = trim($word);
            // Replace whitespace with '|'
            $searchQuery = preg_replace('/\s+/', '|', $word);

            $parameters = [$searchQuery, $searchQuery,
                $category, $excludeColor, $qtyFt];
            $type = [Type::STRING, // $searchQuery
                Type::STRING, // $searchQuery
                Type::STRING, // $category
                ArrayType::INTEGER, // $excludeColor
                Type::INTEGER,
            ];

            $sqlFeatures =
              'SELECT distinct on(rank, pc.clr_pvt_id)
              p.pro_pvt_id AS id,
              p.selling_price AS "sellingPrice",
              p.size,
              p.qty_in_stock AS qty,
              pr.pro_id AS productId,
              pr.name AS name,  pr.brand AS brand,
              pr.category AS category, pr.occasion AS occasion,
              pr.type AS type,
              d.discount, d.start_date, d.end_date,
              pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
              pc.image_md AS "imageMedium",
              ts_rank(pr.search_vector, to_tsquery(?)) AS rank

            FROM product_data p

            INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
            INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
            LEFT JOIN product_discount d ON pr.pro_id = d.fk_pro_id

            WHERE to_tsvector(pr.features) @@ to_tsquery(?)
            AND pr.category = ?
            AND pc.clr_pvt_id NOT IN (?)
            GROUP BY
              p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
              pr.name,  pr.brand, d.discount,
              d.start_date, d.end_date, pc.clr_pvt_id,
              pc.image_md,
              pc.color
            ORDER BY rank DESC, pc.clr_pvt_id ASC
            LIMIT ?
            ';

            $iniFt =
                $conn->executeQuery($sqlFeatures, $parameters, $type)
                     ->fetchAllAssociative();

            foreach ($iniFt as $i => $product) {
                $excludeColor[] = $product['colorId'];
                $resultFt[$i] =
                  ['list' => 'features',
                      'product' => $this->$a->enrichProduct($product)];
            }
        }

        /* Check for duplicates */
        if (!empty($pcID)) {
            if (count($pcID) !== count(array_count_values($pcID))) {
                $colorLimit = array_count_values($pcID);

                foreach ($colorLimit as $color => $limit) {
                    $intro =
                      'SELECT distinct on(p.size)
                p.pro_pvt_id AS id,
                p.selling_price AS "sellingPrice",
                p.size,
                p.qty_in_stock AS qty,
                pr.pro_id AS productId,
                pr.name AS name,  pr.brand AS brand,
                pr.category AS category, pr.occasion AS occasion,
                pr.type AS type,
                d.discount, d.start_date, d.end_date,
                pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
                pc.image_md AS "imageMedium"

              FROM product_data p

              INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
              INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
              LEFT JOIN product_discount d ON pr.pro_id = d.fk_pro_id
              ';

                    $condition =
                    ' WHERE pc.clr_pvt_id = ?
             ';
                    $end =
                      'GROUP BY
                p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
                pr.name,  pr.brand, d.discount,
                d.start_date, d.end_date, pc.clr_pvt_id,
                pc.image_md, pc.color
              LIMIT ?
              ';

                    $sqlColor = $intro.$condition.$end;
                    $parameters = [$color, $limit];
                    $type = [Type::INTEGER, // $color
                        Type::INTEGER,  // $limit
                    ];

                    $iniPC =
                        $conn->executeQuery($sqlColor, $parameters, $type)
                             ->fetchAllAssociative();

                    foreach ($iniPC as $i => $product) {
                        $resultPC[$product['id']] =
                          ['list' => $product['colorId'],
                              'product' => $this->$a->enrichProduct($product)];
                    }
                }
            } else {
                $condition =
                ' WHERE pc.clr_pvt_id IN (?)
           ';
                $end =
                  'GROUP BY
              p.pro_pvt_id, p.selling_price, p.size, pr.pro_id,
              pr.name,  pr.brand, d.discount,
              d.start_date, d.end_date, pc.clr_pvt_id,
              pc.image_md,
              pc.color
            ';

                $sqlPC = $intro.$condition.$end;
                $parameters = [$pcID];
                $type = [ArrayType::INTEGER, // $pcID
                ];

                $iniPC =
                    $conn->executeQuery($sqlPC, $parameters, $type)
                         ->fetchAllAssociative();

                foreach ($iniPC as $i => $product) {
                    $resultPC[$i] =
                      ['list' => $product['colorId'],
                          'product' => $this->$a->enrichProduct($product)];
                }
            }
        }

        $unsorted =
            array_merge(
                $resultBr,
                $resultOc,
                $resultCo,
                $resultTy,
                $resultFa,
                $resultTe,
                $resultSi,
                $resultBrTy,
                $resultBrCo,
                $resultBrCoTy,
                $resultBrOc,
                $resultBrOcTy,
                $resultBrOcCo,
                $resultCoTy,
                $resultCoOc,
                $resultCoFa,
                $resultCoTe,
                $resultFaTy,
                $resultFaTe,
                $resultFt,
                $resultDsc,
                $resultPC
            );

        foreach ($sort as $i => $sortValue) {
            foreach ($unsorted as $j => $product) {
                if ($product['list'] == $sortValue) {
                    $sorted[$product['product']['id'].'-'.$sortValue] = $product['product'];
                    unset($unsorted[$j]);
                    break;
                }
            }
        }

        return $sorted;

        // // Sort according to the ids in array $id
        // // NOTE: PostGres(doctrine) is currently not
        // // equipped to sort by another array.
        // // Since we have to sort for only 10 items,
        // // we can do it after the query.
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
}
