<?php

// src/Repository/Billing/OrderItemRepository.php

namespace App\Repository\Billing;

use App\Controller\_Utils\Attributes;
use App\Entity\Billing\Order;
use App\Entity\Billing\OrderItem;
use App\Entity\NoMap\Search\Search;
use App\Entity\NoMap\Transfer\Billing\OrderItemTransfer;
use App\Entity\Product\Product\Product;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType as Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderItem[]    findAll()
 * @method OrderItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderItemRepository extends ServiceEntityRepository
{
    use Attributes;

    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $_em,
    ) {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(OrderItem $item, bool $flush = true): void
    {
        $this->_em->persist($item);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(OrderItem $item, bool $flush = true): void
    {
        $this->_em->remove($item);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function cartTransfer($userId)
    {
        $result = $this->createQueryBuilder('i')
        ->select(sprintf(
            'NEW %s(  o.order_id, i.item_id,
                    p.id, i.quantity,
                    pc.color,pc.fabric, pc
                  )',
            OrderItemTransfer::class
        ))
        ->leftJoin('i.product', 'p')
        ->leftJoin('p.product', 'pr')
        ->leftJoin('pr.productColor', 'pc')
        ->leftJoin('i.orderRef', 'o')
        ->leftJoin('o.users', 'u')
        ->andWhere('o.users = :user')
        ->setParameter('user', $userId)
        ->orderBy('i.updated', 'ASC')
        ->orderBy('i.created', 'DESC')
        // ->groupBy('o')
        ->getQuery()
        ->getResult()
        ;

        foreach ($result as $i => $item) {
            $results[$item->getProduct()] = $item;
        }

        return $results;
    }

    /**
     * @return ProductData[] Returns an array of ProductData objects
     */
    public function findByCart(array $filter)
    {
        if (!empty($filter['products'])) {
            $products = $filter['products'];

            foreach ($products as $i => $product) {
                $query = $this
                    ->createQueryBuilder('o')
                    ->andWhere('o.product IN (:product)')
                    ->setParameter('product', $product)
                    ->orderBy('o.order_id', 'DESC')
                ;
                $queries[$i] = $query->getQuery()->getResult();
            }

            $queries = array_merge(...$queries);

            return $queries;
        } elseif (empty($filter['products'])) {
            return null;
        }
    }

    /**
     * @return OrderItem[] returns an array of Order arrays
     *                     and NOT Order objects
     */
    public function fetch(int $item)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT
          o.order_id,
          i.item_id, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc


         WHERE i.item_id = :item
         ';
        $query = $entityManager->createQuery($dql);

        $query->setParameter('item', $item);
        $result = $this->fullName($query->getOneOrNullResult());

        return $result;
    }

    /**
     * @return OrderItem[] returns an array of Order arrays
     *                     and NOT Order objects
     */
    public function items(int $purchase, $q)
    {
        $order = $q->has('order') ? $q->get('order') : 'ASC';
        $sort = $q->has('sort') ? $q->get('sort') : 'pr.name, pc.color';

        $em = $this->getEntityManager();
        $dql =
        'SELECT
          o.order_id,
          i.item_id, i.quantity,
          p.id AS productDataId,
          pr.id  AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc


         WHERE o.order_id = :purchase
         ';

        $dql .= " ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        $query->setParameter('purchase', $purchase);

        $initialResult = $query->getResult();

        foreach ($initialResult as $i => $result) {
            $results[$i] = $this->fullName($result);
        }

        return $results;
    }

    /**
     * @return Order[] returns an array of Order arrays
     *                 and NOT Order objects
     */
    public function purchases(Product $product, string $status)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
          o.order_id,
          i.item_id, i.quantity,
          i.created,i.updated,
          p.id AS productDataId,
          pr.id AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall as image,
          u.email, u.title, u.firstName, u.lastName,
          ui.image as pic

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc


         WHERE pr.id = :product
         AND o.status = :status

         ORDER BY i.updated ASC, i.created DESC
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('product', $product)
              ->setParameter('status', $status);

        $initialResult = $query->getResult();

        $results = [];
        $total = 0;
        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $item) {
                $results[$i] = $this->fullName($item);
                $results[$i]['index'] = $i + 1;
                $subtotal[] = $item['subtotal'];
            }
            $total = array_sum($subtotal);
        }

        return ['items' => $results, 'total' => $total];
    }

    /**
     * @return Order[] returns an array of Order arrays
     *                 and NOT Order objects
     */
    public function all($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort')
                                : 'o.updated desc,
                                   o.created desc,
                                   pr.name desc,
                                   pc.color';
        $id = $q->has('id') ? $q->get('id') : '';
        $item = $q->has('item') ? $q->get('item') : '';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $name = $q->has('name') ? $q->get('name') : '';
        $customer = $q->has('customer') ? $q->get('customer') : '';
        $status = $q->has('status') ? $q->get('status') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $size = $q->has('size') ? $q->get('size') : '';
        $fabrics = $q->has('fabrics') ? $q->get('fabrics') : '';
        $all = $q->all();

        $condition = '';
        $parameters = [];
        $type = [];

        if (!empty($id)) {
            $condition .= ' WHERE o.ord_id = :id ';
            $parameters['id'] = $id;
            $type['id'] = Type::INTEGER;
        }

        if (!empty($item)) {
            $condition .= ' WHERE i.ord_itm_id = :item ';
            $parameters['item'] = $item;
            $type['item'] = Type::INTEGER;
        }

        if (!empty($pc)) {
            $condition .= ' WHERE pc.clr_pvt_id = :pc ';
            $parameters = [$pc];
            $type = [Type::INTEGER];
        }

        if (!empty($brand)) {
            $condition .= ' WHERE pr.brand = :brand ';
            $parameters = [$brand];
            $type = [Type::STRING];
        }

        if (!empty($product) and empty($customer)) {
            $condition .= ' WHERE pr.pro_id = :product ';
            $parameters = [$product];
            $type = [Type::INTEGER];
        }

        if (!empty($customer) and empty($product)) {
            $condition .= ' WHERE u.user_id = :customer ';
            $parameters = [$customer];
            $type = [Type::INTEGER];
        }

        if (!empty($product) and !empty($customer)) {
            $condition .= ' WHERE pr.pro_id = :product AND
                           u.user_id = :customer AND o.status = :status';
            $parameters[] = $product;
            $type[] = Type::INTEGER;
            $parameters[] = $customer;
            $type[] = Type::INTEGER;
            $parameters['status'] = Order::STATUS_PAID;
            $type['status'] = Type::STRING;
        }

        if (!empty($size)) {
            $condition .= ' WHERE p.pro_pvt_id = :size ';
            $parameters = [$size];
            $type = [Type::INTEGER];
        }

        if (!empty($colors)) {
            $condition .= ' WHERE pc.color IN (:colors) ';
            $parameters = [$colors];
            $type = [Type::STRING];
        }

        if (!empty($fabrics)) {
            $condition .= ' WHERE pc.fabric::text  ~* :fabrics ';
            $parameters = [$fabrics];
            $type = [Type::STRING];  // $fabrics
        }

        if (!empty($status) and empty($condition)) {
            $condition .= ' WHERE o.status = :status ';
        } elseif (!empty($status) and !empty($condition)) {
            $condition .= ' AND o.status = :status ';
        }

        if (!empty($status)) {
            $parameters['status'] = $status;
            $type['status'] = Type::STRING;
        }

        $sql =
        'SELECT
          o.ord_id as order_id, o.status, o.created,
          i.ord_itm_id as item_id, i.quantity,
          i.created, i.updated,
          p.pro_pvt_id AS "productDataId",
          pr.pro_id AS "productId", pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.selling_price AS "sellingPrice", p.qty_in_stock AS "qtyInStock",
          p.size, p.sku, p.selling_price * i.quantity AS subtotal,
          pc.clr_pvt_id AS "colorId", pc.color, pc.fabric,
          pc.image_sm AS image,
          u.user_id AS "userId", u.email, u.title, u.first_name AS "firstName",
          u.last_name AS "lastName",
          ui.image AS pic

         FROM order_item i

         LEFT JOIN "orders" o ON o.ord_id = i.fk_ord_id
         LEFT JOIN users u ON u.user_id = o.fk_user_id
         LEFT JOIN user_image ui ON ui.fk_user_id = u.user_id
         LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
         LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
         LEFT JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id'
         .$condition." ORDER BY $sort $order ";

        $sqlTotal =
          'SELECT SUM(a.total)
           FROM
             ( SELECT SUM(p.selling_price * i.quantity) AS total
               FROM order_item i
               LEFT JOIN "orders" o ON o.ord_id = i.fk_ord_id
               LEFT JOIN users u ON u.user_id = o.fk_user_id
               LEFT JOIN user_image ui ON ui.fk_user_id = u.user_id
               LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
               LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
               LEFT JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id'
                .$condition.
                '
                GROUP BY p.selling_price
              ) a
        ';
        if (empty($status)) {
            if (empty($condition)) {
                $condition .= ' WHERE o.status = :status ';
            } elseif (!empty($condition)) {
                $condition .= ' AND o.status = :status ';
            }
        }

        $sqlCart =
          'SELECT SUM(a.total)
           FROM
             ( SELECT SUM(p.selling_price * i.quantity) AS total
               FROM order_item i
               LEFT JOIN "orders" o ON o.ord_id = i.fk_ord_id
               LEFT JOIN users u ON u.user_id = o.fk_user_id
               LEFT JOIN user_image ui ON ui.fk_user_id = u.user_id
               LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
               LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
               LEFT JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id'
                .$condition.
               ' GROUP BY p.selling_price
              ) a
        ';

        $results = [];
        $total = [];
        $totalCart = [];
        $totalPaid = [];
        $c = $this->getEntityManager()->getConnection();
        $initialResult = $c->executeQuery($sql, $parameters, $type)
                           ->fetchAllAssociative();

        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $result) {
                $initialResult[$i]['fabrics'] = json_decode($result['fabrics']);
            }

            foreach ($initialResult as $i => $item) {
                $results[$i] = $this->fullName($item);
            }
        }

        $total = $c->executeQuery($sqlTotal, $parameters, $type)
                   ->fetchAllAssociative();

        $status = 'cart';
        $parameters['status'] = $status;
        $type['status'] = Type::STRING;

        $totalCart = $c->executeQuery($sqlCart, $parameters, $type)
                       ->fetchAllAssociative();
        $status = Order::STATUS_PAID;

        $parameters['status'] = $status;

        $totalPaid = $c->executeQuery($sqlCart, $parameters, $type)
                       ->fetchAllAssociative();

        if (!empty($total)) {
            $total = $total[0]['sum'];
        }
        if (!empty($totalCart)) {
            $totalCart = $totalCart[0]['sum'];
        }
        if (!empty($totalPaid)) {
            $totalPaid = $totalPaid[0]['sum'];
        }

        return ['total' => ['total' => $total,
            'totalCart' => $totalCart,
            'totalPaid' => $totalPaid,
        ],
            'items' => $results];
    }

    /**
     * @return OrderItem[] returns an array of Order arrays
     *                     and NOT Order objects
     */
    public function search(Search $search, $query): array
    {
        $em = $this->getEntityManager();
        $order = $query->has('order') ? $query->get('order') : 'DESC';
        $sort = $query->has('sort') ? $query->get('sort')
                                    : 'o.updated desc,
                                       o.created desc,
                                       pr.name desc,
                                       pc.color';
        $id = $query->has('id') ? $query->get('id') : '';
        $pc = $query->has('pc') ? $query->get('pc') : '';
        $product = $query->has('product') ? $query->get('product') : '';
        $customer = $query->has('customer') ? $query->get('customer') : '';
        $size = $query->has('size') ? $query->get('size') : '';
        $colors = $query->has('colors') ? $query->get('colors') : '';
        $status = $query->has('status') ? $query->get('status') : '';
        $fabrics = $query->has('fabrics') ? explode('-', $query->get('fabrics')) : '';
        $condition = '';
        $conditionSQL = '';
        $parameters = [];
        $type = [];

        if (!empty($search->search())) {
            // Remove special characters except space
            $keywords = preg_replace('/[^A-Za-z0-9""+*_@., ]/', ' ', $search->search());
            // $keywords = preg_replace('/[&\/\\#,+()$~%.:*?<>{}]/',' ', $search->search());

            // Remove unnecessary space
            $keywords = preg_replace('/\s\s+/', ' ', $search->search());
            // Strip whitespace (or other characters) from the beginning and end of a string
            $keywords = trim((string) $keywords, "\x00.. \x1F");
            // Arrange all words in an array
            $keywords = explode(' ', $keywords);

            $condition .= ' WHERE (';
            $conditionSQL .= ' WHERE (';
            $keyText = '';
            foreach ($keywords as $i => $keyword) {
                if (end($keywords) == $keyword) {
                    $condition .= "
                (ILIKE(u.email, :word$i) = true) OR
                (ILIKE(u.firstName, :word$i) = true) OR
                (ILIKE(u.lastName, :word$i) = true) OR

                (ILIKE(pr.name, :word$i) = true) OR
                (ILIKE(pr.brand, :word$i) = true) OR
                (ILIKE(pc.color, :word$i) = true) OR
                (ILIKE(p.size, :word$i) = true) OR
                (ILIKE(JSON_TEXT(pc.fabric), :word$i) = true)
                )
                ";
                } else {
                    $condition .= "
                (ILIKE(u.email, :word$i) = true) OR
                (ILIKE(u.firstName, :word$i) = true) OR
                (ILIKE(u.lastName, :word$i) = true) OR

                (ILIKE(pr.name, :word$i) = true) OR
                (ILIKE(pr.brand, :word$i) = true) OR
                (ILIKE(pc.color, :word$i) = true) OR
                (ILIKE(p.size, :word$i) = true) OR
                (ILIKE(JSON_TEXT(pc.fabric), :word$i) = true) OR
               ";
                }

                $keyText =
                  ($i != array_key_last($keywords))
                  ? $keyText.$keyword.'|'
                  : $keyText.$keyword;
            }

            $conditionSQL .=
              ' u.email ~* ? OR u.first_name ~* ? OR
              u.last_name ~* ? OR pr.name ~* ? OR pr.brand ~* ? )';

            $parameters[] = strtolower($keyText);
            $parameters[] = strtolower($keyText);
            $parameters[] = strtolower($keyText);
            $parameters[] = strtolower($keyText);
            $parameters[] = strtolower($keyText);

            $type[] = Type::STRING;
            $type[] = Type::STRING;
            $type[] = Type::STRING;
            $type[] = Type::STRING;
            $type[] = Type::STRING;
        }

        if (!empty($search->startDate()) and !empty($search->endDate())) {
            if (!empty($search->search())) {
                $condition .= ' AND ';
                $conditionSQL .= ' AND ';
            } else {
                $condition .= ' WHERE ';
                $conditionSQL .= ' WHERE ';
            }

            $condition .= ' o.created BETWEEN :startDate AND :endDate ';

            $conditionSQL .= ' o.created BETWEEN ? AND ? ';

            $parameters[] = $search->startDate()->format('Y-m-d H:i:s');
            $type[] = Type::STRING;
            $parameters[] = $search->endDate()->format('Y-m-d H:i:s');
            $type[] = Type::STRING;
        }

        if (!empty($status)) {
            $condition .= ' AND o.status = :status';

            $conditionSQL .= ' AND o.status = ? ';
            $parameters['status'] = $status;
            $type['status'] = Type::STRING;
        }

        $dql =
        'SELECT
          o.order_id, o.status, o.created,
          i.item_id, i.quantity,
          i.created,i.updated,
          p.id AS productDataId,
          pr.id AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall as image,
          u.id AS userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc

          '.$condition;

        $dql .= " ORDER BY $sort $order ";

        $query = $em->createQuery($dql);

        if (!empty($search->startDate()) && !empty($search->endDate())) {
            $query
              ->setParameter('startDate', $search->startDate()->format('Y-m-d H:i:s'))
              ->setParameter('endDate', $search->endDate()->format('Y-m-d H:i:s'));
        }

        if (!empty($keywords)) {
            foreach ($keywords as $i => $word) {
                $query->setParameter("word$i", '%'.strtolower($word).'%');
            }
        }

        if (!empty($status)) {
            $query->setParameter('status', $status);
        }

        $initialResult = $query->getResult();
        $results = [];

        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $item) {
                $results[$i] = $this->fullName($item);
            }
        }

        $sqlTotal =
          'SELECT SUM(a.total)
           FROM
             ( SELECT SUM(p.selling_price * i.quantity) AS total
               FROM order_item i
               LEFT JOIN "orders" o ON o.ord_id = i.fk_ord_id
               LEFT JOIN users u ON u.user_id = o.fk_user_id
               LEFT JOIN user_image ui ON ui.fk_user_id = u.user_id
               LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
               LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
               LEFT JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id'
                .$conditionSQL.
                '
                GROUP BY p.selling_price
              ) a
        ';

        if (empty($status)) {
            if (empty($conditionSQL)) {
                $conditionSQL .= ' WHERE o.status = :status ';
            } elseif (!empty($conditionSQL)) {
                $conditionSQL .= ' AND o.status = ? ';
            }
        }

        $sqlCart =
          'SELECT SUM(a.total)
           FROM
             ( SELECT SUM(p.selling_price * i.quantity) AS total
               FROM order_item i
               LEFT JOIN "orders" o ON o.ord_id = i.fk_ord_id
               LEFT JOIN users u ON u.user_id = o.fk_user_id
               LEFT JOIN user_image ui ON ui.fk_user_id = u.user_id
               LEFT JOIN product_data p ON p.pro_pvt_id = i.fk_pro_id
               LEFT JOIN products pr ON pr.pro_id = p.fk_pro_id
               LEFT JOIN product_color pc ON pc.clr_pvt_id = p.fk_clr_pvt_id'
                .$conditionSQL.
               ' GROUP BY p.selling_price
              ) a
        ';

        $c = $this->getEntityManager()->getConnection();
        $total = $c->executeQuery($sqlTotal, $parameters, $type)
                   ->fetchAllAssociative();

        $status = 'cart';
        $parameters['status'] = $status;
        $type['status'] = Type::STRING;

        $totalCart = $c->executeQuery($sqlCart, $parameters, $type)
                       ->fetchAllAssociative();

        $status = Order::STATUS_PAID;
        $parameters['status'] = $status;

        $totalPaid = $c->executeQuery($sqlCart, $parameters, $type)
                       ->fetchAllAssociative();

        if (!empty($total)) {
            $total = $total[0]['sum'];
        }
        if (!empty($totalCart)) {
            $totalCart = $totalCart[0]['sum'];
        }
        if (!empty($totalPaid)) {
            $totalPaid = $totalPaid[0]['sum'];
        }

        return ['total' => ['total' => $total,
            'totalCart' => $totalCart,
            'totalPaid' => $totalPaid,
        ],
            'items' => $results];
    }

    /**
     * @return ProductData[] Return a stripped version (array) of the ProductData
     */
    public function colorSet(int $product)
    {
        $sql =
          'SELECT
          pc.clr_pvt_id AS "colorId",
          p.selling_price as "sellingPrice",
          SUM(i.quantity) as "totalQty",
          SUM(p.selling_price * i.quantity) AS "subCartTotal"

        FROM order_item i

        LEFT JOIN product_data p ON i.fk_pro_id = p.pro_pvt_id
        LEFT JOIN products pr ON p.fk_pro_id = pr.pro_id
        LEFT JOIN product_color pc ON p.fk_clr_pvt_id = pc.clr_pvt_id
        LEFT JOIN "orders" o ON i.fk_ord_id = o.ord_id

        WHERE pr.pro_id = ?
        AND o.status = ?

        GROUP BY
          pc.clr_pvt_id, p.selling_price
      ';
        $c = $this->getEntityManager()->getConnection();
        $fetch = 'fetchAllAssociative';
        $parameterCart = [$product, Order::STATUS_CART];
        $parameterPaid = [$product, Order::STATUS_PAID];
        $type = [Type::INTEGER,    // product_id
            Type::STRING,   // status
        ];
        $cart = [];
        $paid = [];
        $colorCart = $c->executeQuery($sql, $parameterCart, $type)->$fetch();
        if (!empty($colorCart)) {
            foreach ($colorCart as $i => $item) {
                $cart[$item['colorId']] = $item;
            }
        }

        $colorPaid = $c->executeQuery($sql, $parameterPaid, $type)->$fetch();
        if (!empty($colorPaid)) {
            foreach ($colorPaid as $i => $item) {
                $paid[$item['colorId']] = $item;
            }
        }

        return ['cart' => $cart, Order::STATUS_PAID => $paid];
    }

    public function reviews(User $user)
    {
        $user_id = $user->getId();
        $em = $this->getEntityManager();
        $dql = 'SELECT
         i.quantity, p.sellingPrice,  p.size,  p.id AS productDataId,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId, pc.color, pc.fabric, tx.texture, tg.tag,
         pc.imageMedium,
         r.id as review

       FROM App\Entity\Billing\OrderItem i

       LEFT JOIN i.product p
       LEFT JOIN p.product pr
       LEFT JOIN i.orderRef o
       LEFT JOIN o.users u
       LEFT JOIN p.color pc
       LEFT JOIN pc.texture tx
       LEFT JOIN pc.tag tg

       LEFT JOIN pr.productReviews r
       LEFT JOIN r.users rev

       -- Exclude item which the user has already reviewed.
       WHERE i.item_id NOT IN
        (
          SELECT ii.item_id

          FROM App\Entity\Billing\OrderItem ii

          LEFT JOIN ii.product pp
          LEFT JOIN pp.product pro
          LEFT JOIN ii.orderRef ord
          LEFT JOIN ord.users uu
          LEFT JOIN pp.color ppc
          LEFT JOIN ppc.texture ttx
          LEFT JOIN ppc.tag ttg
          LEFT JOIN pro.productReviews rr
          LEFT JOIN rr.users revv

          WHERE uu.id = :user
          AND ord.status = :paid
          AND uu.id IN (revv.id)
        )

       AND u.id = :user
       AND o.status = :paid
       ORDER BY pr.name
       ';

        $dql = 'SELECT
         i.quantity, p.sellingPrice,  p.size,  p.id AS productDataId,
         pr.id AS productId, pr.name, pr.brand,
         pr.category,pr.occasion, pr.type,
         pc.id AS colorId, pc.color, pc.fabric,
         pc.imageMedium,
         r.id as review

       FROM App\Entity\Billing\OrderItem i

       LEFT JOIN i.product p
       LEFT JOIN p.product pr
       LEFT JOIN i.orderRef o
       LEFT JOIN o.users u
       LEFT JOIN p.color pc
       LEFT JOIN pr.productReviews r

       WHERE r.id IS NULL
       AND u.id = :user
       AND o.status = :paid
       ORDER BY pr.name
       ';
        $query = $em->createQuery($dql);
        $query->setParameter('user', $user_id)
              ->setParameter(Order::STATUS_PAID, Order::STATUS_PAID)
        ;
        $iniReviews = $query->getResult();
        // dd($iniReviews);
        if (!empty($iniReviews)) {
            foreach ($iniReviews as $i => $product) {
                $iniReviews[$i] = $this->fullName($product);
            }
        }

        $results = [];
        foreach ($iniReviews as $i => $product) {
            $colorKeys = [
                'colorId', 'color', 'colors_set', 'colors_full_set', 'colors_full',
                'fabrics', 'fabrics_full', 'fabrics_full_set',
                'textures', 'textures_full', 'textures_full_set',
                'tags', 'imageMedium', 'quantity', 'sellingPrice',
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

    public function checkProduct(int $product)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT COUNT(i) AS COUNT

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN i.product p

         WHERE p.id = :product
         ';
        $query = $entityManager->createQuery($dql);
        $query->setParameter('product', $product);
        $result = $query->getOneOrNullResult()['COUNT'];

        return (0 === $result) ? true : false;
    }

    public function checkPurchase(int $product, ?User $user)
    {
        if (empty($user)) {
            return ['purchase' => '', 'review' => ''];
        }

        $user_id = $user->getId();
        $em = $this->getEntityManager();
        $dql =
        'SELECT COUNT(i) AS COUNT
         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN o.users u
         LEFT JOIN i.product p
         LEFT JOIN p.product pr

         WHERE pr.id = :product AND u.id = :user
         ';

        $query = $em->createQuery($dql);
        $query->setParameter('product', $product)
              ->setParameter('user', $user_id);

        $result = $query->getOneOrNullResult()['COUNT'];

        $checkPurchase = (0 !== $result) ? true : false;

        $dqlReview =
        'SELECT COUNT(i) AS COUNT
         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN o.users u
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN pr.productReviews r
         LEFT JOIN r.users rev

         WHERE pr.id = :product AND u.id = :user
         AND rev.id = :user
         ';

        $query = $em->createQuery($dqlReview);
        $query->setParameter('product', $product)
              ->setParameter('user', $user_id);

        $result = $query->getOneOrNullResult()['COUNT'];

        $checkReview = (0 !== $result) ? true : false;

        $check = ['purchase' => $checkPurchase,
            'review' => $checkReview];

        return $check;
    }

    public function latest(Product $product, $status)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT
          o.order_id,
          i.item_id, i.quantity,
          i.created,i.updated,
          p.id AS productDataId,
          pr.id AS productId, pr.name, pr.features,
          pr.brand, pr.category, pr.occasion, pr.type,
          p.sellingPrice, p.qtyInStock, p.size, p.sku,
          p.sellingPrice * i.quantity AS subtotal,
          pc.id AS colorId , pc.color, pc.fabric,
          pc.imageSmall as image,
          u.id AS userId, u.email, u.title, u.firstName, u.lastName,
          ui.image as pic

         FROM App\Entity\Billing\OrderItem i
         LEFT JOIN i.orderRef o
         LEFT JOIN o.users u
         LEFT JOIN u.userImage ui
         LEFT JOIN i.product p
         LEFT JOIN p.product pr
         LEFT JOIN p.color pc


         WHERE pr.id = :product
         AND o.status = :status
         ORDER BY i.updated DESC, i.created DESC
        ';

        $query = $em->createQuery($dql)->setMaxResults(5);
        $query->setParameter('product', $product)
              ->setParameter('status', $status);

        $initialResult = $query->getResult();

        $results = [];
        if (!empty($initialResult)) {
            foreach ($initialResult as $i => $item) {
                $results[$i] = $this->fullName($item);
            }
        }

        return $results;
    }

    public function paidProducts(Product $product, User $user)
    {
        $em = $this->getEntityManager();
        $dql =
        'SELECT i

       FROM App\Entity\Billing\OrderItem i
       LEFT JOIN i.orderRef o
       LEFT JOIN o.users u
       LEFT JOIN i.product p
       LEFT JOIN p.product pr

       WHERE pr.id = :product
       AND u.id = :user
       AND o.status = :status
       ';

        $query = $em->createQuery($dql);
        $query->setParameter('product', $product)
              ->setParameter('user', $user)
              ->setParameter('status', Order::STATUS_PAID);

        $initialResult = $query->getResult();

        return $initialResult;
    }
}
