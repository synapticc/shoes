<?php

// src/Repository/Product/Product/ProductRepository.php

namespace App\Repository\Product\Product;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *                                                                                                    // NOTE:  findBy([]) is equivalent to findAll()
 */
class ProductRepository extends ServiceEntityRepository
{
    use Attributes;

    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $_em,
    ) {
        parent::__construct($registry, Product::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Product $entity, bool $flush = true): void
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
    public function remove(Product $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Product[] Returns an array of Product array
     *
     * Search product based on :
     *  - brand
     *  - category
     *  - occasion
     *  - type
     */
    public function search(Search $search, $query): array
    {
        $em = $this->getEntityManager();
        $order = $query->has('order') ? $query->get('order') : 'DESC';
        $sort = $query->has('sort') ? $query->get('sort') : 'pr.updated';
        $brand = !empty($search->brands()) ? $search->brands() : '';
        $category = !empty($search->categories()) ? $search->categories() : '';
        $occasions = !empty($search->occasions()) ? $search->occasions() : '';
        $type = !empty($search->types()) ? $search->types() : '';
        $colors = !empty($search->colors()) ? $search->colors() : '';
        $fabrics = !empty($search->fabrics()) ? $search->fabrics() : '';
        $textures = !empty($search->textures()) ? $search->textures() : '';
        $tags = !empty($search->tags()) ? $search->tags() : '';

        $keywords = [];

        if (empty($brand) and empty($occasions) and empty($category)
            and empty($type) and empty($colors) and empty($fabrics)
            and empty($textures) and empty($tags) and empty($search->search())) {
            return [];
        }

        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Replace whitespace with '|'
            $keywordsPhrase = preg_replace('/\s+/', '|', $keywords);
        }

        $dql =
        'SELECT
         pr.id, pr.name, pr.brand,
         pr.description, pr.features,
         pr.category,pr.occasion, pr.type,
         pr.displayed, pr.displayDate, q.qtyPack, v.videoUrl,
         pr.created, pr.updated,
         pc.id AS colorId, pc.color, pc.fabric, tx.texture, tg.tag,
         pc.image, pc.imageMedium, pc.imageSmall,
         img2.image AS image2, img2.imageMedium AS imageMedium2,
         img2.imageSmall AS imageSmall2,
         img3.image AS image3, img3.imageMedium AS imageMedium3,
         img3.imageSmall AS imageSmall3,
         img4.image AS image4, img4.imageMedium AS imageMedium4,
         img4.imageSmall AS imageSmall4,
         img5.image AS image5, img5.imageMedium AS imageMedium5,
         img5.imageSmall AS imageSmall5
         __search_rank__

       FROM App\Entity\Product\Product\Product pr
       LEFT JOIN pr.productColor pc
       LEFT JOIN pc.texture tx
       LEFT JOIN pc.tag tg

       LEFT JOIN pc.productImage2 img2
       LEFT JOIN pc.productImage3 img3
       LEFT JOIN pc.productImage4 img4
       LEFT JOIN pc.productImage5 img5
       LEFT JOIN pr.qtyPack q
       LEFT JOIN pr.video v
       WHERE ';

        if (!empty($brand)) {
            $dql .= ' pr.brand IN (:brand) ';
        }

        if (!empty($category)) {
            if (!empty($brand)) {
                $dql .= ' AND ';
            }

            $dql .= ' pr.category IN (:category)';
        }

        if (!empty($occasions)) {
            if (!empty($category) or !empty($brand)) {
                $dql .= ' AND  ';
            }

            // foreach ($occasions as $i => $occasion)
            //   $dql .= " (ILIKE(JSON_TEXT(pr.occasion), :occasion$i) = true) " .
            //           ( (end($occasions) != $occasion) ? " OR " : "" );

            $dql .= ' ( ';

            foreach ($occasions as $i => $occasion) {
                $dql .= " (CONTAINS(pr.occasion, :occasion$i) = TRUE) ".
                        ((end($occasions) != $occasion) ? ' OR ' : '');
            }
            // Add 'OR' before each element except the last one.

            $dql .= ' ) ';
        }

        if (!empty($type)) {
            if (!empty($brand) or !empty($category) or !empty($occasions)) {
                $dql .= ' AND ';
            }

            $dql .= ' pr.type IN (:type)';
        }

        if (!empty($colors)) {
            if (!empty($brand) or !empty($category)
                or !empty($occasions) or !empty($type)) {
                $dql .= ' AND ';
            }

            $dql .= ' ( ';

            foreach ($colors as $i => $color) {
                $dql = $dql." (ILIKE(pc.color, :color$i) = true) ".
                       ((end($colors) != $color) ? ' OR ' : '');
            }
            // Add 'OR' before each element except the last one.

            $dql = $dql.' ) ';
        }

        if (!empty($fabrics)) {
            if (!empty($brand) or !empty($category) or !empty($occasions)
                or !empty($type) or !empty($colors)) {
                $dql .= ' AND ';
            }

            $dql .= ' ( ';

            foreach ($fabrics as $i => $fabric) {
                $dql = $dql." (CONTAINS(pc.fabric, :fabric$i) = TRUE) ".
                        ((end($fabrics) != $fabric) ? ' OR ' : '');
            }
            // Add 'OR' before each element except the last one.

            $dql .= ' ) ';
        }

        if (!empty($textures)) {
            if (!empty($brand) or !empty($category) or !empty($occasions)
                or !empty($type) or !empty($colors) or !empty($fabrics)) {
                $dql .= ' AND ';
            }

            $dql .= ' ( ';

            foreach ($textures as $i => $texture) {
                $dql .= " (CONTAINS(tx.texture, :texture$i) = TRUE) ".
                        ((end($textures) != $texture) ? ' OR ' : '');
            }
            // Add 'OR' before each element except the last one.

            $dql .= ' ) ';
        }

        if (!empty($tags)) {
            if (!empty($brand) or !empty($category) or !empty($occasions)
                or !empty($type) or !empty($colors) or !empty($fabrics) or !empty($textures)) {
                $dql .= ' AND ';
            }

            $dql .= ' ( ';

            foreach ($tags as $i => $tag) {
                $dql .= " (CONTAINS(tg.tag, :tag$i) = TRUE) ".
                        ((end($tags) != $tag) ? ' OR ' : '');
            }
            // Add 'OR' before each element except the last one.

            $dql .= ' ) ';
        }

        if (!empty($keywords)) {
            if (!empty($brand) or !empty($category) or !empty($occasions)
                 or !empty($type) or !empty($colors) or !empty($fabrics)
                 or !empty($textures) or !empty($tags)) {
                $dql .= ' AND ';
            }
            /* If exact search is checked, look for the whole set of words.
            */
            if ($search->isExact()) {
                $dql = preg_replace('/__search_rank__/', '', $dql);

                /* If no sort argument is set, by default, display results from 'name'
                  column before results 'description' and 'features' columns.
                  Otherwise, apply the sort argument.
                */
                if ('pr.updated' === $sort) {
                    $name = $em->createQuery($dql);
                    $name->setDql($name->getDql().' (ILIKE(pr.name, :phrase) = true) ')
                         ->setParameter('phrase', '%'.$keywords.'%');

                    $desc = $em->createQuery($dql);
                    $desc->setDql($desc->getDql().' (ILIKE(pr.description, :phrase) = true) ')
                         ->setParameter('phrase', '%'.$keywords.'%');

                    $feat = $em->createQuery($dql);
                    $feat->setDql($feat->getDql().' (ILIKE(pr.features, :phrase) = true) ')
                      ->setParameter('phrase', '%'.$keywords.'%');

                    if (!empty($brand)) {
                        $name->setParameter('brand', $brand);
                        $desc->setParameter('brand', $brand);
                        $feat->setParameter('brand', $brand);
                    }

                    if (!empty($category)) {
                        $name->setParameter('category', $category);
                        $desc->setParameter('category', $category);
                        $feat->setParameter('category', $category);
                    }

                    if (!empty($occasions)) {
                        foreach ($occasions as $i => $occasion) {
                            $name->setParameter("occasion$i", '["'.$occasion.'"]');
                            $desc->setParameter("occasion$i", '["'.$occasion.'"]');
                            $feat->setParameter("occasion$i", '["'.$occasion.'"]');
                        }
                    }

                    if (!empty($type)) {
                        $name->setParameter('type', $type);
                        $desc->setParameter('type', $type);
                        $feat->setParameter('type', $type);
                    }

                    if (!empty($colors)) {
                        foreach ($colors as $i => $color) {
                            $name->setParameter("color$i", '%'.$color.'%');
                            $desc->setParameter("color$i", '%'.$color.'%');
                            $feat->setParameter("color$i", '%'.$color.'%');
                        }
                    }

                    if (!empty($fabrics)) {
                        foreach ($fabrics as $i => $fabric) {
                            $name->setParameter("fabric$i", '["'.$fabric.'"]');
                            $desc->setParameter("fabric$i", '["'.$fabric.'"]');
                            $feat->setParameter("fabric$i", '["'.$fabric.'"]');
                        }
                    }

                    if (!empty($textures)) {
                        foreach ($textures as $i => $texture) {
                            $name->setParameter("texture$i", '["'.$texture.'"]');
                            $desc->setParameter("texture$i", '["'.$texture.'"]');
                            $feat->setParameter("texture$i", '["'.$texture.'"]');
                        }
                    }

                    if (!empty($tags)) {
                        foreach ($tags as $i => $tag) {
                            $name->setParameter("tag$i", '["'.$tag.'"]');
                            $desc->setParameter("tag$i", '["'.$tag.'"]');
                            $feat->setParameter("tag$i", '["'.$tag.'"]');
                        }
                    }

                    $nameResult = $name->getResult();
                    $descResult = $desc->getResult();
                    $featResult = $feat->getResult();
                    $resultFull = array_merge($nameResult, $descResult, $featResult);
                }
                // $sort != 'pr.updated'
                else {
                    $query = $em->createQuery($dql);
                    $query->setDql($query->getDql().
                      " ( (ILIKE(pr.name, :phrase) = true) OR
                  (ILIKE(pr.description, :phrase) = true) OR
                  (ILIKE(pr.features, :phrase) = true)
                 )  ORDER BY $sort $order
              ")
                      ->setParameter('phrase', $keywords);

                    if (!empty($brand)) {
                        $query->setParameter('brand', $brand);
                    }

                    if (!empty($category)) {
                        $query->setParameter('category', $category);
                    }

                    if (!empty($occasions)) {
                        foreach ($occasions as $i => $occasion) {
                            $query->setParameter("occasion$i", '["'.$occasion.'"]');
                        }
                    }

                    if (!empty($type)) {
                        $query->setParameter('type', $type);
                    }

                    if (!empty($colors)) {
                        foreach ($colors as $i => $color) {
                            $query->setParameter("color$i", '%'.$color.'%');
                        }
                    }

                    if (!empty($fabrics)) {
                        foreach ($fabrics as $i => $fabric) {
                            $query->setParameter("fabric$i", '["'.$fabric.'"]');
                        }
                    }

                    if (!empty($textures)) {
                        foreach ($textures as $i => $texture) {
                            $query->setParameter("texture$i", '["'.$texture.'"]');
                        }
                    }

                    if (!empty($tags)) {
                        foreach ($tags as $i => $tag) {
                            $query->setParameter("tag$i", '["'.$tag.'"]');
                        }
                    }

                    $resultFull = $query->getResult();
                }
            }
            /* If exact search is not checked, then split the set of words into
               individual words and seach for each word separately.
            */ elseif (!$search->isExact()) {
                $dql = preg_replace('/__search_rank__/', ', TSRANK(pr.search_vector, :rank) AS rank ', $dql);

                /* If no sort argument is set, by default, display results from 'name'
                  column before results 'description' and 'features' columns.
                  Otherwise, apply the sort argument.
                */
                if ('pr.updated' === $sort) {
                    /* Convert search queries
                      FROM
                        The unique combination of reliable comfort
                      TO
                        The:A|unique:A|combination:A|of:A|reliable:A|comfort:A

                      The search_vector column was created as follows:
                        setweight(to_tsvector('english', coalesce(name, '')), 'A') ||
                        setweight(to_tsvector('english', coalesce(description, '')), 'B') ||
                        setweight(to_tsvector('english', coalesce(features, '')), 'C')
                    */

                    // Replace whitespace with '|'
                    $keywordsName = preg_replace('/\s+/', ':A|', $keywords);
                    // End last words with ':A'
                    $keywordsName = $keywordsName.':A';

                    $name = $em->createQuery($dql);
                    $name->setDql($name->getDql().
                        ' (TSMATCH(TO_TSVECTOR(pr.name), TO_TSQUERY(:phrase)) = TRUE)
                  ORDER BY rank DESC ')
                        ->setParameter('phrase', $keywordsPhrase)
                        ->setParameter('rank', $keywordsName);

                    // Replace whitespace with '|B'
                    $keywordsDesc = preg_replace('/\s+/', ':B|', $keywords);
                    // End last words with ':B'
                    $keywordsDesc = $keywordsDesc.':B';

                    $desc = $em->createQuery($dql);
                    $desc->setDql($desc->getDql().
                      ' (TSMATCH(TO_TSVECTOR(pr.description), TO_TSQUERY(:phrase)) = TRUE)
                ORDER BY rank DESC ')
                      ->setParameter('phrase', $keywordsPhrase)
                      ->setParameter('rank', $keywordsDesc);

                    // Replace whitespace with '|C'
                    $keywordsFeat = preg_replace('/\s+/', ':C|', $keywords);
                    // End last words with ':C'
                    $keywordsFeat = $keywordsFeat.':C';

                    $feat = $em->createQuery($dql);
                    $feat->setDql($feat->getDql().
                      ' (TSMATCH(TO_TSVECTOR(pr.features), TO_TSQUERY(:phrase)) = TRUE)
                ORDER BY rank DESC ')
                      ->setParameter('phrase', $keywordsPhrase)
                      ->setParameter('rank', $keywordsFeat);

                    if (!empty($brand)) {
                        $name->setParameter('brand', $brand);
                        $desc->setParameter('brand', $brand);
                        $feat->setParameter('brand', $brand);
                    }

                    if (!empty($category)) {
                        $name->setParameter('category', $category);
                        $desc->setParameter('category', $category);
                        $feat->setParameter('category', $category);
                    }

                    if (!empty($occasions)) {
                        foreach ($occasions as $i => $occasion) {
                            $name->setParameter("occasion$i", '["'.$occasion.'"]');
                            $desc->setParameter("occasion$i", '["'.$occasion.'"]');
                            $feat->setParameter("occasion$i", '["'.$occasion.'"]');
                        }
                    }

                    if (!empty($type)) {
                        $name->setParameter('type', $type);
                        $desc->setParameter('type', $type);
                        $feat->setParameter('type', $type);
                    }

                    if (!empty($colors)) {
                        foreach ($colors as $i => $color) {
                            $name->setParameter("color$i", '%'.$color.'%');
                            $desc->setParameter("color$i", '%'.$color.'%');
                            $feat->setParameter("color$i", '%'.$color.'%');
                        }
                    }

                    if (!empty($fabrics)) {
                        foreach ($fabrics as $i => $fabric) {
                            $name->setParameter("fabric$i", '["'.$fabric.'"]');
                            $desc->setParameter("fabric$i", '["'.$fabric.'"]');
                            $feat->setParameter("fabric$i", '["'.$fabric.'"]');
                        }
                    }

                    if (!empty($textures)) {
                        foreach ($textures as $i => $texture) {
                            $name->setParameter("texture$i", '["'.$texture.'"]');
                            $desc->setParameter("texture$i", '["'.$texture.'"]');
                            $feat->setParameter("texture$i", '["'.$texture.'"]');
                        }
                    }

                    if (!empty($tags)) {
                        foreach ($tags as $i => $tag) {
                            $name->setParameter("tag$i", '["'.$tag.'"]');
                            $desc->setParameter("tag$i", '["'.$tag.'"]');
                            $feat->setParameter("tag$i", '["'.$tag.'"]');
                        }
                    }

                    $nameResult = $name->getResult();
                    $descResult = $desc->getResult();
                    $featResult = $feat->getResult();
                    $resultFull = array_merge($nameResult, $descResult, $featResult);
                } else { // $sort === 'pr.updated'
                    $query = $em->createQuery($dql);
                    $query->setDql($query->getDql().
                      ' ( (TSMATCH(TO_TSVECTOR(pr.name), TO_TSQUERY(:phrase)) = TRUE) OR
                  (TSMATCH(TO_TSVECTOR(pr.description), TO_TSQUERY(:phrase)) = TRUE) OR
                  (TSMATCH(TO_TSVECTOR(pr.features), TO_TSQUERY(:phrase)) = TRUE)
                  ')
                      ->setParameter('phrase', $keywordsPhrase)
                      ->setParameter('rank', $keywordsPhrase);
                    $query->setDql($query->getDql()." )  ORDER BY $sort $order  ");

                    if (!empty($brand)) {
                        $query->setParameter('brand', $brand);
                    }

                    if (!empty($category)) {
                        $query->setParameter('category', $category);
                    }

                    if (!empty($occasions)) {
                        foreach ($occasions as $i => $occasion) {
                            $query->setParameter("occasion$i", '["'.$occasion.'"]');
                        }
                    }

                    if (!empty($type)) {
                        $query->setParameter('type', $type);
                    }

                    if (!empty($colors)) {
                        foreach ($colors as $i => $color) {
                            $query->setParameter("color$i", '%'.$color.'%');
                        }
                    }

                    if (!empty($fabrics)) {
                        foreach ($fabrics as $i => $fabric) {
                            $query->setParameter("fabric$i", '["'.$fabric.'"]');
                        }
                    }

                    if (!empty($textures)) {
                        foreach ($textures as $i => $texture) {
                            $query->setParameter("texture$i", '["'.$texture.'"]');
                        }
                    }

                    if (!empty($tags)) {
                        foreach ($tags as $i => $tag) {
                            $query->setParameter("tag$i", '["'.$tag.'"]');
                        }
                    }

                    $resultFull = $query->getResult();
                }
            }
        }
        // if (!empty($keywords))
        else {
            $dql = preg_replace('/__search_rank__/', '', $dql);
            $query = $em->createQuery($dql);

            if (!empty($brand)) {
                $query->setParameter('brand', $brand);
            }

            if (!empty($category)) {
                $query->setParameter('category', $category);
            }

            if (!empty($occasions)) {
                foreach ($occasions as $i => $occasion) {
                    $query->setParameter("occasion$i", '["'.$occasion.'"]');
                }
            }

            if (!empty($type)) {
                $query->setParameter('type', $type);
            }

            if (!empty($colors)) {
                foreach ($colors as $i => $color) {
                    $query->setParameter("color$i", '%'.$color.'%');
                }
            }

            if (!empty($fabrics)) {
                foreach ($fabrics as $i => $fabric) {
                    $query->setParameter("fabric$i", '["'.$fabric.'"]');
                }
            }

            if (!empty($textures)) {
                foreach ($textures as $i => $texture) {
                    $query->setParameter("texture$i", '["'.$texture.'"]');
                }
            }

            if (!empty($tags)) {
                foreach ($tags as $i => $tag) {
                    $query->setParameter("tag$i", '["'.$tag.'"]');
                }
            }

            $resultFull = $query->getResult();
        }

        if (empty($resultFull)) {
            return [];
        }

        foreach ($resultFull as $i => $product) {
            $resultFull[$i] = $this->fullName($product);
        }

        $results = [];
        foreach ($resultFull as $i => $product) {
            $colorKeys =
              ['colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                  'fabrics', 'fabrics_full', 'fabrics_full_set',
                  'textures', 'textures_full', 'textures_full_set',
                  'image', 'image2', 'image3', 'image4', 'image5',
                  'imageMedium', 'imageMedium2', 'imageMedium3',
                  'imageMedium4', 'imageMedium5',
                  'imageSmall', 'imageSmall2', 'imageSmall3',
                  'imageSmall4', 'imageSmall5'];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['id']][$product['color'].'-'.$product['colorId']][$key] = $product[$key];
                }
            }

            /* Retrieve the dimensions of the image stored at the end each image adddress
              ex.
              "image" => "uploads/adidas/women/Adilette-CF-Print/large/
                          1-66c59cadbbe1d-1280x960.webp"
              width = 1280
              height = 960
            */
            $imageKeys =
              ['image', 'image2', 'image3', 'image4', 'image5',
                  'imageMedium', 'imageMedium2', 'imageMedium3',
                  'imageMedium4', 'imageMedium5',
                  'imageSmall', 'imageSmall2', 'imageSmall3',
                  'imageSmall4', 'imageSmall5'];

            foreach ($productColors as $i => $productColor) {
                foreach ($productColor as $j => $pc) {
                    foreach ($pc as $key => $value) {
                        if (in_array($key, $imageKeys) and !empty($value)) {
                            $dimensions = explode('-', $value);
                            $last = $dimensions[array_key_last($dimensions)];
                            $set = explode('x', $last);
                            $width = $set[0];
                            $height = explode('.', $set[1])[0];
                            $productColors[$i][$j][$key.'Width'] = $width;
                            $productColors[$i][$j][$key.'Height'] = $height;
                        }
                    }
                }
            }

            /* Sort ProductColor in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['id']][$key] = $product[$key];
                }
            }

            $results[$product['id']]['colors'] = $productColors[$product['id']];
        }

        return $results;
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findSearchByName(Search $search): array
    {
        // Remove apostrophe
        $search->q = str_replace("'", '', $search->q);
        // Separate all words and store in an array
        $search->q = explode(' ', $search->q);
        $searchTerm = $search->q;

        foreach ($searchTerm as $i => $term) {
            if (!empty($searchTerm[$i])) {
                $DQL = "SELECT p FROM App\Entity\Product\Product\Product p WHERE ( LOWER(p.name) LIKE LOWER('%$searchTerm[$i]%'))";

                if ($search->brands()) {
                    $DQL .= ' AND p.brand = '."'".$search->brands()."'";
                }

                $em = $this->getEntityManager();
                $query[$i] = $em->createQuery($DQL);
                $query[$i] = $query[$i]->getResult();
            }
        }

        $finalQuery = array_merge(...$query);
        $finalQuery = array_values(array_unique($finalQuery));

        if (!empty($finalQuery)) {
            return $finalQuery;
        } elseif (empty($finalQuery)) {
            return [];
        }
    }

    /**
     * @return Product[] Return a stripped version (array) of the Product
     */
    public function all($query)
    {
        $em = $this->getEntityManager();
        $order = $query->has('order') ? $query->get('order') : 'DESC';
        $sort = $query->has('sort') ? $query->get('sort') : 'pr.updated';
        $brand = $query->has('brand') ? $query->get('brand') : '';
        $category = $query->has('category') ? $query->get('category') : '';
        $occasion = $query->has('occasion') ? $query->get('occasion') : '';
        $type = $query->has('type') ? $query->get('type') : '';

        $condition = ' ';

        if (!empty($brand)) {
            $condition .= ' pr.brand = :brand ';
        }

        if (!empty($category)) {
            $condition .= ' pr.category = :category ';
        }

        if (!empty($occasion)) {
            $condition .= ' (ILIKE(JSON_TEXT(pr.occasion), :occasion) = true)  ';
        }

        if (!empty($type)) {
            $condition .= ' pr.type = :type ';
        }

        $dql =
        'SELECT
           pr.id, pr.name, pr.brand,
           pr.description, pr.features,
           pr.category,pr.occasion, pr.type,
           pr.displayed, pr.displayDate, q.qtyPack, v.videoUrl,
           pr.created, pr.updated as updated,
           pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
           pc.image, pc.imageMedium, pc.imageSmall,
           img2.image AS image2, img2.imageMedium AS imageMedium2,
           img2.imageSmall AS imageSmall2,
           img3.image AS image3, img3.imageMedium AS imageMedium3,
           img3.imageSmall AS imageSmall3,
           img4.image AS image4, img4.imageMedium AS imageMedium4,
           img4.imageSmall AS imageSmall4,
           img5.image AS image5, img5.imageMedium AS imageMedium5,
           img5.imageSmall AS imageSmall5

         FROM App\Entity\Product\Product\Product pr
         LEFT JOIN pr.productColor pc
         LEFT JOIN pc.texture tx

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN pr.qtyPack q
         LEFT JOIN pr.video v
         ';

        if (!empty($brand) or !empty($category)
           or !empty($occasion) or !empty($type)) {
            $dql .= " WHERE $condition ";
        }

        $dql .= " ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        if (!empty($brand)) {
            $query->setParameter('brand', $brand);
        }

        if (!empty($category)) {
            $query->setParameter('category', $category);
        }

        if (!empty($occasion)) {
            $query->setParameter('occasion', '%'.strtolower($occasion).'%');
        }

        if (!empty($type)) {
            $query->setParameter('type', $type);
        }

        $initialResult = $query->getResult();

        foreach ($initialResult as $i => $product) {
            $initialResult[$i] = $this->fullName($product);
        }

        if (empty($initialResult)) {
            return [];
        }

        $results = [];
        $color = [];

        $colorKeys =
          ['colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
              'fabrics', 'fabrics_full', 'fabrics_full_set',
              'textures', 'textures_full', 'textures_full_set',
              'image', 'image2', 'image3', 'image4', 'image5',
              'imageMedium', 'imageMedium2', 'imageMedium3',
              'imageMedium4', 'imageMedium5',
              'imageSmall', 'imageSmall2', 'imageSmall3',
              'imageSmall4', 'imageSmall5'];

        foreach ($initialResult as $i => $product) {
            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['id']][$key] = $product[$key];
                }
            }

            if (!empty($product['colorId'])) {
                foreach ($product as $key => $value) {
                    if (in_array($key, $colorKeys)) {
                        $productColors[$product['id']][$product['color'].'-'.$product['colorId']][$key] = $product[$key];
                    }
                }

                /* Sort PC in alphabetical order */
                foreach ($productColors as $j => $productColor) {
                    ksort($productColor);
                    $productColors[$j] = $productColor;
                }

                $results[$product['id']]['colors'] =
                $productColors[$product['id']];
            }
        }

        /* Retrieve the dimensions of the image stored at the end each image adddress
          ex.
          "image" => "uploads/adidas/women/Adilette-CF-Print/large/
                      1-66c59cadbbe1d-1280x960.webp"
          width = 1280
          height = 960
        */
        $imageKeys =
          ['image', 'image2', 'image3', 'image4', 'image5',
              'imageMedium', 'imageMedium2', 'imageMedium3',
              'imageMedium4', 'imageMedium5',
              'imageSmall', 'imageSmall2', 'imageSmall3',
              'imageSmall4', 'imageSmall5'];

        foreach ($productColors as $i => $productColor) {
            foreach ($productColor as $j => $pc) {
                foreach ($pc as $key => $value) {
                    if (in_array($key, $imageKeys) and !empty($value)) {
                        $dimensions = explode('-', $value);
                        $last = $dimensions[array_key_last($dimensions)];
                        $set = explode('x', $last);
                        $width = $set[0];
                        $height = explode('.', $set[1])[0];
                        $productColors[$i][$j][$key.'Width'] = $width;
                        $productColors[$i][$j][$key.'Height'] = $height;
                    }
                }
            }
        }

        foreach ($initialResult as $i => $product) {
            $results[$product['id']]['colors'] =
              $productColors[$product['id']];
        }

        $results = array_values($results);

        return $results;
    }

    /**
     * @return Product[] Return a stripped version (array) of the Product
     */
    public function fetch(int $id)
    {
        $em = $this->getEntityManager();
        $otherProductId = [];
        $productColorId = [];
        $dql =
        'SELECT
           pr.id, pr.name, pr.brand,
           pr.description, pr.features,
           pr.category, pr.occasion, pr.type,
           pr.displayed, pr.displayDate, q.qtyPack, v.videoUrl,
           d.discount, d.startDate, d.endDate, ds.discontinued,
           ds.dateDiscontinued, prc.dateDowngradePrice,
           prc.refundable, prc.exchangeable,
           pr.created, pr.updated,
           pc.id AS pcId, pc.color, pc.fabric, tx.texture,
           vp.videoUrl AS pcVideo,
           tg.tag,

           s.id AS sId, s.sort, s.tag AS similarTags,
           sm.id as smId, sm.sort as colorSort,

           oex.id AS oexId, oex.colors AS oexColor,
           oexp.id  AS oexpId,
           expc.id AS expcId, expc.color AS expcColor,
           expc.fabric AS expcFabric, extx.texture AS expcTexture,

           pc.image, pc.imageMedium, pc.imageSmall,
           img2.image AS image2, img2.imageMedium AS imageMedium2,
           img2.imageSmall AS imageSmall2,
           img3.image AS image3, img3.imageMedium AS imageMedium3,
           img3.imageSmall AS imageSmall3,
           img4.image AS image4, img4.imageMedium AS imageMedium4,
           img4.imageSmall AS imageSmall4,
           img5.image AS image5, img5.imageMedium AS imageMedium5,
           img5.imageSmall AS imageSmall5,

           expc.imageMedium AS excludeImageMedium,
           expr.name AS excludeImageMediumName,
           expr.brand AS excludeImageMediumBrand

         FROM App\Entity\Product\Product\Product pr

         LEFT JOIN pr.similarProduct s

         LEFT JOIN pr.productColor pc
         LEFT JOIN pc.texture tx
         LEFT JOIN pc.tag tg

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5
         LEFT JOIN pc.video vp

         LEFT JOIN pc.similarProductColor sm
         LEFT JOIN sm.excludeColor oex
         LEFT JOIN sm.excludeProductColors oexp
         LEFT JOIN oexp.color expc
         LEFT JOIN expc.texture extx
         LEFT JOIN expc.product expr

         LEFT JOIN pr.qtyPack q
         LEFT JOIN pr.video v
         LEFT JOIN pr.discontinued ds
         LEFT JOIN pr.pricing prc
         LEFT JOIN pr.discount d

         WHERE pr.id = :id
         ORDER BY pc.color ASC
         ';
        $query = $em->createQuery($dql);
        $query->setParameter('id', $id);
        $initialResult = $query->getResult();

        foreach ($initialResult as $i => $product) {
            $initialResult[$i] = $this->fullName($product);
        }

        $colorKeys =
          ['pcId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
              'fabrics', 'fabrics_full', 'fabrics_full_set',
              'textures', 'textures_full', 'textures_full_set',
              'tags', 'pcVideo',
              'image', 'image2', 'image3', 'image4', 'image5',
              'imageMedium', 'imageMedium2', 'imageMedium3',
              'imageMedium4', 'imageMedium5',
              'imageSmall', 'imageSmall2', 'imageSmall3',
              'imageSmall4', 'imageSmall5',

              'sId', 'smId', 'colorSort', 'similarTags',

              'oexId', 'oexColor',
              'ocId', 'otherColor', 'qtyColor',
              'oexpId', 'expcId',  'expcColor',
              'expcFabric', 'expcTexture',

              'excludeImageMedium', 'excludeImageMediumName',
              'excludeImageMediumBrand'];

        $otherColorKeys =
          ['ocId', 'otherColor', 'qtyColor'];

        $similarSortKeys =
          ['smId', 'colorSort'];

        $productColorKeys =
          ['pcId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
              'fabrics', 'fabrics_full', 'fabrics_full_set',
              'textures', 'textures_full', 'textures_full_set',
              'tags', 'pcVideo',
              'image', 'image2', 'image3', 'image4', 'image5',
              'imageMedium', 'imageMedium2', 'imageMedium3',
              'imageMedium4', 'imageMedium5',
              'imageSmall', 'imageSmall2', 'imageSmall3',
              'imageSmall4', 'imageSmall5', ];

        $excludePCKeys =
          [
              'smId' => 'smId',
              'pcId' => 'expcId',
              'color' => 'expcColor',
              'fabrics' => 'expcFabric',
              'textures' => 'expcTexture',
              'tags' => 'expcTags',
              'imageMedium' => 'excludeImageMedium',
              'name' => 'excludeImageMediumName',
              'brand' => 'excludeImageMediumBrand'];

        foreach ($initialResult as $i => $product) {
            if (!empty($product['sId'])) {
                if (!empty($product['sort'])) {
                    foreach ($product['sort'] as $j => $srt) {
                        if ('integer' === gettype($srt)) {
                            $otherProductId[] = $srt;
                        }
                    }
                }
            }
            if (!empty($product['pcId'])) {
                foreach ($product as $key => $value) {
                    if (in_array($key, $productColorKeys)) {
                        $productColors[$product['id']][$product['pcId']][$key] = $product[$key];
                    }
                }

                if (!empty($product['colorSort'])) {
                    foreach ($product['colorSort'] as $j => $sort) {
                        if ('integer' === gettype($sort)) {
                            $productColorId[] = $sort;
                        }
                    }

                    foreach ($product as $key => $value) {
                        if (in_array($key, $similarSortKeys)) {
                            $similarSort[$product['id']][$product['pcId']][$key] = $product[$key];
                        }
                    }
                }

                $result[$product['id']]['colors'][$product['pcId']] =
                $productColors[$product['id']][$product['pcId']];

                if (!empty($product['colorSort'])) {
                    $result[$product['id']]['colors'][$product['pcId']]['similarColors']['sort'] =
                    $similarSort[$product['id']][$product['pcId']];
                }

                if (!empty($product['expcId'])) {
                    foreach ($product as $key => $value) {
                        if (in_array($key, $excludePCKeys)) {
                            $similarExcludePC[$product['id']][$product['pcId']][$product['expcId']][array_search($key, $excludePCKeys)]
                                             = $product[$key];
                        }
                    }

                    $similarExcludePC[$product['id']][$product['pcId']][$product['expcId']] =
                      $this->fullName($similarExcludePC[$product['id']][$product['pcId']][$product['expcId']]);

                    $result[$product['id']]['colors'][$product['pcId']]['similarColors']['excludeProductColors'] =
                    $similarExcludePC[$product['id']][$product['pcId']];
                }

                if (!empty($product['oexId'])) {
                    $result[$product['id']]['colors'][$product['pcId']]['similarColors']['excludeColors'] = $product['oexColor'];
                }
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $result[$product['id']][$key] = $product[$key];
                }
            }
        }

        $result = array_values($result)[0];

        $attributes = $this->getAttributes();

        if (!empty($otherProductId)) {
            $pc = [];
            $dql =
            'SELECT
             pr.id, pr.name, pr.brand,
             pr.category, pr.occasion, pr.type,
             pc.id AS pcId, pc.color, pc.fabric,
             tx.texture, tg.tag,
             pc.imageSmall, pc.imageMedium

           FROM App\Entity\Product\ProductColor\ProductColor pc
           INNER JOIN pc.product pr

           LEFT JOIN pr.productColor pc
           LEFT JOIN pc.texture tx
           LEFT JOIN pc.tag tg

           WHERE pc.id IN (:id)
           ORDER BY pr.brand, pr.name
           ';

            $q = $em->createQuery($dql);
            $q->setParameter('id', array_unique($otherProductId));
            $initialOther = $q->getResult();

            if (!empty($initialOther)) {
                $productColors = [];
                $productColorKeys =
                  ['pcId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                      'fabrics', 'fabrics_full', 'fabrics_full_set',
                      'textures', 'textures_full', 'textures_full_set', 'tags',
                      'imageSmall', 'imageMedium'];

                foreach ($initialOther as $i => $otherProduct) {
                    $initialOther[$i] = $this->fullName($otherProduct);
                }

                foreach ($initialOther as $i => $otherProduct) {
                    if (!empty($otherProduct['pcId'])) {
                        foreach ($otherProduct as $key => $value) {
                            if (in_array($key, $productColorKeys)) {
                                $productColors[$otherProduct['id']][$otherProduct['pcId']][$key] = $otherProduct[$key];
                            }
                        }

                        foreach ($otherProduct as $key => $value) {
                            if (!in_array($key, $productColorKeys)) {
                                $otherProducts[$otherProduct['id']][$key] = $otherProduct[$key];
                            }
                        }

                        $otherProducts[$otherProduct['id']]['colors'] =
                          $productColors[$otherProduct['id']];

                        $result['otherProducts']['products'] = $otherProducts;

                        foreach ($productColors as $i => $productColor) {
                            foreach ($productColor as $j => $color) {
                                $thumbnails[$j] = $color;
                            }
                        }

                        $result['otherProducts']['thumbnails'] = $thumbnails;
                    }
                }
            }
        }

        if (!empty($productColorId)) {
            $pc = [];
            $productColor = [];
            $dql =
            'SELECT
             pr.id, pr.name, pr.brand,
             pr.category, pr.occasion, pr.type,
             pc.id AS pcId, pc.color, pc.fabric,
             tx.texture, tg.tag,
             pc.imageSmall, pc.imageMedium

           FROM App\Entity\Product\ProductColor\ProductColor pc
           LEFT JOIN pc.texture tx
           INNER JOIN pc.product pr
           LEFT JOIN pc.tag tg

           WHERE pc.id IN (:id)
           ';

            $q = $em->createQuery($dql);
            $q->setParameter('id', array_unique($productColorId));
            $initialColor = $q->getResult();

            if (!empty($initialColor)) {
                foreach ($initialColor as $i => $color) {
                    $pc[$color['pcId']] = $this->fullName($color);
                }
            }

            if (!empty($result['colors'])) {
                foreach ($result['colors'] as $j => $productColor) {
                    if (!empty($productColor['similarColors']['sort'])) {
                        $otherColors = [];
                        $otherProductColors = [];
                        $otherColorsArray = [];

                        foreach ($productColor['similarColors']['sort']['colorSort'] as $k => $sort) {
                            if ('string' === gettype($sort)) {
                                if (!empty($attributes[$sort])) {
                                    $sortArray[$j][$k]['type'] = 'color';
                                    $sortArray[$j][$k]['sort']['color'] = $sort;
                                    $sortArray[$j][$k]['sort']['color_full'] = $attributes[$sort];
                                    $otherColors[] = $sort;
                                }
                            } elseif ('integer' === gettype($sort)) {
                                if (!empty($pc)) {
                                    $sortArray[$j][$k]['type'] = 'product';
                                    $sortArray[$j][$k]['sort'] = $pc[$sort];
                                    $otherProductColors[] = $pc[$sort];
                                }
                            }
                        }

                        if (!empty($otherColors)) {
                            $otherColorSet = array_count_values($otherColors);
                            foreach ($otherColorSet as $color => $qtyColor) {
                                $otherColorsArray[$color]['color'] = $color;
                                $otherColorsArray[$color]['qtyColor'] = $qtyColor;
                            }

                            $result['colors'][$j]['similarColors']['otherColors']
                              = $otherColorsArray;
                        }

                        if (!empty($otherProductColors)) {
                            $result['colors'][$j]['similarColors']['otherProductColors'] =
                              $otherProductColors;
                        }
                    }
                }

                foreach ($result['colors'] as $j => $productColor) {
                    if (!empty($sortArray[$j])) {
                        $result['colors'][$j]['similarColors']['sort']
                        = $sortArray[$j];
                    }
                }
            }
        } else {
            if (!empty($similarSort)) {
                foreach ($result as $i => $product) {
                    if (!empty($product['colors'])) {
                        foreach ($product['colors'] as $j => $productColor) {
                            if (!empty($productColor['similarColors']['sort'])) {
                                $otherColors = [];
                                foreach ($productColor['similarColors']['sort']['colorSort'] as $k => $sort) {
                                    if ('string' === gettype($sort)) {
                                        if (!empty($attributes[$sort])) {
                                            $sortArray[$i][$j][$k]['type'] = 'color';
                                            $sortArray[$i][$j][$k]['sort']['color'] = $sort;
                                            $sortArray[$i][$j][$k]['sort']['color_full'] = $attributes[$sort];
                                            $otherColors[] = $sort;
                                        }
                                    }
                                }

                                if (!empty($otherColors)) {
                                    $otherColorSet = array_count_values($otherColors);
                                    foreach ($otherColorSet as $color => $qtyColor) {
                                        $otherColorsArray[$color]['color'] = $color;
                                        $otherColorsArray[$color]['qtyColor'] = $qtyColor;
                                    }

                                    $result[$i]['colors'][$j]['similarColors']['otherColors']
                                      = $otherColorsArray;
                                }
                            }
                        }
                        foreach ($product['colors'] as $j => $productColor) {
                            if (!empty($sortArray[$i][$j])) {
                                $result[$i]['colors'][$j]['similarColors']['sort']
                                = $sortArray[$i][$j];
                            }
                        }
                    }
                }
            }
        }

        // Iniitializing sort values
        $sort =
        ['brand' => 0, 'occasion' => 0, 'type' => 0, 'color' => 0,
            'fabric' => 0, 'texture' => 0, 'size' => 0, 'brandType' => 0,
            'brandColor' => 0, 'brandColorType' => 0, 'brandOccasion' => 0,
            'brandOccasionType' => 0, 'brandOccasionColor' => 0, 'colorType' => 0,
            'colorOccasion' => 0, 'colorFabric' => 0, 'colorTexture' => 0,
            'fabricType' => 0, 'fabricTexture' => 0, 'tags' => 0,
            'description' => 0, 'features' => 0,
            'sliderCount' => 0,
        ];

        // Retrieve count of each 'sort' values
        if (!empty($result['sort'])) {
            $s = array_count_values($result['sort']);
            $qtyBrand = (!empty($s['brand'])) ? $s['brand'] : 0;
            $qtyOccasion = (!empty($s['occasion'])) ? $s['occasion'] : 0;
            $qtyType = (!empty($s['type'])) ? $s['type'] : 0;
            $qtyColor = (!empty($s['color'])) ? $s['color'] : 0;
            $qtyFabric = (!empty($s['fabric'])) ? $s['fabric'] : 0;
            $qtyTexture = (!empty($s['texture'])) ? $s['texture'] : 0;
            $qtySize = (!empty($s['size'])) ? $s['size'] : 0;

            $qtyBrandType =
            (!empty($s['brand-type'])) ? $s['brand-type'] : 0;
            $qtyBrandColor =
            (!empty($s['brand-color'])) ? $s['brand-color'] : 0;
            $qtyBrandColorType =
            (!empty($s['brand-color-type'])) ? $s['brand-color-type'] : 0;
            $qtyBrandOccasion =
            (!empty($s['brand-occasion'])) ? $s['brand-occasion'] : 0;
            $qtyBrandOccasionType =
            (!empty($s['brand-occasion-type'])) ? $s['brand-occasion-type'] : 0;
            $qtyBrandOccasionColor =
            (!empty($s['brand-occasion-color'])) ? $s['brand-occasion-color'] : 0;

            $qtyColorType = (!empty($s['color-type'])) ? $s['color-type'] : 0;
            $qtyColorOccasion =
            (!empty($s['color-occasion'])) ? $s['color-occasion'] : 0;
            $qtyColorFabric =
            (!empty($s['color-fabric'])) ? $s['color-fabric'] : 0;
            $qtyColorTexture =
            (!empty($s['color-texture'])) ? $s['color-texture'] : 0;
            $qtyFabricType =
            (!empty($s['fabric-type'])) ? $s['fabric-type'] : 0;
            $qtyFabricTexture =
            (!empty($s['fabric-texture'])) ? $s['fabric-texture'] : 0;
            $qtyTags = (!empty($s['tags'])) ? $s['tags'] : 0;
            $qtyDescription = (!empty($s['description'])) ? $s['description'] : 0;
            $qtyFeatures = (!empty($s['features'])) ? $s['features'] : 0;

            $sort =
            ['brand' => $qtyBrand, 'occasion' => $qtyOccasion,
                'type' => $qtyType, 'color' => $qtyColor,
                'fabric' => $qtyFabric, 'texture' => $qtyTexture,
                'size' => $qtySize,
                'brandType' => $qtyBrandType,
                'brandColor' => $qtyBrandColor,
                'brandColorType' => $qtyBrandColorType,
                'brandOccasion' => $qtyBrandOccasion,
                'brandOccasionType' => $qtyBrandOccasionType,
                'brandOccasionColor' => $qtyBrandOccasionColor,
                'colorType' => $qtyColorType,
                'colorOccasion' => $qtyColorOccasion,
                'colorFabric' => $qtyColorFabric,
                'colorTexture' => $qtyColorTexture,
                'fabricType' => $qtyFabricType,
                'fabricTexture' => $qtyFabricTexture,
                'tags' => $qtyTags,
                'description' => $qtyDescription,
                'features' => $qtyFeatures,
                'sliderCount' => count($result['sort']),
            ];
        }

        return ['sort' => $sort, 'product' => $result];
    }

    /**
     * @return Product[] return a stripped version (array) of the Product
     *
     *  Create a small array with mainly the Product's full name and thumbnail
     */
    public function similar(int $id)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
           pr.id, pr.name, pr.brand,
           pr.category, pr.occasion, pr.type,
           pc.id AS pcId, pc.color, pc.fabric, tx.texture,
           pc.imageMedium

         FROM App\Entity\Product\Product\Product pr
         LEFT JOIN pr.productColor pc
         LEFT JOIN pc.texture tx

         WHERE pr.id = :id
         ORDER BY pc.color ASC
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $id);
        $initialResult = $query->getResult();

        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $pr) {
                $initialResult[$i] = $this->fullName($pr);
            }
        }

        // Create multi-dimensionsal associative array to mimic Object structure.
        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $product) {
                $productColorKeys = [
                    'pcId', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                    'fabrics', 'fabrics_full', 'fabrics_full_set',
                    'textures', 'textures_full', 'textures_full_set',
                    'imageMedium'];

                foreach ($product as $key => $value) {
                    if (in_array($key, $productColorKeys)) {
                        $productColors[$product['id']][$product['pcId']][$key] = $product[$key];
                    }
                }

                $result[$product['id']]['colors'][$product['pcId']] =
                $productColors[$product['id']][$product['pcId']];

                foreach ($product as $key => $value) {
                    if (!in_array($key, $productColorKeys)) {
                        $result[$product['id']][$key] = $product[$key];
                    }
                }
            }

            return array_values($result)[0];
        }

        return [];
    }

    /**
     * @return Product[] Returns an array of Product objects
     *
     * Search product based on :
     *  - brand
     *  - category
     *  - occasion
     *  - type
     */
    public function findByFilter(array $filter)
    {
        $query = $this->createQueryBuilder('p');

        $query->andWhere('p.displayed = :display')
              ->setParameter('display', 'true');

        if (!empty($filter['orderPrice'])) {
            $orderPrice = $filter['orderPrice'];

            if ('nameAsc' === $orderPrice) {
                $query->orderBy('p.name', 'ASC');
            } elseif ('nameDsc' === $orderPrice) {
                $query->orderBy('p.name', 'DESC');
            }
        }

        if (!empty($filter['brand'])) {
            $brand = $filter['brand'];
            $query->andWhere('p.brand IN (:brand)')
                  ->setParameter('brand', $brand);
        }

        if (!empty($filter['category'])) {
            $categories = $filter['category'];
            $query->andWhere('p.category IN (:category)')
                  ->setParameter('category', $categories);
        }

        if (!empty($filter['occasion'])) {
            $occasions = $filter['occasion'];
            $query->andWhere('p.occasion IN (:occasion)')
                  ->setParameter('occasion', $occasions);
        }

        if (!empty($filter['type'])) {
            $types = $filter['type'];
            $query->andWhere('p.type IN (:type)')
                  ->setParameter('type', $types);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @return Product[] Return a stripped version (array) of the Product
     */
    public function full(int $id)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT
           pr.id  AS productId, pr.name, pr.description, pr.features,
           pr.brand, pr.category, pr.occasion, pr.type,
           pc.id AS pcId, pc.color AS pcColor, pc.fabric AS pcFabric,
           pc.imageMedium,
           img2.imageMedium AS imageMedium2,
           img3.imageMedium AS imageMedium3,
           img4.imageMedium AS imageMedium4,
           img5.imageMedium AS imageMedium5,
           pr.created

         FROM App\Entity\Product\Product\Product pr

         LEFT JOIN pr.productColor pc

         LEFT JOIN pc.productImage2 img2
         LEFT JOIN pc.productImage3 img3
         LEFT JOIN pc.productImage4 img4
         LEFT JOIN pc.productImage5 img5

         WHERE pr.id = :id
         ';
        $query = $em->createQuery($dql);

        $query->setParameter('id', $id);
        $initialResult = $query->getResult();

        // Create multi-dimensionsal associative array to mimic Object structure
        foreach ($initialResult as $i => $product) {
            $colorKeys = ['pcId', 'pcColor', 'pcFabric', 'texture',
                'imageMedium', 'imageMedium2', 'imageMedium3',
                'imageMedium4', 'imageMedium5'];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['productId']][$product['pcId']][$key] = $product[$key];
                }
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $result[$product['productId']][$key] = $product[$key];
                }
            }

            $result[$product['productId']]['colors'] = $productColors[$product['productId']];
        }

        $result = $this->fullName(array_values($result)[0]);

        return $result;
    }

    /**
     * @return Product[] Returns an array of Product objects
     *
     * Search product based on the color of each Product's 'sort' attribute.
     * 'sort' column stores:
     * - product ID, color, brand, type, brand-type & others.
     */
    public function similarSort(int $color)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT pr
         FROM App\Entity\Product\Product\Product pr
         LEFT JOIN pr.similarProduct s
         WHERE JSON_TEXT(s.sort) LIKE :color
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('color', '%'.$color.'%');
        $result = $query->getResult();

        return $result;
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
