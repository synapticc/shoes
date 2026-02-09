<?php

// src/Repository/Review/ReviewRepository.php

namespace App\Repository\Review;

use App\Controller\_Utils\Attributes;
use App\Controller\_Utils\Paginator;
use App\Entity\NoMap\Search\Search;
use App\Entity\Product\Product\Product;
use App\Entity\Review\Review;
use App\Entity\User\User;
use App\Repository\Review\ReviewHelpfulRepository as HelpfulRepo;
use App\Repository\User\Settings\MaxItemsRepository as MaxItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType as ArrayType;
use Doctrine\DBAL\ParameterType as Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping as Map;
use Doctrine\Persistence\ManagerRegistry;
// use App\Entity\NoMap\Walker\SortableNullsWalker as Sortable;
// use Doctrine\ORM\Query;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    use Attributes;

    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $_em, private MaxItems $max, Encryptor $encryptor, HelpfulRepo $helpfulRepo)
    {
        parent::__construct($registry, Review::class);
        $this->encryptor = $encryptor;
        $this->helpfulRepo = $helpfulRepo;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Review $entity, bool $flush = true): void
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
    public function remove(Review $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ReviewData
     */
    public function userReviews(User $user)
    {
        $user_id = $user->getId();
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT
         r.id,
         r.headline, r.comment,
         l.like, r.rating,
         r.fit, r.width, r.comfort,
         rc.recommend,
         d.delivery,
         pic.image, pic.created as pic_created,
         r.created, r.updated,
         i.quantity, p.sellingPrice, p.size,  p.id AS productDataId,
         pr.id AS productId, pr.name, pr.brand,
         pr.category, pr.occasion, pr.type,
         pc.id AS colorId, pc.color, pc.fabric, tx.texture,
         pc.imageMedium

       FROM App\Entity\Review\Review r

       LEFT JOIN r.users u
       LEFT JOIN u.orders o
       LEFT JOIN o.items i
       LEFT JOIN i.product p
       LEFT JOIN p.color pc
       LEFT JOIN pc.texture tx
       LEFT JOIN p.product pro
       LEFT JOIN r.product pr

       LEFT JOIN r.reviewLike l
       LEFT JOIN r.reviewRecommend rc
       LEFT JOIN r.reviewDelivery d
       LEFT JOIN r.reviewImage pic

       WHERE u.id = :id
       AND r.id IS NOT NULL
       AND pro.id = pr.id
       ORDER BY pr.id ASC
       ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('id', $user_id);
        $initialReviews = $query->getResult();

        if (!empty($initialReviews)) {
            foreach ($initialReviews as $i => $product) {
                $initialReviews[$i] = $this->fullName($product);
            }
        }

        $results = [];
        foreach ($initialReviews as $i => $product) {
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

    public function storeReviews($q, $search, ?User $user)
    {
        $em = $this->getEntityManager();
        $c = $em->getConnection();
        $add = 'addScalarResult';
        $table = 'addEntityResult';
        $set = 'setParameter';
        $create = 'createNativeQuery';
        $result = 'getScalarResult';
        $sort = $q->has('order')
                 ? explode('_', $q->get('order'))[0]
                 : 'updated';
        $direction = $q->has('order')
                 ? explode('_', $q->get('order'))[1]
                 : 'ASC';
        $order = $q->has('order')
                 ? str_replace('_', ' ', $q->get('order'))
                 : 'updated DESC';
        if ('customer' === $sort) {
            $order = '"firstName" '.$direction.', "lastName" '.$direction;
        }
        if ('ric' === $sort) {
            $sort = '"review_image_count"';
            $order = " $sort ".$direction;
        }

        $rating = $q->has('rating') ? $q->all('rating') : '';
        $customer = $q->has('customer') ? $this->encryptor->decrypt($q->get('customer')) : '';
        $brands = $q->has('brand') ? $q->all('brand') : '';
        $types = $q->has('type') ? $q->all('type') : '';
        $occasions = $q->has('occasion') ? $q->all('occasion') : '';
        $categories = $q->has('category') ? $q->all('category') : '';
        $colors = $q->has('color') ? implode('|', $q->all('color')) : '';
        $colorExclude = $q->has('color_exclude') ? implode('|', $q->all('color_exclude')) : '';
        $fabrics = $q->has('fabrics') ? $q->all('fabrics') : '';
        $sizes = $q->has('size') ? $q->all('size') : '';
        $priceRange = $q->has('price_range') ? $q->all('price_range') : '';
        $product = $q->has('product') ? $this->encryptor->decrypt($q->get('product')) : '';
        $fit = $q->has('fit') ? $q->all('fit') : '';
        $width = $q->has('width') ? $q->all('width') : '';
        $comfort = $q->has('comfort') ? $q->all('comfort') : '';
        $thumbnail = $q->has('thumbnail') ? $q->get('thumbnail') : '';
        $uploaded = $q->has('uploaded') ? $q->all('uploaded') : '';
        $comments = $q->has('comment') ? $q->all('comment') : '';
        $like = $q->has('like') ? $q->all('like') : '';
        $delivery = $q->has('delivery') ? $q->all('delivery') : '';
        $recommend = $q->has('recommend') ? $q->all('recommend') : '';

        if (!empty($sizes)) {
            foreach ($sizes as $k => $size) {
                if (array_key_exists($size, $this->adultKidSizeSet(false))) {
                    $sizes[$k] = $this->adultKidSizeSet()[$size];
                }
            }
        }

        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Replace whitespace with '|'
            $keywords = preg_replace('/\s+/', '|', $keywords);
        }

        $condition = '';

        if (!empty($keywords)) {
            $condition .= ' AND (
            r.headline ~* :keywords  OR
            r.comment ~* :keywords  OR
            pr.name ~* :keywords  OR
            pr.brand~* :keywords OR
            u.email~* :keywords OR
            u.first_name ~* :keywords OR
            u.last_name ~* :keywords
            )';
        }

        if (!empty($brands)) {
            $condition .= ' AND pr.brand IN (:brands) ';
        }
        if (!empty($rating)) {
            $condition .= ' AND r.rating IN (:rating) ';
        }
        if (!empty($customer)) {
            $condition .= ' AND u.user_id = :customer ';
        }
        if (!empty($product)) {
            $condition .= ' AND pr.pro_id = :product ';
        }
        if (!empty($types)) {
            $condition .= ' AND pr.type IN (:types) ';
        }
        if (!empty($occasions)) {
            $condition .= ' AND ( ';
            foreach ($occasions as $i => $occasion) {
                if (end($occasions) == $occasion) {
                    $condition .= " pr.occasion @> :occasion$i ";
                } else {
                    $condition .= " pr.occasion @> :occasion$i OR ";
                }
            }
            $condition .= ' ) ';
        }
        if (!empty($categories)) {
            $condition .= ' AND pr.category IN (:categories) ';
        }
        if (!empty($colors)) {
            $condition .= ' AND pc.color ~* :colors ';
        }
        if (!empty($colorExclude)) {
            $condition .= ' AND pc.color !~* :colorExclude ';
        }
        if (!empty($fabrics)) {
            $condition .= ' AND ( ';
            foreach ($fabrics as $i => $fabric) {
                if (end($fabrics) == $fabric) {
                    $condition .= " pc.fabric @> :fabric$i ";
                } else {
                    $condition .= " pc.fabric @> :fabric$i OR ";
                }
            }
            $condition .= ' ) ';
        }
        if (!empty($sizes)) {
            $condition .= ' AND p.size IN (:sizes) ';
        }
        if (!empty($fit)) {
            $condition .= ' AND r.fit IN (:fit) ';
        }
        if (!empty($width)) {
            $condition .= ' AND r.width IN (:width) ';
        }
        if (!empty($comfort)) {
            $condition .= ' AND r.comfort IN (:comfort) ';
        }
        if (!empty($fit)) {
            $condition .= ' AND r.fit IN (:fit) ';
        }
        if (!empty($like)) {
            $condition .= ' AND l.vote_like IN (:like) ';
        }
        if (!empty($delivery)) {
            $condition .= ' AND d.delivery IN (:delivery) ';
        }
        if (!empty($recommend)) {
            $condition .= ' AND rc.recommend IN (:recommend) ';
        }

        if (!empty($priceRange)) {
            $condition .= ' AND ( ';
            foreach ($priceRange as $i => $priceValue) {
                $prices = explode('_', $priceValue);
                $condition .= " p.selling_price BETWEEN :minPrice$i AND :maxPrice$i ";

                if ($i != array_key_last($priceRange)) {
                    $condition .= ' OR ';
                }
            }
            $condition .= ' )';
        }

        if (!empty($comments)) {
            $condition .= ' AND ( ';
            foreach ($comments as $i => $comment) {
                $commentSet = explode('_', $comment);
                $condition .= " length(r.comment) BETWEEN :minComment$i AND :maxComment$i ";

                if ($i != array_key_last($comments)) {
                    $condition .= ' OR ';
                }
            }
            $condition .= ' )';
        }

        $columns = "
        r.rvw_id AS id, r.active,  r.headline, r.comment,
        CASE
        WHEN LENGTH(r.comment) BETWEEN 5 AND 75 THEN 'Brief'
        WHEN LENGTH(r.comment) BETWEEN 76 AND 225 THEN 'Medium'
        WHEN LENGTH(r.comment) BETWEEN 226 AND 445 THEN 'Long'
        WHEN LENGTH(r.comment) BETWEEN 446 AND 900 THEN 'Paragraph'
        ELSE 'Other' END AS length_comment,
        l.vote_like AS \"like\",  r.rating AS rating, r.fit AS fit,
        r.width AS width, r.comfort AS comfort, rc.recommend AS recommend,
        d.delivery AS delivery,
        rimg.image AS image,
        rimg.created AS review_created,
        rimg2.image AS image2,
        rimg2.created AS review2_created,
        rimg3.image AS image3,
        rimg3.created AS review3_created,
        rimg4.image AS image4,
        rimg4.created AS review4_created,
        r.created AS created,
        r.updated AS updated,
        u.user_id AS \"userId\",
        u.title AS title,
        u.email AS email,
        u.first_name AS \"firstName\",
        u.last_name AS \"lastName\",
        ua.country AS country,
        pr.pro_id AS \"productId\",
        pr.name AS name,
        pr.brand AS brand,
        pr.category AS category,
        pr.occasion AS occasion,
        pr.type AS \"type\",
        (COUNT(DISTINCT rimg.image) +
         COUNT(DISTINCT rimg2.image) +
         COUNT(DISTINCT rimg3.image) +
         COUNT(DISTINCT rimg4.image)) AS \"review_image_count\",
        COUNT(*) OVER (PARTITION BY u.user_id) AS \"total_reviews\"
      ";

        $join = '
        LEFT JOIN LATERAL (
          SELECT l.vote_like FROM review_likes l
          WHERE l.fk_rvw_id = r.rvw_id
          ORDER BY l.vote_like DESC LIMIT 1) l ON true

        LEFT JOIN LATERAL (
          SELECT rc.recommend FROM review_recommends rc
          WHERE rc.fk_rvw_id = r.rvw_id
          ORDER BY rc.recommend DESC LIMIT 1) rc ON true

        LEFT JOIN LATERAL (
          SELECT d.delivery FROM review_delivery d
          WHERE d.fk_rvw_id = r.rvw_id
          ORDER BY d.created DESC LIMIT 1) d ON true

        LEFT JOIN LATERAL (
          SELECT rimg.image, rimg.created FROM review_image rimg
          WHERE rimg.fk_rvw_id = r.rvw_id
          ORDER BY rimg.created DESC LIMIT 1) rimg ON true

        LEFT JOIN LATERAL (
          SELECT rimg2.image, rimg2.created FROM review_image2 rimg2
          WHERE rimg2.fk_rvw_id = r.rvw_id
          ORDER BY rimg2.created DESC LIMIT 1) rimg2 ON true

        LEFT JOIN LATERAL (
          SELECT rimg3.image, rimg3.created FROM review_image3 rimg3
          WHERE rimg3.fk_rvw_id = r.rvw_id
          ORDER BY rimg3.created DESC LIMIT 1) rimg3 ON true

        LEFT JOIN LATERAL (
          SELECT rimg4.image, rimg4.created FROM review_image4 rimg4
          WHERE rimg4.fk_rvw_id = r.rvw_id
          ORDER BY rimg4.created DESC LIMIT 1) rimg4 ON true

        INNER JOIN users u ON r.fk_user_id = u.user_id
        INNER JOIN user_address ua ON u.user_id = ua.fk_user_id
        INNER JOIN products pr ON pr.pro_id = r.fk_pro_id
      ';

        $sql = "
        SELECT
        $columns
        FROM product_reviews r
        $join
        WHERE r.active = :status
        $condition
        GROUP BY
          r.rvw_id, r.active, r.headline, r.comment, l.vote_like,  r.rating,
          r.fit, r.width, r.comfort, rc.recommend, d.delivery, rimg.image,
          rimg2.image, rimg3.image, rimg4.image,  rimg.created, rimg2.created,
          rimg3.created, rimg4.created,
          r.created, r.updated, pr.pro_id, u.user_id, u.title,
          u.email, u.first_name, u.last_name, ua.country, pr.pro_id,
          pr.name, pr.brand, pr.category, pr.occasion, pr.type,
          u.user_id
      ";

        if ((!empty($thumbnail) and 'all' === $thumbnail)
             or !empty($priceRange)
        ) {
            $sql =
              "SELECT DISTINCT ON( pc.clr_pvt_id, u.user_id, $sort)
          $columns
           ,p.pro_pvt_id AS \"productDataId\",
            pc.image_md AS \"imageMedium\",
            pc.clr_pvt_id AS \"colorId\",
            pc.color, pc.fabric
           FROM product_reviews r
           $join
           INNER JOIN product_data p ON pr.pro_id = p.fk_pro_id
           INNER JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id
           WHERE r.active = :status
           $condition
          GROUP BY
            r.rvw_id, r.active, r.headline, r.comment, l.vote_like,  r.rating,
            r.fit, r.width, r.comfort, rc.recommend, d.delivery, rimg.image,
            rimg2.image, rimg3.image, rimg4.image,  rimg.created, rimg2.created,
            rimg3.created, rimg4.created,
            r.created, r.updated, pr.pro_id, u.user_id, u.title,
            u.email, u.first_name, u.last_name, ua.country, pr.pro_id,
            pr.name, pr.brand, pr.category, pr.occasion, pr.type, p.pro_pvt_id,
            pc.clr_pvt_id, pc.color, pc.fabric, pc.image_md,
            u.user_id
          ";
        }

        if ((!empty($thumbnail) and 'purchased' === $thumbnail)
            or (!empty($thumbnail) and 'purchased' === $thumbnail and !empty($priceRange))
             or !empty($sizes) or !empty($fabrics) or !empty($minPrice) or !empty($maxPrice) or !empty($colors)) {
            $sql =
            "SELECT DISTINCT ON( pc.clr_pvt_id, u.user_id, $sort)
         $columns
          ,p.pro_pvt_id AS \"productDataId\",
           pc.image_md AS \"imageMedium\",
           pc.clr_pvt_id AS \"colorId\",
           pc.color, pc.fabric
          FROM product_reviews r
          $join
          INNER JOIN review_data rd ON r.rvw_id = rd.fk_rvw_id
          INNER JOIN product_data p ON p.pro_pvt_id = rd.fk_pro_id
          INNER JOIN product_color pc ON pc.clr_pvt_id = rd.fk_clr_id
          WHERE r.active = :status
          $condition
          GROUP BY
            r.rvw_id, r.active, r.headline, r.comment, l.vote_like,  r.rating,
            r.fit, r.width, r.comfort, rc.recommend, d.delivery, rimg.image,
            rimg.created, rimg2.image, rimg3.image, rimg4.image,  rimg.created,
            rimg2.created,rimg3.created, rimg4.created,r.created, r.updated,
            pr.pro_id, u.user_id, u.title,
            u.email, u.first_name, u.last_name, ua.country, pr.pro_id,
            pr.name, pr.brand, pr.category, pr.occasion, pr.type, p.pro_pvt_id,
            pc.clr_pvt_id, pc.color, pc.fabric, pc.image_md,
            u.user_id
          ";
        }

        if (!empty($uploaded)) {
            $sql .= ' HAVING ';
            foreach ($uploaded as $i => $u) {
                if (end($uploaded) == $u) {
                    $sql .= " (COUNT(DISTINCT rimg.image) +
                       COUNT(DISTINCT rimg2.image) +
                       COUNT(DISTINCT rimg3.image) +
                       COUNT(DISTINCT rimg4.image)) = :uploaded$i ";
                } else {
                    $sql .= " (COUNT(DISTINCT rimg.image) +
                          COUNT(DISTINCT rimg2.image) +
                          COUNT(DISTINCT rimg3.image) +
                          COUNT(DISTINCT rimg4.image)) = :uploaded$i OR";
                }
            }
        }

        $sql .= " ORDER BY  $order NULLS LAST ";

        $map = new Map();
        $map->$add('id', 'id')->$add('active', 'active')
            ->$add('headline', 'headline')->$add('comment', 'comment')
            ->$add('length_comment', 'length_comment')
            ->$add('like', 'like')->$add('rating', 'rating')
            ->$add('fit', 'fit')->$add('width', 'width')
            ->$add('comfort', 'comfort')->$add('recommend', 'recommend')
            ->$add('delivery', 'delivery')
            ->$add('created', 'created')
            ->$add('updated', 'updated')
            ->$add('userId', 'userId')->$add('title', 'title')
            ->$add('email', 'email')->$add('firstName', 'firstName')
            ->$add('lastName', 'lastName')->$add('country', 'country')
            ->$add('productId', 'productId')->$add('name', 'name')
            ->$add('brand', 'brand')->$add('category', 'category')
            ->$add('occasion', 'occasion')->$add('type', 'type')
            ->$add('total_reviews', 'total_reviews')
            ->$add('review_image_count', 'review_image_count')
        ;

        for ($i = 1; $i <= 4; ++$i) {
            $suffix = (1 === $i) ? '' : $i;
            $map->$add("image$suffix", "image$suffix");
            $map->$add("review{$suffix}_created", "review{$suffix}_created");
        }

        if (!empty($colors) or !empty($fabrics) or !empty($colorExclude)
            or !empty($sizes) or 'purchased' === $thumbnail
            or 'all' === $thumbnail) {
            $map
              ->$add('productDataId', 'productDataId')
              ->$add('imageMedium', 'imageMedium')
              ->$add('colorId', 'colorId')->$add('color', 'color')
              ->$add('fabrics', 'fabrics');
        }

        $query = $em->$create($sql, $map);
        $query->$set('status', true);

        if (!empty($keywords)) {
            $query->$set('keywords', $keywords);
        }
        if (!empty($brands)) {
            $query->$set('brands', $brands);
        }
        if (!empty($rating)) {
            $query->$set('rating', $rating);
        }
        if (!empty($customer)) {
            $query->$set('customer', $customer);
        }
        if (!empty($brands)) {
            $query->$set('brands', $brands);
        }
        if (!empty($types)) {
            $query->$set('types', $types);
        }
        if (!empty($occasions)) {
            foreach ($occasions as $i => $occasion) {
                $query->$set("occasion$i", '["'.$occasion.'"]');
            }
        }
        if (!empty($categories)) {
            $query->$set('categories', $categories);
        }
        if (!empty($colors)) {
            $query->$set('colors', $colors);
        }
        if (!empty($fabrics)) {
            foreach ($fabrics as $i => $fabric) {
                $query->$set("fabric$i", '["'.$fabric.'"]');
            }
        }
        if (!empty($colorExclude)) {
            $query->$set('colorExclude', $colorExclude);
        }
        if (!empty($sizes)) {
            $query->$set('sizes', $sizes);
        }
        if (!empty($product)) {
            $query->$set('product', $product);
        }
        if (!empty($like)) {
            $query->$set('like', $like);
        }
        if (!empty($delivery)) {
            $query->$set('delivery', $delivery);
        }
        if (!empty($recommend)) {
            $query->$set('recommend', $recommend);
        }
        if (!empty($fit)) {
            $query->$set('fit', $fit);
        }
        if (!empty($width)) {
            $query->$set('width', $width);
        }
        if (!empty($comfort)) {
            $query->$set('comfort', $comfort);
        }
        if (!empty($uploaded)) {
            foreach ($uploaded as $i => $u) {
                $query->$set("uploaded$i", $u);
            }
        }

        if (!empty($priceRange)) {
            foreach ($priceRange as $i => $priceValue) {
                $prices = explode('_', $priceValue);
                $minPrice = $prices[0];
                $maxPrice = $prices[1];
                $query->$set("minPrice$i", $minPrice)
                      ->$set("maxPrice$i", $maxPrice);
            }
        }

        if (!empty($comments)) {
            foreach ($comments as $i => $commentLength) {
                $lengths = explode('_', $commentLength);
                $minComment = $lengths[0];
                $maxComment = $lengths[1];
                $query->$set("minComment$i", $minComment)
                      ->$set("maxComment$i", $maxComment);
            }
        }

        $initialReviews = $query->$result();

        if (empty($initialReviews)) {
            return [];
        }

        foreach ($initialReviews as $i => $review) {
            $initialReviews[$i] = $this->fullName($review);

            $created = new \DateTime($review['created']);
            $initialReviews[$i]['created_date'] = $created->format('j M Y');

            $updated = new \DateTime($review['updated']);
            $initialReviews[$i]['updated_date'] = $updated->format('j M Y');
        }

        foreach ($initialReviews as $i => $review) {
            $reviews[] = $review['id'];
            $users[] = $review['userId'];

            $results[$review['id']] = $review;
            if ((!empty($thumbnail) and ('purchased' === $thumbnail
                or 'all' === $thumbnail)) or !empty($colors) or !empty($fabrics)
                or !empty($colorExclude) or !empty($sizes)) {
                $colorKeys = [
                    'itemId', 'colorId', 'color', 'colors_set', 'colors_full_set',
                    'colors_full', 'fabrics', 'imageMedium', 'productDataId'];

                foreach ($review as $key => $value) {
                    if (in_array($key, $colorKeys)) {
                        $reviewColors[$review['id']][$review['colorId']][$key] = $review[$key];
                    }
                }

                foreach ($review as $key => $value) {
                    if (!in_array($key, $colorKeys)) {
                        $results[$review['id']][$key] = $review[$key];
                    }
                }

                $results[$review['id']]['thumbnails'] = $reviewColors[$review['id']];
            } else {
                $results[$review['id']]['thumbnails'] = [];
            }
        }

        $reviewers = $this->reviewerCount($users);
        $helpful = $this->helpfulRepo->helpful($reviews);
        $notHelpful = $this->helpfulRepo->notHelpful($reviews);

        foreach ($results as $i => $review) {
            if (array_key_exists($i, $helpful)) {
                $results[$review['id']]['helpful'] = $helpful[$i];
            } else {
                $results[$review['id']]['helpful'] = [];
            }

            if (array_key_exists($i, $notHelpful)) {
                $results[$review['id']]['notHelpful'] = $notHelpful[$i];
            } else {
                $results[$review['id']]['notHelpful'] = [];
            }

            if (array_key_exists($review['userId'], $reviewers)) {
                $results[$review['id']]['reviewerCount'] = $reviewers[$review['userId']];
            }

            $results[$review['id']]['reviewer'] = null;
            $results[$review['id']]['reviewerId'] = null;

            if (!empty($user)) {
                $reviewHelpfuls = $this->helpfulRepo->checkHelpfuls($reviews, $user);
                if (array_key_exists($i, $reviewHelpfuls)) {
                    $results[$review['id']]['reviewer'] = $reviewHelpfuls[$i]['helpful'];
                    $results[$review['id']]['reviewerId'] = $reviewHelpfuls[$i]['id'];
                }
            }
        }

        return $results;
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ReviewData
     */
    public function adminReviews($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'ASC';
        $sort = $q->has('sort') ? $q->get('sort') : 'r.id';
        $rating = $q->has('rating') ? $q->get('rating') : '';
        $customer = $q->has('customer') ? $q->get('customer') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $fit = $q->has('fit') ? $q->get('fit') : '';
        $width = $q->has('width') ? $q->get('width') : '';
        $comfort = $q->has('comfort') ? $q->get('comfort') : '';
        $like = '';
        $delivery = '';
        $recommend = '';

        if ($q->has('like')) {
            switch ($x = $q->get('like')) {
                case $x = 1: $like = true;
                    break;
                case $x = 0: $like = false;
                    break;
                case $x = 'null': $like = null;
                    break;
            }
        }
        if ($q->has('delivery')) {
            switch ($x = $q->get('delivery')) {
                case $x = 1: $delivery = true;
                    break;
                case $x = 0: $delivery = false;
                    break;
                case $x = 'null': $delivery = null;
                    break;
            }
        }
        if ($q->has('recommend')) {
            switch ($x = $q->get('recommend')) {
                case $x = 1: $recommend = true;
                    break;
                case $x = 0: $recommend = false;
                    break;
                case $x = 'null': $recommend = null;
                    break;
            }
        }

        $dql = 'SELECT
         r.id, r.active,
         r.headline, r.comment,
         l.like, r.rating,
         r.fit, r.width, r.comfort,
         rc.recommend,
         d.delivery,
         rimg.image as review_img, rimg.created as pic_created,
         r.created, r.updated, i.item_id as itemId, p.size,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId, pc.color, pc.fabric, tx.texture,
         pc.imageMedium,
         u.id as userId, u.title, u.email, u.firstName, u.lastName,
         ui.image as pic

       FROM App\Entity\Review\Review r

       LEFT JOIN r.product pr
       LEFT JOIN pr.productData p
       LEFT JOIN r.reviewLike l
       LEFT JOIN r.reviewRecommend rc
       LEFT JOIN r.reviewDelivery d
       LEFT JOIN r.reviewImage rimg
       LEFT JOIN r.users u
       LEFT JOIN u.userImage ui
       LEFT JOIN u.orders o
       LEFT JOIN o.items i
       LEFT JOIN i.product pp
       LEFT JOIN pp.color pc
       LEFT JOIN pc.texture tx

       WHERE pp.id =  p.id
       ';

        if (!empty($rating) or !empty($customer) or !empty($brand)
            or !empty($product) or '' !== $like or '' !== $delivery
            or '' !== $recommend or !empty($fit) or !empty($width)
            or !empty($comfort)) {
            $dql .= ' AND ';
        }

        if (!empty($rating)) {
            $dql .= ' r.rating = :rating ';
        }
        if (!empty($customer)) {
            $dql .= ' u.id = :customer ';
        }
        if (!empty($brand)) {
            $dql .= ' pr.brand = :brand ';
        }
        if (!empty($product)) {
            $dql .= ' pr.id = :product ';
        }
        if ('' !== $like) {
            if (null === $like) {
                $dql .= ' l.like IS NULL ';
            } else {
                $dql .= ' l.like = :like ';
            }
        }
        if ('' !== $delivery) {
            if (null === $delivery) {
                $dql .= ' d.delivery IS NULL ';
            } else {
                $dql .= ' d.delivery = :delivery ';
            }
        }
        if ('' !== $recommend) {
            if (null === $recommend) {
                $dql .= ' rc.recommend IS NULL ';
            } else {
                $dql .= ' rc.recommend = :recommend ';
            }
        }
        if (!empty($fit)) {
            $dql .= ' r.fit = :fit ';
        }
        if (!empty($width)) {
            $dql .= ' r.width = :width ';
        }
        if (!empty($comfort)) {
            $dql .= ' r.comfort = :comfort ';
        }

        /* If the 'user' column is being used to sort the results, the ORDER BY
           sorts rows by 'firstName' first. Then it sorts the rows by 'lastName',
           both using the same order.
           */
        if ('u.firstName' == $sort) {
            $dql .= " ORDER BY u.firstName $order, u.lastName $order ";
        } else {
            $dql .= " ORDER BY $sort $order ";
        }

        $query = $em->createQuery($dql);
        if (!empty($rating)) {
            $query->setParameter('rating', $rating);
        }
        if (!empty($customer)) {
            $query->setParameter('customer', $customer);
        }
        if (!empty($brand)) {
            $query->setParameter('brand', $brand);
        }
        if (!empty($product)) {
            $query->setParameter('product', $product);
        }
        if (null !== $like and '' !== $like) {
            $query->setParameter('like', $like);
        }
        if (null !== $delivery and '' !== $delivery) {
            $query->setParameter('delivery', $delivery);
        }
        if (null !== $recommend and '' !== $recommend) {
            $query->setParameter('recommend', $recommend);
        }
        if (null !== $recommend and '' !== $recommend) {
            $query->setParameter('recommend', $recommend);
        }
        if (!empty($fit)) {
            $query->setParameter('fit', $fit);
        }
        if (!empty($width)) {
            $query->setParameter('width', $width);
        }
        if (!empty($comfort)) {
            $query->setParameter('comfort', $comfort);
        }

        $initialReviews = $query->getResult();

        if (empty($initialReviews)) {
            return [];
        }

        foreach ($initialReviews as $i => $product) {
            $initialReviews[$i] = $this->fullName($product);
        }

        foreach ($initialReviews as $i => $product) {
            $colorKeys = [
                'itemId', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageMedium', 'quantity', 'sellingPrice', 'size'];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['itemId']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['id']][$key] = $product[$key];
                }
            }

            $results[$product['id']]['thumbnails'][] = $productColors[$product['itemId']];
        }

        return $results;
    }

    public function search(Search $search, $query): array
    {
        $em = $this->getEntityManager();
        $order = $query->has('order') ? $query->get('order') : 'DESC';
        $sort = $query->has('sort') ? $query->get('sort') : 'r.created';

        if (empty($search->search())) {
            return [];
        }

        $keywords = '';

        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Arrange all words in an array
            // $keywords = explode(' ', $keywords );
        }

        $dql = 'SELECT
         r.id, r.active,
         r.headline, r.comment,
         l.like, r.rating,
         r.fit, r.width, r.comfort,
         rc.recommend,
         d.delivery,
         rimg.image as review_img, rimg.created as pic_created,
         r.created, r.updated, i.item_id as itemId,
         p.id as productDataId, p.size,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
         pc.imageMedium,
         u.id as userId, u.title, u.email, u.firstName, u.lastName,
         ui.image as pic

       FROM App\Entity\Review\Review r

       LEFT JOIN r.product pr
       LEFT JOIN pr.productData p
       LEFT JOIN r.reviewLike l
       LEFT JOIN r.reviewRecommend rc
       LEFT JOIN r.reviewDelivery d
       LEFT JOIN r.reviewImage rimg
       LEFT JOIN r.users u
       LEFT JOIN u.userImage ui
       LEFT JOIN u.orders o
       LEFT JOIN o.items i
       LEFT JOIN i.product pp
       LEFT JOIN pp.color pc
       LEFT JOIN pc.texture tx


       WHERE pp.id = p.id AND (';

        if (!empty($keywords)) {
            $dql .= '
          (ILIKE(r.headline, :word) = true) OR
          (ILIKE(r.comment, :word) = true) OR
          (ILIKE(pr.name, :word) = true) OR
          (ILIKE(pr.brand, :word) = true) OR
          (ILIKE(pr.category, :word) = true) OR
          (ILIKE(JSON_TEXT(pr.occasion), :word) = true) OR
          (ILIKE(pr.type, :word) = true) OR
          (ILIKE(u.email, :word) = true) ';
            $dql .= ')';

            /* If the user column is being used to sort the results, the ORDER BY
               sorts rows by first name first. Then it sorts the rows by last name,
               both using the same order.
               */
            if ('u.firstName' == $sort) {
                $dql .= " ORDER BY u.firstName $order, u.lastName $order ";
            } else {
                $dql .= " ORDER BY $sort $order ";
            }

            $query = $em->createQuery($dql);
            $query->setParameter('word', "%$keywords%");
        }

        $initialReviews = $query->getResult();

        $results = [];
        if (empty($initialReviews)) {
            return [];
        }

        foreach ($initialReviews as $i => $product) {
            $initialReviews[$i] = $this->fullName($product);
        }

        foreach ($initialReviews as $i => $product) {
            $colorKeys = [
                'itemId', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageMedium', 'quantity', 'sellingPrice', 'size',
                'productDataId'];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['itemId']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $results[$product['id']][$key] = $product[$key];
                }
            }

            $results[$product['id']]['thumbnails'][] = $productColors[$product['itemId']];
        }

        return $results;
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ProductData
     */
    public function meanRating(int $product)
    {
        $sql =
        'SELECT AVG(r.rating)::NUMERIC(10, 0)
       FROM product_reviews r
       LEFT JOIN products pr ON r.fk_pro_id = pr.pro_id
       WHERE  pr.pro_id = :product';

        $parameters = [$product];
        $type = [Type::INTEGER];
        $c = $this->getEntityManager()->getConnection();
        $meanRating = $c->executeQuery($sql, $parameters, $type)
                        ->fetchAllAssociative();

        if (!empty($meanRating[0]['avg'])) {
            $meanRating = abs(floor($meanRating[0]['avg']));

            return $meanRating;
        }

        return '';
    }

    public function countReviews(int $product)
    {
        $sql =
        'SELECT COUNT(DISTINCT r.rvw_id)
       FROM product_reviews r
       LEFT JOIN products pr ON r.fk_pro_id = pr.pro_id
       WHERE  pr.pro_id = :product';

        $parameters = [$product];
        $type = [Type::INTEGER];
        $c = $this->getEntityManager()->getConnection();
        $count = $c->executeQuery($sql, $parameters, $type)
                   ->fetchAllAssociative();
        if (!empty($count)) {
            return $count[0]['count'];
        }

        return '';
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ProductData
     */
    public function productReviews($r, int $product)
    {
        $page = $r->query->getInt('page', 1);
        $page_size = $this->max->reviews();
        $query = $this->createQueryBuilder('r');
        $query->leftJoin('r.product', 'pr')
              ->andWhere('pr.id = :product')
              ->setParameter('product', $product)
              ->andWhere('r.active = :active')
              ->setParameter('active', true)
              ->orderBy('r.updated', 'ASC')
              ->orderBy('r.created', 'DESC')
              ->getQuery()
        ;

        $paginator = new Paginator();
        $paginator = $paginator->paginate($query, $page, $page_size);

        if (empty($paginator)) {
            return [];
        }

        return $paginator;
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ProductData
     */
    public function bestReview(int $product)
    {
        $sql = 'SELECT
         r.rvw_id as reviewId,
         r.headline, r.comment,
         l.vote_like,
         r.rating,
         r.fit, r.width, r.comfort,
         rc.recommend,
         d.delivery,
         r.created, r.updated,
         pr.pro_id AS productId,
         u.user_id as userId, u.first_name as "firstName",
         u.last_name as "lastName",
         ((r.rating * 15) + length(r.headline) + length(r.comment)) as sum

       FROM product_reviews r

       LEFT JOIN products pr ON r.fk_pro_id = pr.pro_id
       LEFT JOIN users u ON r.fk_user_id = u.user_id
       LEFT JOIN review_likes l ON r.rvw_id = l.fk_rvw_id
       LEFT JOIN review_recommends rc ON r.rvw_id = rc.fk_rvw_id
       LEFT JOIN review_delivery d ON r.rvw_id = d.fk_rvw_id

       WHERE r.active = :active AND pr.pro_id = :product
       ORDER BY sum DESC
       LIMIT 1
       ';

        $active = 1;
        $parameters[] = $active;
        $type[] = Type::INTEGER;

        $parameters[] = $product;
        $type[] = Type::INTEGER;
        $c = $this->getEntityManager()->getConnection();
        $bestReview = $c->executeQuery($sql, $parameters, $type)
                        ->fetchAllAssociative();

        if (!empty($bestReview)) {
            return $bestReview[0];
        }

        return [];
    }

    /*
     This query executes the following SQL query,
       ('SELECT search_vector FROM product_reviews r')
     which must return a single tsvector column,
     and then returns statistics for each distinct lexeme found in the data.
     The results is ordered by frequency (nentry), number of documents (ndoc), or alphabetically by word.

     */
    public function reviewWords($q, $search)
    {
        $c = $this->getEntityManager()->getConnection();

        if (empty($q->all())) {
            $sql =
              " SELECT * FROM ts_stat(
              'SELECT search_vector FROM product_reviews r')
            ORDER BY nentry DESC, ndoc DESC, word
            LIMIT :limit;
         ";

            $parameters = ['limit' => 30];
            $type = ['limit' => Type::INTEGER];

            $result =
                $c->executeQuery($sql, $parameters, $type)
                  ->fetchAllAssociative();

            $reviewWords = array_column($result, 'word');

            return !empty($reviewWords) ? $reviewWords : [];
        }

        $rating = $q->has('rating') ? $q->all('rating') : '';
        $customer = $q->has('customer') ? $this->encryptor->decrypt($q->get('customer')) : '';
        $brands = $q->has('brands') ? $q->all('brands') : '';
        $types = $q->has('type') ? $q->all('type') : '';
        $occasions = $q->has('occasion') ? $q->all('occasion') : '';
        $categories = $q->has('category') ? $q->all('category') : '';
        $colors = $q->has('color') ? implode('|', $q->all('color')) : '';
        $colorExclude = $q->has('color_exclude') ? implode('|', $q->all('color_exclude')) : '';
        $fabrics = $q->has('fabrics') ? $q->all('fabrics') : '';
        $textures = $q->has('textures') ? $q->all('textures') : '';
        $tags = $q->has('tags') ? $q->all('tags') : '';
        $sizes = $q->has('size') ? $q->all('size') : '';
        $minPrice = $q->has('price') ? $q->all('price')['min'] : 500;
        $maxPrice = $q->has('price') ? $q->all('price')['max'] : 25000;
        $priceRange = $q->has('price_range') ? $q->all('price_range') : '';
        $product = $q->has('product') ? $this->encryptor->decrypt($q->get('product')) : '';
        $fit = $q->has('fit') ? $q->all('fit') : '';
        $width = $q->has('width') ? $q->all('width') : '';
        $comfort = $q->has('comfort') ? $q->all('comfort') : '';
        $like = $q->has('like') ? $q->all('like') : '';
        $delivery = $q->has('delivery') ? $q->all('delivery') : '';
        $recommend = $q->has('recommend') ? $q->all('recommend') : '';

        if (!empty($sizes)) {
            foreach ($sizes as $k => $size) {
                if (array_key_exists($size, $this->adultKidSizeSet(false))) {
                    $sizes[$k] = $this->adultKidSizeSet()[$size];
                }
            }
        }

        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $keywords);
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Replace whitespace with '|'
            $keywords = preg_replace('/\s+/', '|', $keywords);
        }

        $condition = '';
        $format = '';
        if (!empty($keywords)) {
            $condition .= ' AND (
                    r.headline ~* %L  OR
                    r.comment ~* %L  OR
                    pr.name ~* %L  OR
                    pr.brand~* %L OR
                    u.email~* %L OR
                    u.first_name ~* %L OR
                    u.last_name ~* %L
                    )';
            $format .= ' ,:keywords::text, :keywords::text, :keywords::text,
                      :keywords::text, :keywords::text, :keywords::text,
                      :keywords::text';
        }

        if (!empty($rating)) {
            $fill = implode(',', array_fill(0, count($rating), '%L'));
            $condition .= " AND r.rating IN ($fill) ";

            $fill = ' , ';
            foreach ($rating as $i => $rate) {
                if (end($rating) == $rate) {
                    $fill .= ":rating$i::integer";
                } else {
                    $fill .= ":rating$i::integer, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($customer)) {
            $condition .= ' AND u.user_id = %L ';
            $format .= ' , :customer::integer';
        }

        if (!empty($brands)) {
            // Create string "%L, %L" for an array of 2 brands.
            $fill = implode(',', array_fill(0, count($brands), '%L'));
            $condition .= " AND pr.brand IN ($fill) ";

            // Create ":brand0::text, :brand1::text" for an array of 2 brands.
            $fill = ' , ';
            foreach ($brands as $i => $brand) {
                if (end($brands) == $brand) {
                    $fill .= ":brand$i::text";
                } else {
                    $fill .= ":brand$i::text, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($product)) {
            $condition .= ' AND pr.pro_id = %L ';
            $format .= ' , :product::integer';
        }
        if (!empty($types)) {
            $sql .= ' AND pr.type IN (:types) ';
        }

        if (!empty($occasions)) {
            $fill = ' , ';
            $condition .= ' AND ( ';
            foreach ($occasions as $i => $occasion) {
                if (end($occasions) == $occasion) {
                    $condition .= ' pr.occasion @> %L ';
                    $fill .= ":occasion$i::text";
                } else {
                    $condition .= ' pr.occasion @> %L OR ';
                    $fill .= ":occasion$i::text, ";
                }
            }
            $condition .= ' ) ';
            $format .= " $fill ";
        }

        if (!empty($categories)) {
            $sql .= ' AND pr.category IN (:categories) ';
        }

        if (!empty($colors)) {
            $condition .= ' AND pc.color ~* %L ';
            $format .= ' , :colors::text ';
        }

        if (!empty($colorExclude)) {
            $condition .= ' AND pc.color !~* %L ';
            $format .= ' , :colorExclude::text ';
        }

        if (!empty($fabrics)) {
            $fill = ' , ';
            $condition .= ' AND ( ';
            foreach ($fabrics as $i => $fabric) {
                if (end($fabrics) == $fabric) {
                    $condition .= ' pc.fabric @> %L ';
                    $fill .= ":fabric$i::text";
                } else {
                    $condition .= ' pc.fabric @> %L OR ';
                    $fill .= ":fabric$i::text, ";
                }
            }
            $condition .= ' ) ';
            $format .= " $fill ";
        }

        if (!empty($sizes)) {
            $fill = implode(',', array_fill(0, count($sizes), '%L'));
            $condition .= " AND p.size IN ($fill) ";

            $fill = ' , ';
            foreach ($sizes as $i => $size) {
                if (end($sizes) == $size) {
                    $fill .= ":size$i::text";
                } else {
                    $fill .= ":size$i::text, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($categories)) {
            $sql .= ' AND pr.category IN (:categories) ';
        }

        if (!empty($fit)) {
            $fill = implode(',', array_fill(0, count($fit), '%L'));
            $condition .= " AND r.fit IN ($fill) ";

            $fill = ' , ';
            foreach ($fit as $i => $ft) {
                if (end($fit) == $ft) {
                    $fill .= ":fit$i::integer";
                } else {
                    $fill .= ":fit$i::integer, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($width)) {
            $fill = implode(',', array_fill(0, count($width), '%L'));
            $condition .= " AND r.width IN ($fill) ";

            $fill = ' , ';
            foreach ($width as $i => $wd) {
                if (end($width) == $wd) {
                    $fill .= ":width$i::integer";
                } else {
                    $fill .= ":width$i::integer, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($comfort)) {
            $fill = implode(',', array_fill(0, count($comfort), '%L'));
            $condition .= " AND r.comfort IN ($fill) ";

            $fill = ' , ';
            foreach ($comfort as $i => $wd) {
                if (end($comfort) == $wd) {
                    $fill .= ":comfort$i::integer";
                } else {
                    $fill .= ":comfort$i::integer, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($like)) {
            $fill = implode(',', array_fill(0, count($like), '%L'));
            $condition .= " AND l.vote_like IN ($fill) ";

            $fill = ' , ';
            foreach ($like as $i => $lk) {
                if (end($like) == $lk) {
                    $fill .= ":like$i::bool";
                } else {
                    $fill .= ":like$i::bool, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($delivery)) {
            $fill = implode(',', array_fill(0, count($delivery), '%L'));
            $condition .= " AND d.delivery IN ($fill) ";

            $fill = ' , ';
            foreach ($delivery as $i => $dl) {
                if (end($delivery) == $dl) {
                    $fill .= ":delivery$i::bool";
                } else {
                    $fill .= ":delivery$i::bool, ";
                }
            }
            $format .= " $fill ";
        }

        if (!empty($recommend)) {
            $fill = implode(',', array_fill(0, count($recommend), '%L'));
            $condition .= " AND rc.recommend IN ($fill) ";

            $fill = ' , ';
            foreach ($recommend as $i => $lk) {
                if (end($recommend) == $lk) {
                    $fill .= ":recommend$i::bool";
                } else {
                    $fill .= ":recommend$i::bool, ";
                }
            }
            $format .= " $fill ";
        }

        /*
          // NOTE:
          The format() function is used to dynamically insert the inner query string into the ts_stat function argument.

          The format($$...$$, ...) syntax uses dollar quoting to safely embed the SQL string, avoiding issues with single quotes.

          %I: Safely quotes the column name. I: Identifier
          %L: Escapes values as SQL literals, making it safe for dynamic input.
              L: Literal

          :headline::text:  Explicitly cast the parameter to text.
          Ensures PostgreSQL knows the data type of the value being compared, preventing the indeterminate_datatype error. The cast is optimized away during planning, so it has negligible performance impact.
        */
        $sql = "SELECT * FROM ts_stat(
          format(
          $$
            SELECT r.search_vector  FROM product_reviews r
            INNER JOIN products pr ON r.fk_pro_id = pr.pro_id
            INNER JOIN product_data p ON pr.pro_id = p.fk_pro_id
            INNER JOIN product_color pc ON pc.fk_pro_id = pr.pro_id
            INNER JOIN users u ON r.fk_user_id = u.user_id

            LEFT JOIN LATERAL (
              SELECT l.vote_like FROM review_likes l
              WHERE l.fk_rvw_id = r.rvw_id
              ORDER BY l.vote_like DESC LIMIT 1) l ON true

            LEFT JOIN LATERAL (
              SELECT d.delivery FROM review_delivery d
              WHERE d.fk_rvw_id = r.rvw_id
              ORDER BY d.created DESC LIMIT 1) d ON true

            LEFT JOIN LATERAL (
              SELECT rc.recommend FROM review_recommends rc
              WHERE rc.fk_rvw_id = r.rvw_id
              ORDER BY rc.recommend DESC LIMIT 1) rc ON true

            WHERE r.rating IS NOT NULL
            $condition
          $$
          $format
          ))
          ORDER BY nentry DESC, ndoc DESC, word
          LIMIT :limit
          ";

        $map = new Map();
        $map->addScalarResult('word', 'word');
        $map->addScalarResult('nentry', 'nentry', 'integer');
        $map->addScalarResult('ndoc', 'ndoc', 'integer');
        $limit = 30;

        $query = $this->getEntityManager()
                      ->createNativeQuery($sql, $map);

        if (!empty($keywords)) {
            $query->setParameter('keywords', $keywords);
        }
        if (!empty($rating)) {
            $query->setParameter('rating', $rating);
        }
        if (!empty($rating)) {
            foreach ($rating as $i => $rate) {
                $query->setParameter("rating$i", $rate);
            }
        }
        if (!empty($customer)) {
            $query->setParameter('customer', $customer);
        }
        if (!empty($brands)) {
            foreach ($brands as $i => $brand) {
                $query->setParameter("brand$i", $brand);
            }
        }
        if (!empty($types)) {
            $query->setParameter('types', $types);
        }
        if (!empty($occasions)) {
            foreach ($occasions as $i => $occasion) {
                $query->setParameter("occasion$i", '["'.$occasion.'"]');
            }
        }
        if (!empty($categories)) {
            $query->setParameter('categories', $categories);
        }
        if (!empty($colors)) {
            $query->setParameter('colors', $colors);
        }
        if (!empty($colorExclude)) {
            $query->setParameter('colorExclude', $colorExclude);
        }
        if (!empty($fabrics)) {
            foreach ($fabrics as $i => $fabric) {
                $query->setParameter("fabric$i", '["'.$fabric.'"]');
            }
        }
        if (!empty($sizes)) {
            foreach ($sizes as $i => $size) {
                $query->setParameter("size$i", $size);
            }
        }
        if (!empty($product)) {
            $query->setParameter('product', $product);
        }
        if (!empty($like)) {
            foreach ($like as $i => $lk) {
                $query->setParameter("like$i", $lk);
            }
        }
        if (!empty($delivery)) {
            foreach ($delivery as $i => $dl) {
                $query->setParameter("delivery$i", $dl);
            }
        }
        if (!empty($recommend)) {
            foreach ($recommend as $i => $rc) {
                $query->setParameter("recommend$i", $rc);
            }
        }
        if (!empty($fit)) {
            foreach ($fit as $i => $ft) {
                $query->setParameter("fit$i", $ft);
            }
        }
        if (!empty($width)) {
            foreach ($width as $i => $wd) {
                $query->setParameter("width$i", $wd);
            }
        }
        if (!empty($comfort)) {
            foreach ($comfort as $i => $cf) {
                $query->setParameter("comfort$i", $cf);
            }
        }

        $query->setParameter('limit', $limit);

        $result = $query->getResult();
        $reviewWords = array_column($result, 'word');

        return !empty($reviewWords) ? $reviewWords : [];
    }

    public function reviewer(int $product, ?User $user)
    {
        if (empty($user)) {
            return '';
        }
        $user_id = $user->getId();
        $entityManager = $this->getEntityManager();

        $dql =
        'SELECT
         r.id,
         r.headline, r.comment,
         l.like, r.rating,
         r.fit, r.width, r.comfort,
         rc.recommend,
         d.delivery,
         rimg.image as reviewImage, rimg.created as pic_created,
         r.created, r.updated,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type, p.id as productDataId,
         pc.id AS colorId ,pc.color, pc.fabric, tx.texture,
         pc.imageSmall,
         u.firstName, u.lastName

       FROM App\Entity\Review\Review r

       LEFT JOIN r.users u
       LEFT JOIN u.userImage ui
       LEFT JOIN r.reviewLike l
       LEFT JOIN r.reviewRecommend rc
       LEFT JOIN r.reviewDelivery d
       LEFT JOIN r.reviewImage rimg

       LEFT JOIN r.reviewData rd
       LEFT JOIN rd.items i
       LEFT JOIN rd.product p
       LEFT JOIN rd.color pc
       LEFT JOIN r.product pr

       LEFT JOIN pc.texture tx


       WHERE pr.id = :product AND u.id = :user
       ';

        $query = $entityManager->createQuery($dql);
        $query->setParameter('product', $product)
              ->setParameter('user', $user_id);
        $initialReviews = $query->getResult();

        foreach ($initialReviews as $i => $product) {
            $initialReviews[$i] = $this->fullName($product);
        }

        foreach ($initialReviews as $i => $product) {
            $colorKeys = [
                'productDataId', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'imageSmall', 'quantity', 'sellingPrice',
            ];

            foreach ($product as $key => $value) {
                if (in_array($key, $colorKeys)) {
                    $productColors[$product['productId']][$product['color'].'-'.$product['colorId']][$key] = $product[$key];
                }
            }

            /* Sort PC in alphabetical order */
            foreach ($productColors as $j => $productColor) {
                ksort($productColor);
                $productColors[$j] = $productColor;
            }

            foreach ($product as $key => $value) {
                if (!in_array($key, $colorKeys)) {
                    $reviewer[$product['id']][$key] = $product[$key];
                }
            }

            $reviewer[$product['id']]['thumbnails'] = $productColors[$product['productId']];
        }
        if (!empty($reviewer)) {
            return array_values($reviewer)[0];
        }

        return [];
    }

    public function checkProduct(int $product)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(r) AS COUNT
         FROM App\Entity\Review\Review r
         LEFT JOIN r.product pr
         WHERE pr.id = :product
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('product', $product);
        $result = $query->getOneOrNullResult()['COUNT'];

        return (0 !== $result) ? true : false;
    }

    /**
     * Return an associative array of User ID and their corresponding
     * count of active reviews posted.
     */
    public function reviewerCount($users)
    {
        $entityManager = $this->getEntityManager();
        $dql =
        'SELECT COUNT(u.id) AS count,u.id
         FROM App\Entity\Review\Review r
         INNER JOIN r.users u
         WHERE u.id IN (:users)
         AND r.active IN (:status)
         GROUP BY u.id
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('users', $users)
              ->setParameter('status', true);

        $initial = $query->getScalarResult();

        $result = [];
        foreach ($initial as $i => $review) {
            $result[$review['id']] = $review['count'];
        }

        return $result;
    }

    /**
     * @return ReviewData[] Return a stripped version (array) of the ProductData
     */
    public function sample()
    {
        // $sql =
        // 'SELECT
        //   r.rvw_id AS id, r.active, r.headline, r.comment,
        //   l.vote_like AS like,  r.rating AS rating, r.fit AS fit,
        //   r.width AS width, r.comfort AS comfort, rc.recommend AS recommend,
        //   d.delivery AS delivery,
        //   rimg.image AS image,
        //   rimg.created AS pic_created,
        //   r.created AS created,
        //   r.updated AS updated,
        //   pr.pro_id AS "productId",
        //   u.user_id AS "userId",
        //   u.title AS title,
        //   u.email AS email,
        //   u.first_name AS "firstName",
        //   u.last_name AS "lastName",
        //   ua.country AS country,
        //   ui.image AS pic,
        //   greatest(
        //    ((EXTRACT(YEAR FROM r.created) * 365 * 24 * 60 * 60) +
        //     (EXTRACT(MONTH FROM r.created) * 30 * 24 * 60 * 60) +
        //     (EXTRACT(DAY FROM r.created) * 24 * 60 * 60) +
        //     (EXTRACT(HOUR FROM r.created) * 60 * 60) +
        //     (EXTRACT(MINUTE FROM r.created) * 60) +
        //     EXTRACT(SECOND FROM r.created)
        //    ),
        //     ((EXTRACT(YEAR FROM r.updated) * 365 * 24 * 60 * 60) +
        //     (EXTRACT(MONTH FROM r.updated) * 30 * 24 * 60 * 60) +
        //     (EXTRACT(DAY FROM r.updated) * 24 * 60 * 60) +
        //     (EXTRACT(HOUR FROM r.updated) * 60 * 60) +
        //     (EXTRACT(MINUTE FROM r.updated ) * 60) +
        //     EXTRACT(SECOND FROM r.updated)
        //     )) AS latest
        // FROM product_reviews r
        //
        // LEFT JOIN review_likes l ON r.rvw_id = l.fk_rvw_id
        // LEFT JOIN review_recommends rc ON r.rvw_id = rc.fk_rvw_id
        // LEFT JOIN review_delivery d ON r.rvw_id = d.fk_rvw_id
        // LEFT JOIN review_image rimg ON r.rvw_id = rimg.fk_rvw_id
        // LEFT JOIN users u ON r.fk_user_id = u.user_id
        // LEFT JOIN user_address ua ON u.user_id = ua.fk_user_id
        // LEFT JOIN "user_image" ui ON u.user_id = ui.fk_user_id
        //
        // LEFT JOIN products pr ON r.fk_pro_id = pr.pro_id
        // LEFT JOIN product_data p ON p.fk_pro_id = pr.pro_id
        // -- LEFT JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        // -- LEFT JOIN product_color_texture tx ON p.clr_pvt_id = tx.fk_clr_pvt_id
        // -- LEFT JOIN product_color_tags tg ON p.clr_pvt_id = tg.fk_clr_pvt_id
        //
        // WHERE r.active = :active
        // ORDER BY r.rvw_id ASC';

        $active = 1;
        $parameters[] = $active;
        $type[] = Type::INTEGER;

        $c = $this->getEntityManager()->getConnection();
        // $reviewOnly = $c->executeQuery($sql, $parameters, $type)
        //                 ->fetchAllAssociative();

        // foreach ($reviewOnly as $key => $value)
        // {
        //   $id['reviewId'][] =  $value['id'];
        //   $id['userId'][] =  $value['userId'];
        //   $id['productId'][] =  $value['productId'];
        // }

        $type = [];
        $parameters = [];

        // $parameters = [
        //   'product' => $id['productId'],
        //   'users' => $id['userId'],
        //   'status' => Order::STATUS_PAID,
        //   'active' => true
        // ];
        //
        // $type = [
        //   'product' => ArrayType::INTEGER,
        //   'users' => ArrayType::INTEGER,
        //   'status' => Type::STRING,
        //   'active' => Type::STRING
        // ];

        $sql =
          'SELECT DISTINCT ON(pc.clr_pvt_id, u.user_id)
          r.rvw_id AS id, r.active, r.headline, r.comment,
          l.vote_like AS like,  r.rating AS rating, r.fit AS fit,
          r.width AS width, r.comfort AS comfort, rc.recommend AS recommend,
          d.delivery AS delivery,
          rimg.image AS image,
          rimg.created AS pic_created,
          r.created AS created,
          r.updated AS updated,
          pr.pro_id AS "productId",
          u.user_id AS "userId",
          u.title AS title,
          u.email AS email,
          u.first_name AS "firstName",
          u.last_name AS "lastName",
          ua.country AS country,
          ui.image AS pic,
          pr.pro_id AS "productId",
          pr.name AS name,
          pr.brand AS brand,
          pr.category AS category,
          pr.occasion AS occasion,
          pr.type AS type,
          p.pro_pvt_id AS "productDataId",
          pc.clr_pvt_id AS "colorId",
          pc.color AS color,
          pc.image_md AS "imageMedium",
          u.user_id AS "userId",
          greatest(
           ((EXTRACT(YEAR FROM r.created) * 365 * 24 * 60 * 60) +
            (EXTRACT(MONTH FROM r.created) * 30 * 24 * 60 * 60) +
            (EXTRACT(DAY FROM r.created) * 24 * 60 * 60) +
            (EXTRACT(HOUR FROM r.created) * 60 * 60) +
            (EXTRACT(MINUTE FROM r.created) * 60) +
            EXTRACT(SECOND FROM r.created)
           ),
            ((EXTRACT(YEAR FROM r.updated) * 365 * 24 * 60 * 60) +
            (EXTRACT(MONTH FROM r.updated) * 30 * 24 * 60 * 60) +
            (EXTRACT(DAY FROM r.updated) * 24 * 60 * 60) +
            (EXTRACT(HOUR FROM r.updated) * 60 * 60) +
            (EXTRACT(MINUTE FROM r.updated ) * 60) +
            EXTRACT(SECOND FROM r.updated)
            )) AS latest

        FROM product_reviews r

        LEFT JOIN review_likes l ON r.rvw_id = l.fk_rvw_id
        LEFT JOIN review_recommends rc ON r.rvw_id = rc.fk_rvw_id
        LEFT JOIN review_delivery d ON r.rvw_id = d.fk_rvw_id
        LEFT JOIN review_image rimg ON r.rvw_id = rimg.fk_rvw_id
        LEFT JOIN users u ON r.fk_user_id = u.user_id
        LEFT JOIN user_address ua ON u.user_id = ua.fk_user_id
        LEFT JOIN "user_image" ui ON u.user_id = ui.fk_user_id
        LEFT JOIN review_data rd ON r.rvw_id = rd.fk_rvw_id

        LEFT JOIN product_data p ON p.pro_pvt_id = rd.fk_pro_id
        LEFT JOIN products pr ON pr.pro_id = r.fk_pro_id
        LEFT JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id

        WHERE r.active = :status

        GROUP BY
          r.rvw_id, r.active, r.headline, r.comment, l.vote_like,  r.rating,
          r.fit, r.width, r.comfort, rc.recommend, d.delivery, rimg.image,
          rimg.created, r.created, r.updated, pr.pro_id, u.user_id, u.title,
          u.email, u.first_name, u.last_name, ua.country, ui.image, pr.pro_id,
          pr.name, pr.brand, pr.category, pr.occasion, pr.type, p.pro_pvt_id,
          pc.clr_pvt_id, pc.color, pc.image_md, u.user_id, latest
      ';

        // $products = $c->executeQuery($sql, $parameters,$type)
        //               ->fetchAllAssociative();

        // dd($sql, $parameters,$type,  $products);
        // dd( $reviewOnly, $products);
        // dd( $products);
        // return ['a' => $reviewOnly,  'b' => $products];
        return [];

        return $products;

        // $sql = 'SELECT
        //    r.rvw_id as reviewId,
        //    r.headline, r.comment,
        //    l.vote_like,
        //    r.rating,
        //    r.fit, r.width, r.comfort,
        //    rc.recommend,
        //    d.delivery,
        //    r.created, r.updated,
        //    pr.pro_id AS productId,
        //    u.user_id as userId, u.first_name as "firstName",
        //    u.last_name as "lastName"
        //
        //  FROM product_reviews r
        //
        //  LEFT JOIN products pr ON r.fk_pro_id = pr.pro_id
        //  LEFT JOIN users u ON r.fk_user_id = u.user_id
        //  LEFT JOIN review_likes l ON r.rvw_id = l.fk_rvw_id
        //  LEFT JOIN review_recommends rc ON r.rvw_id = rc.fk_rvw_id
        //  LEFT JOIN review_delivery d ON r.rvw_id = d.fk_rvw_id
        //
        //  WHERE r.active = :active
        //  ';

        // $map = new Map();
        // $map->addScalarResult('word', 'word');
        // $map->addScalarResult('nentry', 'nentry', 'integer');
        // $map->addScalarResult('ndoc', 'ndoc', 'integer');
        // $limit = 30;
        //
        // $query = $this->getEntityManager()
        //               ->createNativeQuery($sql, $map);

        $active = 1;
        $parameters[] = $active;
        $type[] = Type::INTEGER;

        $c = $this->getEntityManager()->getConnection();
        $bestReview = $c->executeQuery($sql, $parameters, $type)
                        ->fetchAllAssociative();

        return $bestReview;
    }

    /*
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $page_size The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    // public function paginate($dql, $page = 1, $page_size = 5)
    // {
    //     $paginator = new Paginator($dql);
    //     $reached = (int) (($page * $page_size ) - $page_size);
    //
    //     $paginator
    //       ->getQuery()
    //       // ->setFirstResult($page_size * ($page - 1))
    //       ->setFirstResult($reached)       // Offset
    //       ->setMaxResults($page_size);     // Limit
    //
    //     return $paginator;
    // }

    // /**
    //  * @return Review[] Returns an array of Review objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // $query
    //   ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, Sortable::class)
    //   ->setHint('SortableNullsWalker.fields', [
    //       'r.updated' => Sortable::NULLS_LAST,
    //       'r.created' => Sortable::NULLS_LAST,
    //   ]);
}
