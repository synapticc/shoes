<?php

// src/Repository/User/Session/PageViewRepository.php

namespace App\Repository\User\Session;

use App\Controller\_Utils\Attributes;
use App\Entity\User\Session\PageView;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageView>
 *
 * @method PageView|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageView|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageView[]    findAll()
 * @method PageView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageViewRepository extends ServiceEntityRepository
{
    use Attributes;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    public function add(PageView $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PageView $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PageView[] Returns an array of PageView arrays
     */
    public function all($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'pg.created';
        $dql =
          'SELECT
            pg.id, pg.created,
            pg.route, pg.queryParameters, pg.url, pg.referer,
            pg.refererRoute,

            u.email, u.title, u.firstName, u.lastName,
            ui.image as pic,

            pc.imageSmall, pd.id as pd_id, pr.name, pr.brand,
            pd.size, pc.color,

            ref.id as ref_id, refpr.name as refName, refpr.brand as refBrand, refpc.imageSmall as imageSmallRef,
            ref.size as refSize, refpc.color as refColor

            FROM App\Entity\User\Session\PageView pg
            INNER JOIN pg.session s
            INNER JOIN s.users u
            LEFT JOIN u.userImage ui

            LEFT JOIN pg.product pd
            LEFT JOIN pd.product pr
            LEFT JOIN pd.color pc

            LEFT JOIN pg.refererProduct ref
            LEFT JOIN ref.product refpr
            LEFT JOIN ref.color refpc
           ';
        $dql .= " ORDER BY $sort $order ";
        $query = $em->createQuery($dql);
        $initialResult = $query->getResult();

        foreach ($initialResult as $i => $item) {
            $result[$i] = $this->fullName($item);
        }

        return $result;
    }

    /**
     * @return PageView[] Returns an array of PageView arrays
     */
    public function users($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'pg.created';
        $customer = $q->has('customer') ? $q->get('customer') : '';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $size = $q->has('size') ? $q->get('size') : '';

        /* Retrieve only store page containing Product and
           Exclude all admin pages */
        $condition = ' WHERE pg.route = :store_details
                       AND (ILIKE(pg.route, :admin) != true)';

        if (!empty($customer)) {
            $condition .= ' AND u.id = :customer ';
        }

        if (!empty($pc)) {
            $condition .= ' AND pc.id = :pc ';
        }

        if (!empty($brand)) {
            $condition .= ' AND pr.brand = :brand ';
        }

        if (!empty($product)) {
            $condition .= ' AND pr.id = :product ';
        }

        if (!empty($colors)) {
            $condition .= ' AND pc.color IN (:colors) ';
        }

        if (!empty($size)) {
            $condition .= ' AND pd.size = :size ';
        }

        $dql =
          'SELECT
            pg.id, pg.created,
            pg.route, pg.queryParameters, pg.url, pg.referer,
            pg.refererRoute,

            u.id as userId, u.email, u.title, u.firstName, u.lastName,
            ui.image as pic,

            pc.imageSmall, pd.id as pd_id,
            pr.id as product, pr.name, pr.brand,
            pd.size, pc.id as colorId,pc.color,

            ref.id as ref_id, refpr.id as refProduct, refpr.name as refName, refpr.brand as refBrand, refpc.imageSmall as imageSmallRef,
            ref.size as refSize,refpc.id as refColorId, refpc.color as refColor

            FROM App\Entity\User\Session\PageView pg
            INNER JOIN pg.session s
            INNER JOIN s.users u
            LEFT JOIN u.userImage ui

            LEFT JOIN pg.product pd
            LEFT JOIN pd.product pr
            LEFT JOIN pd.color pc

            LEFT JOIN pg.refererProduct ref
            LEFT JOIN ref.product refpr
            LEFT JOIN ref.color refpc
           ';

        $dql = $dql.$condition." ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        $query->setParameter('store_details', 'store_details')
              ->setParameter('admin', '%admin%');

        if (!empty($customer)) {
            $query->setParameter('customer', $customer);
        }

        if (!empty($pc)) {
            $query->setParameter('pc', $pc);
        }

        if (!empty($brand)) {
            $query->setParameter('brand', $brand);
        }

        if (!empty($product)) {
            $query->setParameter('product', $product);
        }

        if (!empty($colors)) {
            $query->setParameter('colors', $colors);
        }

        if (!empty($size)) {
            $query->setParameter('size', $size);
        }

        $initialResult = $query->getResult();
        $result = [];
        foreach ($initialResult as $i => $item) {
            $refererKeys =
              [
                  'ref_id' => 'ref_id',
                  'productId' => 'refProduct',
                  'name' => 'refName',
                  'brand' => 'refBrand',
                  'imageSmall' => 'imageSmallRef',
                  'size' => 'refSize',
                  'colorId' => 'refColorId',
                  'color' => 'refColor'];

            $referer = [];
            if (!empty($item['ref_id'])) {
                foreach ($item as $key => $value) {
                    if (in_array($key, $refererKeys)) {
                        $referer[array_search($key, $refererKeys)]
                                         = $item[$key];
                    }
                }

                $referer =
                  $this->fullName($referer);

                $initialResult[$i]['refererProduct'] = $referer;
            }
            $result[$i] = $this->fullName($initialResult[$i]);
        }

        return $result;
    }

    /**
     * @return PageView[] Returns an array of PageView arrays
     */
    public function anonymous($q)
    {
        $em = $this->getEntityManager();
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'pg.created';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $size = $q->has('size') ? $q->get('size') : '';

        /* Retrieve only store page containing Product and
           Exclude all admin pages */
        $condition = ' WHERE pg.route = :store_details
                       AND (ILIKE(pg.route, :admin) != true)
                       AND u.id IS NULL';

        if (!empty($pc)) {
            $condition .= ' AND pc.id = :pc ';
        }

        if (!empty($brand)) {
            $condition .= ' AND pr.brand = :brand ';
        }

        if (!empty($product)) {
            $condition .= ' AND pr.id = :product ';
        }

        if (!empty($colors)) {
            $condition .= ' AND pc.color IN (:colors) ';
        }

        if (!empty($size)) {
            $condition .= ' AND pd.size = :size ';
        }

        $dql =
          'SELECT
            pg.id, pg.created,
            pg.route, pg.queryParameters, pg.url, pg.referer,
            pg.refererRoute, d.ipAddress,

            pc.imageSmall, pd.id as pd_id,
            pr.id as product, pr.name, pr.brand,
            pd.size, pc.id as colorId,pc.color,

            ref.id as ref_id, refpr.id as refProduct, refpr.name as refName, refpr.brand as refBrand, refpc.imageSmall as imageSmallRef,
            ref.size as refSize,refpc.id as refColorId, refpc.color as refColor

            FROM App\Entity\User\Session\PageView pg
            INNER JOIN pg.session s
            INNER JOIN s.device d
            LEFT JOIN s.users u

            LEFT JOIN pg.product pd
            LEFT JOIN pd.product pr
            LEFT JOIN pd.color pc


            LEFT JOIN pg.refererProduct ref
            LEFT JOIN ref.product refpr
            LEFT JOIN ref.color refpc
           ';

        $dql = $dql.$condition." ORDER BY $sort $order ";

        $query = $em->createQuery($dql);
        $query->setParameter('store_details', 'store_details')
              ->setParameter('admin', '%admin%');

        if (!empty($pc)) {
            $query->setParameter('pc', $pc);
        }

        if (!empty($brand)) {
            $query->setParameter('brand', $brand);
        }

        if (!empty($product)) {
            $query->setParameter('product', $product);
        }

        if (!empty($colors)) {
            $query->setParameter('colors', $colors);
        }

        if (!empty($size)) {
            $query->setParameter('size', $size);
        }

        $initialResult = $query->getResult();

        $result = [];
        foreach ($initialResult as $i => $item) {
            $result[$i] = $this->fullName($item);
        }

        return $result;
    }

    /**
     * @return PageView[] Returns an array of PageView arrays
     */
    public function user(User $user)
    {
        $entityManager = $this->getEntityManager();
        $dql =
          'SELECT
             p.id,
             p.created,
             pr.id as pro_id, pr.name, pr.brand, pc.imageSmall,
             refpr.id as ref_id, refpr.name as refName, refpr.brand as refBrand, refpc.imageSmall as imageSmallRef,
             p.route, p.queryParameters, p.url, p.referer

           FROM App\Entity\User\Session\PageView p
           INNER JOIN p.session s
           INNER JOIN s.users u

           LEFT JOIN p.product pd
           LEFT JOIN pd.product pr
           LEFT JOIN pd.color pc
           LEFT JOIN p.refererProduct ref
           LEFT JOIN ref.product refpr
           LEFT JOIN ref.color refpc

           WHERE u.id = :user
           AND (p.route = :store OR p.route = :store_details)
           ORDER BY p.created DESC
           ';

        $query = $entityManager->createQuery($dql);
        $query->setParameter('user', $user)
              ->setParameter('store', 'store')
              ->setParameter('store_details', 'store_details');

        return $query->getResult();
    }

    /**
     * @return PageView[] returns an array of the latest 15 PageView
     */
    public function latest(int $product)
    {
        $em = $this->getEntityManager();
        $dql =
          'SELECT
            pg.id, pg.created,
            pg.route, pg.queryParameters, pg.url, pg.referer,
            pg.refererRoute,

            u.id as userId, u.email, u.title, u.firstName, u.lastName,
            ui.image as pic,

            pc.imageSmall, pd.id as pd_id,
            pr.id as product, pr.name, pr.brand,
            pd.size, pc.id as colorId,pc.color,

            ref.id as ref_id, refpr.id as refProduct, refpr.name as refName, refpr.brand as refBrand, refpc.imageSmall as imageSmallRef,
            ref.size as refSize,refpc.id as refColorId, refpc.color as refColor

            FROM App\Entity\User\Session\PageView pg
            INNER JOIN pg.session s
            INNER JOIN s.users u
            LEFT JOIN u.userImage ui

            LEFT JOIN pg.product pd
            LEFT JOIN pd.product pr
            LEFT JOIN pd.color pc
            LEFT JOIN pg.refererProduct ref
            LEFT JOIN ref.product refpr
            LEFT JOIN ref.color refpc

            WHERE pr.id = :product
           ';

        /* Retrieve only store page containing Product and
           Exclude all admin pages */
        $condition = ' AND pg.route = :store_details
                       AND (ILIKE(pg.route, :admin) != true)';

        $dql = $dql.$condition.' ORDER BY pg.created DESC ';

        $query = $em->createQuery($dql)->setMaxResults(15);
        $query->setParameter('product', $product)
              ->setParameter('store_details', 'store_details')
              ->setParameter('admin', '%admin%');

        $initialResult = $query->getResult();
        $result = [];
        foreach ($initialResult as $i => $item) {
            $result[$i] = $this->fullName($item);
        }

        return $result;
    }

    // /**
    //  * @return PageView[] Returns an array of PageView arrays
    //  */
    // public function anonymous()
    // {
    //     $entityManager = $this->getEntityManager();
    //     $query =
    //       $entityManager
    //         ->createQuery(
    //           'SELECT
    //              p.id,
    //              p.created,
    //              pc.imageSmall, pr.name, pr.brand,
    //              p.route, p.queryParameters, p.url, p.referer
    //
    //            FROM App\Entity\User\Session\PageView p
    //            INNER JOIN p.session s
    //            LEFT JOIN s.users u
    //            LEFT JOIN p.product pd
    //            LEFT JOIN pd.product pr
    //            LEFT JOIN pd.color pc

    //            WHERE u.id IS NULL
    //            ORDER BY p.created DESC
    //            ')
    //            ;
    //
    //     return $query->getResult();
    // }

    //    /**
    //     * @return PageView[] Returns an array of PageView objects
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

    //    public function findOneBySomeField($value): ?PageView
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
