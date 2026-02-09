<?php

// src/Controller/Admin/Log/PageViewController.php

namespace App\Controller\Admin\Log;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\User\Session\PageViewRepository as PageRepo;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageViewController extends AbstractController
{
    use Attributes;

    public function users(Request $r, PageRepo $page, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        $customerUrl = $q->has('customer_url') ? $q->get('customer_url') : '';
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';
        $pcUrl = $q->has('pc_url') ? $q->get('pc_url') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $productName = $q->has('product_name') ? $q->get('product_name') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $colorName = $q->has('color_name') ? $q->get('color_name') : '';
        $size = $q->has('size') ? $q->get('size') : '';

        $pageViews = $page->users($q);

        /*  Collect any column filter with their respective label */
        $filter = [];
        $columns = [
            'pc_url' => $pcUrl,  'brand' => $brand,
            'name' => $productName,
            'customer_email' => $email, 'colors' => $colorName,
            'size' => $size, ];

        foreach ($columns as $key => $column) {
            if (!empty($column)) {
                $filter['column'] = $key;
                $filter['value'] = $column ? $column : [];
            }
        }

        /* Paginate the results | Start */
        $pages = [25, 50, 75, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($pageViews) / $itemsPage);
        $page = $q->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($pageViews) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }
        $pageViews = $pg->paginate($pageViews, $page, $itemsPage);
        /* Paginate the results | End */

        return $this->render('admin/6_log/index.html.twig', [
            'pageViews' => $pageViews,
            'searchForm' => $searchForm,
            'search' => '',
            'get' => $r->query->all(),
            'pages' => $pages,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'filters' => $filter,
        ]);
    }

    public function anonymous(Request $r, PageRepo $page, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        $customerUrl = $q->has('customer_url') ? $q->get('customer_url') : '';
        $email = $q->has('customer_email') ? $q->get('customer_email') : '';
        $pcUrl = $q->has('pc_url') ? $q->get('pc_url') : '';
        $brand = $q->has('brand') ? $q->get('brand') : '';
        $productName = $q->has('product_name') ? $q->get('product_name') : '';
        $colors = $q->has('colors') ? $q->get('colors') : '';
        $colorName = $q->has('color_name') ? $q->get('color_name') : '';
        $size = $q->has('size') ? $q->get('size') : '';

        $pageViews = $page->anonymous($q);

        /*  Collect any column filter with their respective label */
        $filter = [];
        $columns = [
            'pc_url' => $pcUrl,  'brand' => $brand,
            'name' => $productName,
            'customer_email' => $email, 'colors' => $colorName,
            'size' => $size, ];

        foreach ($columns as $key => $column) {
            if (!empty($column)) {
                $filter['column'] = $key;
                $filter['value'] = $column ? $column : [];
            }
        }

        /* Paginate the results | Start */
        $pages = [25, 50, 75, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($pageViews) / $itemsPage);
        $page = $q->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($pageViews) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $pageViews = $pg->paginate($pageViews, $page, $itemsPage);
        /* Paginate the results | End */

        return $this->render('admin/6_log/index.html.twig', [
            'pages' => $pages,
            'searchForm' => $searchForm,
            'pageViews' => $pageViews,
            'search' => '',
            'get' => $r->query->all(),
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
            'filters' => $filter,
        ]);
    }
}
