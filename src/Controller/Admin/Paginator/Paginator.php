<?php

// src/Controller/Admin/Paginator/Paginator.php

namespace App\Controller\Admin\Paginator;

use App\Controller\_Utils\Attributes;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Paginator
{
    use Attributes;

    public function __construct(private PaginatorInterface $knp, private RequestStack $requestStack)
    {
    }

    public function paginate(array $set)
    {
        $request = $this->requestStack->getCurrentRequest();
        $q = $request->query;
        $page = $q->getInt('page', 1);

        /* Paginate the results | Start */
        $pages = $this->itemsRange();
        $itemsPage = $pages[0];
        $maxPage = (int) ceil(count($set) / $itemsPage);
        $page = $q->getInt('page', 1);

        if ($q->has('items_page')) {
            $itemsPage = (int) $q->get('items_page');
            $maxPage = (int) ceil(count($set) / $itemsPage);

            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        $items = $this->knp->paginate($set, $page, $itemsPage);

        return [
            'items' => $items,
            'pages' => $pages,
            'items_page' => $itemsPage,
            'maxPage' => $maxPage,
        ];
    }
}
