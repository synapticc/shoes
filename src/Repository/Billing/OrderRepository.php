<?php

// src/Repository/Billing/OrderRepository.php

namespace App\Repository\Billing;

use App\Entity\Billing\Order;
use App\Entity\Billing\OrderItem;
use App\Entity\NoMap\Search\Search;
use App\Entity\NoMap\Transfer\Billing\OrderTransfer;
use App\Service\AttributeService;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType as Type;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Minwork\Helper\Arr;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private AttributeService $a)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Order $entity, bool $flush = true): void
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
    public function remove(Order $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function cartTransfer($userId)
    {
        $result = $this->createQueryBuilder('o')
        ->select(sprintf(
            'NEW %s(  o.order_id,
                    o.status,
                    o.userAgent,
                    o.activeStatus
                    )',
            OrderTransfer::class
        ))
        ->leftJoin('o.users', 'u')
        ->andWhere('o.users = :user')
        ->setParameter('user', $userId)
        ->groupBy('o.order_id')
        ->getQuery()
        ->getResult()
        ;

        return $result;
    }

    /**
     * @return Order[] Returns an array of Order arranged in ascending order of Order Items
     */
    public function findByMaxOrderItems($userId)
    {
        $query = $this->createQueryBuilder('o')
            ->andWhere('o.users = :val')
            ->setParameter('val', $userId)
            ->andWhere('o.status = :status')
            ->setParameter('status', 'cart')
            ->innerJoin(
                OrderItem::class,    // Entity
                'i',               // Alias
                Join::WITH,        // Join type
                'i.orderRef = o.order_id', // Join columns
            )
            ->orderBy('i.quantity', 'DESC')
        ;

        return $query->getQuery()->getResult();
    }

    public function findCart($userId)
    {
        $query = $this->createQueryBuilder('o')
            ->andWhere('o.users = :user')
            ->setParameter('user', $userId)
            ->andWhere('o.status = :status')
            ->setParameter('status', 'cart')
            ->innerJoin(
                OrderItem::class,    // Entity
                'i',               // Alias
                Join::WITH,        // Join type
                'i.orderRef = o.order_id', // Join columns
            )
            ->orderBy('o.activeStatus', 'DESC')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Finds carts which hasn't been updated for given number of days.
     *
     * @return Order[] Returns an array of expired Order
     */
    public function findExpiredCart(\DateTime $dateLimit, int $resultLimit)
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('pc.updated < :updated')
            ->setParameter('updated', $dateLimit)
            ->andWhere('pc.status = :status')
            ->setParameter('status', Order::STATUS_CART)
            ->setMaxResults($resultLimit)
        ;

        return $query->getQuery()->getResult();
    }

    public function fetchCart(int $user)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
          o.order_id as id, o.status,
          o.activeStatus, o.userAgent,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall,
          o.created, o.updated,
          i.created AS HIDDEN i_created,
          i.updated AS HIDDEN i_updated,
          CASE WHEN i.updated IS NULL THEN 1
          ELSE 0 END AS HIDDEN updated_null

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc

         WHERE o.users = :user
         AND o.status = :status

         ORDER BY o.activeStatus DESC, updated_null ASC, i.updated DESC';

        $query = $em->createQuery($dql);
        $query->setParameter('user', $user)
              ->setParameter('status', Order::STATUS_CART);

        $initial = $query->getResult();
        if (empty($initial)) {
            return [];
        }

        $result = [];
        $orderKeys = ['id', 'status', 'activeStatus', 'userAgent', 'created',
            'updated'];
        $itemKeys = ['itemId', 'productDataId', 'productId', 'quantity', 'name',
            'brand', 'category', 'occasion', 'type', 'sellingPrice',
            'qtyInStock', 'size', 'sku', 'colorId', 'color', 'fabrics',
            'imageSmall', 'subtotal', 'i_created', 'i_updated'];

        foreach ($initial as $i => $order) {
            foreach ($order as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $result[$order['id']][$key] = $order[$key];
                }
            }

            if (!empty($order['itemId'])) {
                foreach ($order as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$order['id']][$order['itemId']][$key] = $order[$key];
                    }
                }
            }

            if (!empty($order['itemId'])) {
                $result[$order['id']]['items'] = $items[$order['id']];
            }
        }

        foreach ($result as $i => $cart) {
            $totalSet = $this->total($cart['id']);
            $result[$i] = array_merge($result[$i], $totalSet);
            $result[$i]['updated'] = date_timestamp_get($result[$i]['updated']);

            if ($result[$i]['totalItems'] > 0) {
                foreach ($cart['items'] as $j => $item) {
                    $result[$i]['items'][$j] = $this->$a->enrichProduct($item);
                }
            }
        }

        return $result;
    }

    public function purchases(int $user)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT
          o.order_id as id,
          b.purchaseDate, b.invoiceTotal,
          o.activeStatus,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.brand,
          p.sellingPrice,
          p.sellingPrice * i.quantity AS subtotal,
          pc.imageSmall

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.color pc
         LEFT JOIN p.product pr
         LEFT JOIN o.billing b

         WHERE o.users = :user
         AND o.status = :status

         ORDER BY b.purchaseDate DESC, pr.name ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('user', $user)
              ->setParameter('status', Order::STATUS_PAID);

        $initial = $query->getResult();

        if (empty($initial)) {
            return [];
        }

        foreach ($initial as $i => $item) {
            $initial[$i] = $this->$a->enrichProduct($item);
        }

        $orderKeys = ['id', 'user', 'updated', 'invoiceTotal',  'purchaseDate'];
        $itemKeys = ['itemId', 'productDataId', 'quantity', 'name', 'imageSmall',
            'brand_full', 'subtotal'];

        $result = [];
        foreach ($initial as $i => $order) {
            foreach ($order as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $orders[$order['id']][$key] = $order[$key];
                }
            }

            if (!empty($order['itemId'])) {
                foreach ($order as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$order['id']][$order['productDataId']][$key] = $order[$key];
                    }
                }
            }

            $result[$order['id']] = $orders[$order['id']];
            $result[$order['id']]['totalQty'] = $this->totalQty($order['id']);

            if (!empty($order['itemId'])) {
                $result[$order['id']]['items'] = $items[$order['id']];
            }
        }

        return $result;
    }

    public function purchasesFull($q  /* Query */)
    {
        $em = $this->getEntityManager();
        $status = Order::STATUS_PAID;
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'b.purchaseDate';
        $user = $q->has('customer') ? $q->get('customer') : '';

        $dql = 'SELECT
          o.order_id as id, o.status,
          b.purchaseDate, b.invoiceTotal,
          o.activeStatus, o.userAgent,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall,
          u.id as userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic,
          o.created, o.updated,
          i.created AS HIDDEN i_created,
          i.updated AS HIDDEN i_updated

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN o.billing b
         LEFT JOIN b.billingDelivery d

         WHERE o.status = :status
         ';

        if (!empty($user)) {
            $dql .= ' AND u.id = :user ';
        }

        if ('items' != $sort) {
            $dql .= " ORDER BY $sort $order ";
        }

        $query = $em->createQuery($dql);
        $query->setParameter('status', $status);
        if (!empty($user)) {
            $query->setParameter('user', $user);
        }

        $initial = $query->getResult();

        $result = [];

        $orderKeys = ['id', 'userId', 'email', 'title', 'firstName', 'lastName',
            'pic', 'status', 'purchaseDate', 'invoiceTotal',
            'activeStatus', 'userAgent', 'invoiceTotal', 'created',
            'updated'];

        $itemKeys = ['itemId', 'productDataId', 'productId', 'quantity', 'name',
            'brand', 'category', 'occasion', 'type', 'sellingPrice',
            'qtyInStock', 'size', 'sku', 'colorId', 'color', 'fabrics',
            'imageSmall', 'subtotal', 'i_created', 'i_updated'];

        $result = [];

        foreach ($initial as $i => $product) {
            $product['updated'] = date_timestamp_get($product['updated']);

            foreach ($product as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $orders[$product['id']][$key] = $product[$key];
                }
            }

            if (!empty($product['itemId'])) {
                foreach ($product as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$product['id']][$product['productDataId']][$key] = $product[$key];
                    }
                }
            }

            $total[$product['id']][$i] = (int) $product['sellingPrice'] * $product['quantity'];

            $totalQty[$product['id']][$i] = (int) $product['quantity'];

            $result[$product['id']] = $orders[$product['id']];
            if (!empty($product['itemId'])) {
                $result[$product['id']]['items'] = $items[$product['id']];
            }
        }

        if (!empty($initial)) {
            foreach ($total as $i => $value) {
                $total[$i] = array_sum($total[$i]);
                $result[$i]['total'] = $total[$i];
            }

            foreach ($totalQty as $i => $value) {
                $totalQty[$i] = array_sum($totalQty[$i]);
                $result[$i]['totalQty'] = $totalQty[$i];
            }

            $result = array_values($result);

            foreach ($result as $i => $cart) {
                if ($cart['total'] > 0) {
                    foreach ($cart['items'] as $j => $item) {
                        $result[$i]['items'][$j] = $this->$a->enrichProduct($item);
                    }
                }
            }

            if (!empty('items' == $sort)) {
                $result = Arr::sortByKeys($result, 'items');
                if ('DESC' == $order) {
                    $result = array_reverse($result);
                }
            }
        }

        return $result;
    }

    public function search(Search $search, $q): array
    {
        $em = $this->getEntityManager();
        $status = Order::STATUS_PAID;
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'b.purchaseDate';

        // Remove special characters except space
        $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
        // Remove unnecessary space
        $keywords = preg_replace('/\s\s+/', ' ', $keywords);
        // Strip whitespace (or other characters) from the beginning and end of a string
        $keywords = trim((string) $keywords, "\x00.. \x1F");
        // Arrange all words in an array
        $keywords = explode(' ', $keywords);

        $condition = ' o.status = :status AND (';

        foreach ($keywords as $i => $word) {
            if (end($keywords) == $word) {
                $condition .= "
              (ILIKE(u.email, :word$i) = true) OR
              (ILIKE(u.firstName, :word$i) = true) OR
              (ILIKE(u.lastName, :word$i) = true) OR

              (ILIKE(pr.name, :word$i) = true) OR
              (ILIKE(pr.description, :word$i) = true) OR
              (ILIKE(pc.color, :word$i) = true)
              )
              ";
            } else {
                $condition .= "
              (ILIKE(u.email, :word$i) = true) OR
              (ILIKE(u.firstName, :word$i) = true) OR
              (ILIKE(u.lastName, :word$i) = true) OR

              (ILIKE(pr.name, :word$i) = true) OR
              (ILIKE(pr.description, :word$i) = true) OR
              (ILIKE(pc.color, :word$i) = true) OR
              ";
            }
        }

        if (!empty($search->startDate()) and !empty($search->startDate())) {
            $condition .= ' AND b.purchaseDate BETWEEN :startDate AND :endDate ';
        }

        $dql = 'SELECT
          o.order_id as id, o.status,
          b.purchaseDate, b.invoiceTotal,
          o.activeStatus, o.userAgent,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall,
          u.id as userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic,
          o.created, o.updated,
          i.created AS HIDDEN i_created,
          i.updated AS HIDDEN i_updated

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN o.billing b
         LEFT JOIN b.billingDelivery d

         WHERE '.$condition;

        if ('items' != $sort and 'total' != $sort) {
            $dql .= " ORDER BY $sort $order ";
        }

        $query = $em->createQuery($dql);
        $query->setParameter('status', $status);

        if (!empty($search->startDate()) and !empty($search->startDate())) {
            $query
              ->setParameter('startDate', $search->startDate()->format('Y-m-d H:i:s'))
              ->setParameter('endDate', $search->endDate()->format('Y-m-d H:i:s'));
        }

        foreach ($keywords as $i => $word) {
            $query->setParameter("word$i", '%'.strtolower($word).'%');
        }

        $initial = $query->getResult();
        $result = [];

        $orderKeys = ['id', 'userId', 'email', 'title', 'firstName', 'lastName',
            'pic', 'status', 'purchaseDate', 'invoiceTotal',
            'activeStatus', 'userAgent', 'invoiceTotal', 'created',
            'updated'];

        $itemKeys = ['itemId', 'productDataId', 'productId', 'quantity', 'name',
            'brand', 'category', 'occasion', 'type', 'sellingPrice',
            'qtyInStock', 'size', 'sku', 'colorId', 'color', 'fabrics',
            'imageSmall', 'subtotal', 'i_created', 'i_updated'];

        $result = [];

        foreach ($initial as $i => $product) {
            $product['updated'] = date_timestamp_get($product['updated']);

            foreach ($product as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $orders[$product['id']][$key] = $product[$key];
                }
            }

            if (!empty($product['itemId'])) {
                foreach ($product as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$product['id']][$product['productDataId']][$key] = $product[$key];
                    }
                }
            }

            $total[$product['id']][$i] = (int) $product['sellingPrice'] * $product['quantity'];

            $totalQty[$product['id']][$i] = (int) $product['quantity'];

            $result[$product['id']] = $orders[$product['id']];
            if (!empty($product['itemId'])) {
                $result[$product['id']]['items'] = $items[$product['id']];
            }
        }

        if (!empty($initial)) {
            foreach ($total as $i => $value) {
                $total[$i] = array_sum($total[$i]);
                $result[$i]['total'] = $total[$i];
            }

            foreach ($totalQty as $i => $value) {
                $totalQty[$i] = array_sum($totalQty[$i]);
                $result[$i]['totalQty'] = $totalQty[$i];
            }

            $result = array_values($result);

            foreach ($result as $i => $cart) {
                if ($cart['total'] > 0) {
                    foreach ($cart['items'] as $j => $item) {
                        $result[$i]['items'][$j] = $this->$a->enrichProduct($item);
                    }
                }
            }

            switch ($sort) {
                case 'items':
                    $result = Arr::sortByKeys($result, 'items', false);
                    if ('DESC' == $order) {
                        $result = array_reverse($result);
                    }

                    break;
                case 'total':
                    $result = Arr::sortByKeys($result, 'total', false);
                    if ('DESC' == $order) {
                        $result = array_reverse($result);
                    }

                    // no break
                default:
                    break;
            }
        }

        return $result;
    }

    public function cartList(int $cart, int $user)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT
          o.order_id as id, o.status,
          b.purchaseDate, b.invoiceTotal,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId, pc.color, pc.fabric,
          pc.imageSmall,
          o.created, o.updated,
          i.created AS HIDDEN i_created,
          i.updated AS HIDDEN i_updated,
          CASE WHEN i.updated IS NULL THEN 1
          ELSE 0 END AS HIDDEN updated_null

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN o.billing b
         LEFT JOIN b.billingDelivery d

         WHERE o.users = :user
         AND o.order_id = :cart
         AND o.status = :status

        ORDER BY updated_null ASC, i.updated DESC';

        $query = $em->createQuery($dql);

        $query->setParameter('user', $user)
              ->setParameter('cart', $cart)
              ->setParameter('status', Order::STATUS_CART);

        $initial = $query->getResult();

        if (empty($initial)) {
            return [];
        }

        $result = [];

        foreach ($initial as $i => $item) {
            $initial[$i] = $this->$a->enrichProduct($item);
        }

        $orderKeys = ['id', 'status', 'purchaseDate', 'invoiceTotal',
            'activeStatus', 'created', 'updated'];

        $itemKeys = ['itemId', 'productDataId', 'productId', 'quantity', 'name',
            'brand_full', 'category_full', 'occasion_full', 'type_full', 'sellingPrice',
            'qtyInStock', 'size', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
            'fabrics', 'fabrics_full', 'fabrics_full_set',
            'imageSmall', 'subtotal', 'i_created', 'i_updated'];

        foreach ($initial as $i => $order) {
            foreach ($order as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $result[$key] = $order[$key];
                }
            }

            if (!empty($order['itemId'])) {
                foreach ($order as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$order['itemId']][$key] = $order[$key];
                    }
                }
            }

            if (!empty($order['itemId'])) {
                $result['items'] = $items;
            }
        }

        $totalSet = $this->total($cart);
        $result = array_merge($result, $totalSet);

        /* Convert DateTime to Timestamps */
        if (!empty($result['updated'])) {
            $result['updated'] = date_timestamp_get($result['updated']);
        }

        return $result;
    }

    public function cart(int $cart)
    {
        $sql =
        'SELECT distinct on (i.updated, i.created)
        o.ord_id as id,
        o.updated as "updated",
        i.ord_itm_id as "itemId", i.quantity,
        p.pro_pvt_id as "productDataId",
        pr.name as "name",  pr.brand as "brand",
        p.size,
        p.selling_price as "sellingPrice",
        p.selling_price * i.quantity as subtotal,
        pc.image_sm as "imageSmall"

       FROM "orders" o

       LEFT JOIN users u ON o.fk_user_id = u.user_id
       LEFT JOIN order_item i ON o.ord_id = i.fk_ord_id
       LEFT JOIN product_data p ON i.fk_pro_id = p.pro_pvt_id
       LEFT JOIN products pr ON p.fk_pro_id = pr.pro_id
       LEFT JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id

       WHERE o.ord_id = :cart
       AND o.status = :status

       ORDER BY i.updated DESC NULLS LAST
       LIMIT :max
       ';

        $parameters['cart'] = $cart;
        $type['cart'] = Type::INTEGER;

        $status = Order::STATUS_CART;
        $parameters['status'] = $status;
        $type['status'] = Type::STRING;

        $max = 2;
        $parameters['max'] = $max;
        $type['max'] = Type::INTEGER;

        $c = $this->getEntityManager()->getConnection();
        $initial = $c->executeQuery($sql, $parameters, $type)
                             ->fetchAllAssociative();

        if (empty($initial)) {
            return [];
        }

        foreach ($initial as $i => $item) {
            $initial[$i]['brand_full'] = $this->name($item['brand']);
        }

        $result = [];
        $orderKeys = ['id', 'updated'];
        $itemKeys = ['itemId', 'productDataId', 'quantity', 'name',
            'brand_full', 'sellingPrice',
            'size', 'imageSmall', 'subtotal'];

        foreach ($initial as $i => $order) {
            foreach ($order as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $result[$key] = $order[$key];
                }
            }

            if (!empty($order['itemId'])) {
                foreach ($order as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$order['productDataId']][$key] = $order[$key];
                    }
                }
            }

            if (!empty($order['itemId'])) {
                $result['items'] = $items;
            }
        }

        $totalSet = $this->total($cart);
        $result = array_merge($result, $totalSet);

        /* Convert DateTime to Timestamps */
        if (!empty($result['updated'])) {
            $result['updated'] = strtotime($result['updated']);
        }

        return $result;
    }

    public function purchase(int $purchase)
    {
        $sql =
        'SELECT distinct on (i.updated, i.created)
        o.ord_id as id,
        o.updated as "updated",
        i.ord_itm_id as "itemId", i.quantity,
        p.pro_pvt_id as "productDataId",
        pr.name as "name",  pr.brand, pr.category, pr.type,
        p.size,
        p.selling_price as "sellingPrice",
        p.selling_price * i.quantity as subtotal,
        pc.image_sm as "imageSmall",
        u.user_id as "user", pc.color, pc.fabric,
        b.invoice_total as "invoiceTotal"

       FROM "orders" o

       INNER JOIN users u ON o.fk_user_id = u.user_id
       INNER JOIN billing b ON b.fk_ord_id = o.ord_id
       INNER JOIN order_item i ON o.ord_id = i.fk_ord_id
       INNER JOIN product_data p ON i.fk_pro_id = p.pro_pvt_id
       INNER JOIN products pr ON p.fk_pro_id = pr.pro_id
       INNER JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id

       WHERE o.ord_id = :cart AND o.status = :status
       ORDER BY i.updated DESC NULLS LAST
       ';

        $parameters[] = $purchase;
        $type[] = Type::INTEGER;  // $purchase

        $status = Order::STATUS_PAID;
        $parameters[] = $status;
        $type[] = Type::STRING;  // $status

        $c = $this->getEntityManager()->getConnection();
        $initial = $c->executeQuery($sql, $parameters, $type)
                           ->fetchAllAssociative();

        if (empty($initial)) {
            return [];
        }

        foreach ($initial as $i => $item) {
            $initial[$i] = $this->$a->enrichProduct($item);
        }

        $result = [];
        $orderKeys = ['id', 'user', 'updated', 'invoiceTotal'];
        $itemKeys = ['itemId', 'productDataId', 'quantity', 'name',
            'color', 'colors_set', 'colors_full_set', 'colors_full',
            'fabrics', 'fabrics_full', 'fabrics_full_set',
            'brand_full', 'category_full', 'type_full', 'sellingPrice',
            'size', 'imageSmall', 'subtotal'];

        foreach ($initial as $i => $order) {
            foreach ($order as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $result[$key] = $order[$key];
                }
            }

            if (!empty($order['itemId'])) {
                foreach ($order as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$order['itemId']][$key] = $order[$key];
                    }
                }
            }

            if (!empty($order['itemId'])) {
                $result['items'] = $items;
            }
        }

        $totalSet = $this->total($purchase);
        $result = array_merge($result, $totalSet);

        /* Convert DateTime to Timestamps */
        if (!empty($result['updated'])) {
            $result['updated'] = strtotime($result['updated']);
        }

        return $result;
    }

    public function info(int $purchase)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT
          o.order_id as id, o.status,
          inv.purchaseDate, inv.invoiceTotal,
          o.activeStatus, o.userAgent,
          u.id as userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic,
          inv.invoicePath,
          o.created, o.updated

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.users u
         LEFT JOIN o.billing inv
         LEFT JOIN u.userImage ui
         WHERE o.order_id = :purchase
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('purchase', $purchase);
        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function total(int $cart)
    {
        $sql =
          'SELECT SUM(a.total) as "total", SUM(a.quantity) as "totalQty",
         COUNT(DISTINCT a.ord_itm_id) As "totalItems"
         FROM
           ( SELECT SUM(p.selling_price * i.quantity) AS total,
             i.quantity, i.ord_itm_id
             FROM "orders" o
             LEFT JOIN order_item i ON o.ord_id = i.fk_ord_id
             LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
             WHERE o.ord_id = :cart
             GROUP BY p.selling_price, i.quantity, i.ord_itm_id
            ) a
      ';

        $parameters = [];
        $type = [];
        $total = [];
        if (!empty($cart)) {
            $parameters = [$cart];
            $type = [Type::INTEGER];
        }
        $c = $this->getEntityManager()->getConnection();

        $total = $c->executeQuery($sql, $parameters, $type)
                   ->fetchAllAssociative();

        if (!empty($total)) {
            $total = $total[0];
        }

        return $total;
    }

    public function totalQty(int $purchase)
    {
        $em = $this->getEntityManager();
        $dql = '
        SELECT SUM(i.quantity) AS total_quantity
        FROM App\Entity\Billing\Order o
        LEFT JOIN o.items i
        WHERE o.order_id = :purchase
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('purchase', $purchase);
        $result = $query->getSingleScalarResult();

        return $result;
    }

    public function totalItems(int $cart)
    {
        $em = $this->getEntityManager();
        $dql = '
        SELECT COUNT(i) AS total_items
        FROM App\Entity\Billing\Order o
        LEFT JOIN o.items i
        WHERE o.order_id = :cart
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('cart', $cart);
        $result = $query->getSingleScalarResult();

        return $result;
    }

    public function currentCart()
    {
        $em = $this->getEntityManager();
        $dql = '
          SELECT o.order_id AS id
          FROM App\Entity\Billing\Order o
          WHERE o.status = :status
            AND (
              o.activeStatus = true  -- try active first
              OR NOT EXISTS (
                SELECT 1
                FROM App\Entity\Billing\Order o2
                WHERE o2.status = :status
                  AND o2.activeStatus = false
              )
            )';

        $status = Order::STATUS_CART;
        $active = true;
        $query = $em->createQuery($dql);
        $query->setParameter('status', $status);

        $result = $query->getSingleScalarResult();

        return $result;
    }

    public function productTotal(int $product)
    {
        $sql =
          'SELECT SUM(a.total) as "total", SUM(a.quantity) as "totalQty",
         COUNT(DISTINCT a.ord_itm_id) As "totalItems"
         FROM
           ( SELECT SUM(p.selling_price * i.quantity) AS total,
             i.quantity, i.ord_itm_id
             FROM "orders" o
             LEFT JOIN order_item i ON o.ord_id = i.fk_ord_id
             LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
             LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
             WHERE pr.pro_id = :product
             GROUP BY p.selling_price, i.quantity, i.ord_itm_id
            ) a
        ';
        $parameters = [];
        $type = [];
        $total = [];
        if (!empty($product)) {
            $parameters[] = $product;
            $type[] = Type::INTEGER;
        }
        $c = $this->getEntityManager()->getConnection();
        $total = $c->executeQuery($sql, $parameters, $type)
                   ->fetchAllAssociative();

        $sql =
        'SELECT SUM(b.total) as "total", SUM(b.quantity) as "totalQty",
       COUNT(DISTINCT b.ord_itm_id) As "totalItems"
       FROM
         ( SELECT SUM(p.selling_price * i.quantity) AS total,
           i.quantity, i.ord_itm_id
           FROM "orders" o
           LEFT JOIN order_item i ON o.ord_id = i.fk_ord_id
           LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
           LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
           WHERE pr.pro_id = :product
           AND o.status = :cart
           GROUP BY p.selling_price, i.quantity, i.ord_itm_id
         ) b
      ';

        $parameters = [];
        $type = [];
        $totalCart = [];
        $cart = 'cart';
        if (!empty($cart)) {
            $parameters[] = $product;
            $type[] = Type::INTEGER;
            $parameters[] = $cart;
            $type[] = Type::STRING;
        }
        $c = $this->getEntityManager()->getConnection();
        $totalCart = $c->executeQuery($sql, $parameters, $type)
                   ->fetchAllAssociative();

        $sql =
        'SELECT SUM(c.total) as "total", SUM(c.quantity) as "totalQty",
       COUNT(DISTINCT c.ord_itm_id) As "totalItems"
       FROM
         ( SELECT SUM(p.selling_price * i.quantity) AS total,
           i.quantity, i.ord_itm_id
           FROM "orders" o
           LEFT JOIN order_item i ON o.ord_id = i.fk_ord_id
           LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
           LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
           WHERE pr.pro_id = :product
           AND o.status = :paid
           GROUP BY p.selling_price, i.quantity, i.ord_itm_id
         ) c
      ';
        $parameters = [];
        $type = [];
        $totalPaid = [];
        $paid = Order::STATUS_PAID;
        if (!empty($paid)) {
            $parameters[] = $product;
            $type[] = Type::INTEGER;
            $parameters[] = $paid;
            $type[] = Type::STRING;
        }
        $c = $this->getEntityManager()->getConnection();

        $totalPaid = $c->executeQuery($sql, $parameters, $type)
                   ->fetchAllAssociative();

        return ['total' => $total[0],
            'totalCart' => $totalCart[0],
            'totalPaid' => $totalPaid[0]];
    }

    public function latest($cart)
    {
        $em = $this->getEntityManager();
        $dql =
          'SELECT o.updated FROM App\Entity\Billing\Order o
           WHERE o.order_id = :cart';

        $query = $em->createQuery($dql);
        $query->setParameter('cart', $cart);
        $initial = $query->getResult();
        if (!empty($initial)) {
            $latestDate = $initial[0]['updated'];
            $timestamps = date_timestamp_get($latestDate);

            return $timestamps;
        }

        return '';
    }

    public function invoice($purchase)
    {
        $em = $this->getEntityManager();
        $status = Order::STATUS_PAID;
        $dql =
        'SELECT
          o.order_id as id, o.status,
          b.purchaseDate, b.invoiceTotal,
          i.item_id AS itemId, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId, pc.color, pc.fabric,
          pc.imageMedium,
          u.id as userId, u.email, u.title,
          u.firstName, u.middleName, u.lastName,
          b.street, b.city, b.country, b.zip, d.deliveryNotes,
          b.cardNumber, b.cardHolder

         FROM App\Entity\Billing\Order o
         LEFT JOIN o.items i
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN o.billing b
         LEFT JOIN b.billingDelivery d

         WHERE o.status = :status AND o.order_id = :purchase
         ORDER BY pr.name, pc.color
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('status', $status)
              ->setParameter('purchase', $purchase);

        $initial = $query->getResult();

        if (empty($initial)) {
            return [];
        }

        foreach ($initial as $i => $item) {
            $initial[$i] = $this->$a->enrichProduct($item);
            $initial[$i]['colors_full'] = str_replace(' | ', '<br>', $initial[$i]['colors_full']);
        }

        $result = [];
        $orderKeys = ['id', 'userId', 'email', 'title', 'firstName', 'middleName',
            'lastName', 'status', 'purchaseDate', 'invoiceTotal',
            'street', 'city', 'country', 'zip', 'deliveryNotes',
            'cardNumber', 'cardHolder'];

        $itemKeys = ['itemId', 'productDataId', 'productId', 'quantity', 'name',
            'brand', 'category', 'occasion', 'type', 'sellingPrice',
            'qtyInStock', 'size', 'sku',
            'imageMedium', 'subtotal', 'brand_full', 'category_full',
            'occasion_full', 'type_full', 'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
            'fabrics', 'fabrics_full', 'fabrics_full_set',
            'textures', 'textures_full', 'textures_full_set'];

        $result = [];
        foreach ($initial as $i => $product) {
            foreach ($product as $key => $value) {
                if (in_array($key, $orderKeys)) {
                    $orders[$product['id']][$key] = $product[$key];
                }
            }

            if (!empty($product['itemId'])) {
                foreach ($product as $key => $value) {
                    if (in_array($key, $itemKeys)) {
                        $items[$product['id']][$product['productDataId']][$key] = $product[$key];
                    }
                }
            }

            $result[$product['id']] = $orders[$product['id']];
            if (!empty($product['itemId'])) {
                $result[$product['id']]['items'] = $items[$product['id']];
            }
        }

        $result = array_values($result)[0];

        return $result;
    }

    /**
     * @return Order[] returns an array of Order: $activeCart
     *                 Detect if the user has two active carts and if so,
     *                 deactivate the latest one and keep only the oldest cart
     *                 since every user can have only one active cart
     */
    public function activeCart(int $user)
    {
        $em = $this->getEntityManager();
        $activeCart = [];
        $dql =
          'SELECT o as cart, o.order_id as id FROM App\Entity\Billing\Order o
           WHERE o.activeStatus = true
           AND o.users = :user
           GROUP BY o.order_id
           ORDER BY o.updated ASC, o.created ASC';

        $activeCount = $em->createQuery($dql)
                      ->setParameter('user', $user)
                      ->getResult();

        if (2 === count($activeCount)) {
            $activeCart = $activeCount[1]['cart'];
            $dql = "
            UPDATE App\Entity\Billing\Order o
            SET o.activeStatus = false,
                o.updated = :updated
            WHERE o.order_id = :id";

            $timezone = new \DateTimeZone('+04:00');
            $updated = new \DateTime('now', $timezone);

            $query = $em->createQuery($dql);
            $query->setParameter('updated', $updated)
                  ->setParameter('id', $activeCount[0]['id']);

            $updatedRow = $query->execute();
        }

        if (1 === count($activeCount)) {
            $activeCart = $activeCount[0]['cart'];
        }

        return $activeCart;
    }

    public function checkCart($cart_id)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT COUNT(o.order_id) FROM App\Entity\Billing\Order o WHERE o.order_id = :id';
        $exists = (bool) $em->createQuery($dql)
                  ->setParameter('id', $cart_id)
                  ->getSingleScalarResult();

        return $exists;
    }

    /**
     * This deletes all Expired Carts (Order) in one query
     * without loading them into memory.
     * An expired cart:
     * 1) Any cart unattached to any user and which hasn't been updated for 6 hours.
     * 1) Any cart unattached to any user and which has existed for more than 2 days.
     * 2) Any cart attached to a user and which has existed for more than 7 days.
     */
    public function deleteExpiredCart()
    {
        $em = $this->getEntityManager();
        $dql = "
        DELETE FROM App\Entity\Billing\Order o
        WHERE
        ( o.users IS NULL
          AND o.status = :status
          AND o.updated < :expiredTime)
        OR
        ( o.users IS NULL
          AND o.status = :status
          AND o.created < :expiredDay1)
        OR
        ( o.users IS NOT NULL
          AND o.status = :status
          AND o.created < :expiredDay2) ";

        /*
        Sets the expired time 6 hours before the current server time.
         P = "Period" (start of duration)
         T = "Time" (separates date and time parts)
         10M = 10 minutes
         6H => 6 hours
        */
        $expiredTime = new \DateTime();
        $expiredTime->sub(new \DateInterval('PT6H'));

        $expiredDay1 = new \DateTime('-7 days');
        $expiredDay2 = new \DateTime('-7 days');

        $query = $em->createQuery($dql);
        $query->setParameter('expiredTime', $expiredTime)
              ->setParameter('expiredDay1', $expiredDay1)
              ->setParameter('expiredDay2', $expiredDay2)
              ->setParameter('status', Order::STATUS_CART);
        $numDeleted = $query->execute();
    }
}
