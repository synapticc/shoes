<?php

// src/Controller/Admin/AdminTransactionsController.php

namespace App\Controller\Admin;

use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\Billing\Order;
use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\Billing\BillingRepository as InvoiceRepo;
use App\Repository\Billing\OrderItemRepository as ItemRepo;
use App\Repository\Billing\OrderRepository as OrderRepo;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface as Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTransactionsController extends AbstractController
{
    use Cart;

    public function purchases(Request $r, OrderRepo $orderRepo, Paginator $pg): Response
    {
        $s = new Search();
        $searchForm = $this->createForm(SearchForm::class, $s);
        $searchForm->handleRequest($r);

        $q = $r->query;
        $order = $q->has('order') ? $q->get('order') : 'DESC';
        $sort = $q->has('sort') ? $q->get('sort') : 'o.dateOfPurchase';
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';

        /*  Collect any column filter with their respective label */
        $filter = [];
        if (!empty($email)) {
            $filter['column'] = 'customer_email';
            $filter['value'] = $email;
        }

        /* Check if search and range date are empty */
        if (empty($s->search()) and empty($s->startDate())) {
            $purchases = $orderRepo->purchasesFull($q);
            $searchTerm = null;
        }
        /* Check if search and range date are not empty */ elseif (!(empty($s->search())) or !empty($s->startDate())) {
            $purchases = $orderRepo->search($s, $q);
            $searchTerm = $s->search();
        }

        // Paginate the results
        $page = $r->query->getInt('page', 1);

        $pages = [15, 30, 50, 100];
        $itemsPage = $pages[0];

        $maxPage = (int) ceil(count($purchases) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $r->query->get('items_page');
            $maxPage = (int) ceil(count($purchases) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $purchases = $pg->paginate($purchases, $page, 12);

        return $this->render('admin/7_transactions/index.html.twig', [
            'purchases' => $purchases,
            'cart' => $this->cart,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'get' => $q->all(),
            'pages' => $pages,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'pages' => $pages,
            'filters' => $filter,
        ]);
    }

    public function show(Request $r, Order $order, OrderRepo $orderRepo, ItemRepo $itemRepo, Paginator $pg, Search $search): Response
    {
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);

        $searchTerm = $search->search();

        // Redirect to Product Index to search for Product.
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('admin_transactions', $r->query->all(), Response::HTTP_SEE_OTHER);
        }

        $q = $r->query;
        $purchase = $orderRepo->info($order->getId());
        $total = $orderRepo->total($order->getId());
        $items = $itemRepo->items($order->getId(), $q);

        $page = $r->query->getInt('page', 1);
        $items = $pg->paginate($items, $page, 8);

        return $this->render(
            'admin/7_transactions/index.html.twig',
            [
                'purchase' => $purchase,
                'items' => $items,
                'total' => $total,
                'searchForm' => $searchForm,
                'search' => $searchTerm,
                'get' => $r->query->all(),
            ]
        );
    }

    public function items(Request $r, ItemRepo $itemRepo, Form $form, Paginator $pg): Response
    {
        $s = new Search();
        $searchForm = $this->createForm(SearchForm::class, $s);
        $searchForm->handleRequest($r);

        /* Retrieve all queries. $q = $query */
        $q = $r->query;
        $id = $q->has('id') ? $q->get('id') : '';
        $pc = $q->has('pc') ? $q->get('pc') : '';
        $pcUrl = $q->has('pc_url') ? $q->get('pc_url') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $product = $q->has('product') ? $q->get('product') : '';
        $productName = $q->has('product_name') ? $q->get('product_name') : '';
        $customer = $q->has('customer') ? $q->get('customer') : '';
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';
        $purchasedItem = $q->has('purchased_item') ? $q->get('purchased_item') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $colorName = $q->has('color_name') ? $q->get('color_name') : '';
        $size = $q->has('size') ? $q->get('size') : '';
        $fabrics = $q->has('fabrics') ? $q->get('fabrics') : '';
        $fabricName = $q->has('fabric_name') ? $q->get('fabric_name') : '';
        $status = Order::STATUS_PAID;
        $itemSet = [];

        /* Check if search and range date are empty */
        if ((empty($s->search()) and empty($s->startDate()))
            or (!empty($id) or !empty($pc) or !empty($brand)
            or !empty($product) or !empty($customer) or !empty($size)
            or !empty($colors) or !empty($fabrics))) {
            $itemSet = $itemRepo->all($q);
            $searchTerm = null;
        }
        /* Check if search and range date are not empty */ elseif (!empty($s->search()) or !empty($s->startDate())) {
            $itemSet = $itemRepo->search($s, $q);
            $searchTerm = $s->search();
        }

        $items = $itemSet['items'];
        $total = $itemSet['total'];

        /*  Collect any column filter with their respective label */
        $filter = [];
        $columns = ['id' => $id, 'pc_url' => $pcUrl, 'brand' => $brand,
            'name' => $productName, 'purchased_item' => $purchasedItem,
            'customer_email' => $email, 'colors' => $colorName,
            'size' => $size, 'fabrics' => $fabricName];

        foreach ($columns as $key => $column) {
            if (!empty($column)) {
                $filter['column'] = $key;
                $filter['value'] = $column ? $column : [];
            }
        }

        /* Paginate the results | Start */
        $pages = [25, 50, 75, 100];
        $itemsPage = $pages[0];

        $maxPage = (int) ceil(count($items) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($items) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }
        $items = $pg->paginate($items, $page, $itemsPage);
        /* Paginate the results | End */

        return $this->render('admin/7_transactions/index.html.twig', [
            'items' => $items,
            'total' => $total,
            'cart' => $this->cart,
            'pages' => $pages,
            'searchForm' => $searchForm,
            'get' => $q->all(),
            'items_page' => $itemsPage,
            'search' => $searchTerm,
            'maxPage' => $maxPage,
            'filters' => $filter,
        ]);
    }

    public function invoices(Request $r, InvoiceRepo $repo, Paginator $pg): Response
    {
        $s = new Search();
        $searchForm = $this->createForm(SearchForm::class, $s);
        $searchForm->handleRequest($r);
        $q = $r->query;
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';

        /*  Collect any column filter with their respective label */
        $filter = [];
        if (!empty($email)) {
            $filter['column'] = 'customer_email';
            $filter['value'] = $email;
        }

        $pages = [8, 15, 25, 50, 100];
        $itemsPage = $pages[0];

        /* Check if search and range date are empty */
        if (empty($s->search()) and empty($s->startDate())) {
            $invoices = $repo->invoices($q);
            $searchTerm = null;
        }
        /* Check if search and range date are not empty */ elseif (!(empty($s->search())) or !empty($s->startDate())) {
            $invoices = $repo->search($s, $q);
            $searchTerm = $s->search();
        }

        $maxPage = (int) ceil(count($invoices) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($invoices) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $invoices = $pg->paginate($invoices, $page, $itemsPage);

        return $this->render('admin/7_transactions/index.html.twig', [
            'invoices' => $invoices,
            'cart' => $this->cart,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'get' => $q->all(),
            'pages' => $pages,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'filters' => $filter,
        ]);
    }
}
