<?php

// src/Controller/_Utils/SearchTrait.php

namespace App\Controller\_Utils;

use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\Product\Product\ProductRepository as ProductRepo;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Inject arrays of product attributes to be used in <select> of forms.
 */
trait SearchTrait
{
    public function __construct(
        protected RequestStack $requestStack,
        protected ProductRepo $productRepo,
        protected $searchForm = null,
        protected $get = null,
    ) {
    }

    public function search()
    {
        $r = $this->requestStack->getCurrentRequest();
        $search = new Search();

        $this->searchForm = $this->createForm(SearchForm::class, $search);

        $this->searchForm->handleRequest($r);
        $q = $r->query;
        $this->get = $q->all();

        $sort = $q->has('sort') ? $q->get('sort') : 'updated';
        $order = $q->has('order') ? $q->get('order') : 'DESC';

        if (empty($search->search()) && empty($search->brands())) {
            $productSet = $this->productRepo->all($sort, $order);
            $searchTerm = null;
        } elseif (!(empty($search->search()) && empty($search->brands()))) {
            $searchTerm = $search->search();
            $productSet = $this->productRepo->search($search, $sort, $order);
        }
    }
}
