<?php

// src/Controller/_Utils/Paginator.php

namespace App\Controller\_Utils;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Paginate queries
 * Retrieve the last page of the results.
 */
class Paginator
{
    private int $totalPages;
    private int $totalItems;
    private int $currentPage;
    private int $lastPage;
    private $items;

    /**
     * Pass through a query object, current page & page_size
     * the offset is calculated from the page and page_size
     * returns an `DoctrinePaginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param int $page      Current page (defaults to 1)
     * @param int $page_size The total number per page (defaults to 5)
     *
     * @return DoctrinePaginator
     */
    public function paginate($query, int $page = 1, int $page_size = 3): Paginator
    {
        // Eliminate any minus from the number
        $page_size = abs($page_size);
        $paginator = new DoctrinePaginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($page_size * ($page - 1))
            ->setMaxResults($page_size);

        $this->currentPage = $page;
        // Retrieve the total number of items
        $this->totalItems = $paginator->count();
        // Calculate the total number of pages
        $this->totalPages = ceil($this->totalItems / $page_size);
        // Determine the last page
        $this->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());
        // Retrieve paginated results
        $this->items = $paginator
                        ->getQuery()
                        // ->getResult(Query::HYDRATE_SIMPLEOBJECT)
                        // ->getResult(HYDRATE_SINGLE_SCALAR)
                        ->getResult()
        ;

        // return an object of Paginator containing all arguments
        return $this;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getItems()
    {
        return $this->items;
    }
}
