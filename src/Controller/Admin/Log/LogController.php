<?php

// src/Controller/Admin/Log/LogController.php

namespace App\Controller\Admin\Log;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\User\Log\LoginReportRepository as LoginRepo;
use Knp\Component\Pager\PaginatorInterface as Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogController extends AbstractController
{
    use Attributes;

    public function loginReport(Request $r, LoginRepo $loginRepo, Paginator $pg): Response
    {
        $search = new Search();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($r);
        $q = $r->query;

        if (empty($search->search())) {
            $loginReport = $loginRepo->login($q);
            $searchTerm = null;
        } elseif (!(empty($search->search()))) {
            $searchTerm = $search->search();
            $loginReport = $loginRepo->search($search, $q);
        }

        //  Retrieving items per page number
        $pages = [20, 40, 60, 80, 100];
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($loginReport) / $itemsPage);
        $page = $r->query->getInt('page', 1);

        if ($r->query->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($loginReport) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $loginReport = $pg->paginate($loginReport, $page, $itemsPage);

        return $this->render('admin/6_log/index.html.twig', [
            'loginReport' => $loginReport,
            'searchForm' => $searchForm,
            'search' => $searchTerm,
            'get' => $r->query->all(),
            'pages' => $pages,
            'items_page' => $itemsPage,
        ]);
    }
}
