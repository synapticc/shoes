<?php

// src/Controller/StoreController.php

namespace App\Controller\Store;

use App\Controller\Cart\CartTray\CartTrayTrait as Cart;
use App\Entity\NoMap\Search\Search;
use App\Form\Search\SearchForm;
use App\Repository\Product\ProductData\ProductDataRepository;
use App\Service\MaxItemsService;
use App\Service\ProductListingService;
use App\Service\StaticFiltersService;
use Fuse\Fuse;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;

class StoreController extends AbstractController
{
    use Cart;

    public function __construct(
        private ProductListingService $listingService,
        private StaticFiltersService $staticFilters,
        private MaxItemsService $maxItemsService,
        private LoggerInterface $logger,
    ) {
    }

    public function index(
        Request $request,
        ProductDataRepository $repo,
        PaginatorInterface $paginator,
        ?Stopwatch $stopwatch = null,
    ): Response {
        try {

            // i. Start the timer.
            if ($stopwatch) {
                $stopwatch->start('store', 'analytics');
            }

            // 1. Handle search form
            $search = new Search();
            $searchForm = $this->createForm(SearchForm::class, $search);
            $searchForm->handleRequest($request);

            // 2. Fetch filtered products
            $productData = $repo->filter($request);

            // 3. Get max items per page (cached from database)
            $maxItems = $this->maxItemsService->listing();

            // 4. Get page number
            $page = $request->query->getInt('page', 1);

            // 5. Paginate results using KnpPaginator
            $paginatedData = $paginator->paginate(
                $productData,
                $page,
                $maxItems,
                [
                    'pageParameterName' => 'page',
                    'sortFieldParameterName' => 'sort',
                    'sortDirectionParameterName' => 'direction',
                    'distinct' => true,
                ]
            );

            // 6. Prepare listing data
            $listingData = $this->listingService->prepareListing(
                $paginatedData,
                $request->query,
                $this->staticFilters->getStaticFilters(),
                $maxItems,
            );

            // 7. Get pagination metadata
            $paginationMeta = $this->listingService->getPaginationMetadata($paginatedData);
            $pageRange = $this->listingService->getPageRange($paginatedData, 2);

            // 8. Get active filters and breadcrumbs
            $activeFilters = $this->extractActiveFilters($request->query->all());
            $breadcrumbs = $this->staticFilters->getBreadcrumbs($activeFilters);

            // 9. Merge all template data
            $templateData = array_merge(
                $listingData,
                [
                    'searchForm' => $searchForm->createView(),
                    'pagination' => $paginationMeta,
                    'pageRange' => $pageRange,
                    'breadcrumbs' => $breadcrumbs,
                    'activeFilters' => $activeFilters,
                    'filterSummary' => $this->staticFilters->getFilterSummary(),
                ],
                ['cart' => $this->cart($this->getUser())]
            );


            // ii. Stop the timer and get the event info.
            if ($stopwatch) {
                $event = $stopwatch->stop('store');
                $executionTime = $event->getDuration(); // Returns int/float in milliseconds
            } else {
                $executionTime = 0;
            }

            $this->logger->debug('Store index rendered', [
                'page' => $page,
                'items_per_page' => $maxItems,
                'total_items' => $paginationMeta['totalItems'],
                'total_pages' => $paginationMeta['totalPages'],
                'execution_time_ms' => round($executionTime * 1000, 2),
            ]);

            // dd($templateData);
            return $this->render('store/index.html.twig', $templateData);

        } catch (\Exception $e) {
            $this->logger->error('Store listing error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->render('store/partials/error/store-error.html.twig', [
                'message' => 'Unable to load products. Please try again later.',
            ], new Response('', Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * Extract active filters from query parameters.
     */
    private function extractActiveFilters(array $queryParams): array
    {
        $filterKeys = [
            'occasions', 'category', 'types', 'colors', 'fabric', 'texture',
            'sock_size', 'adult_size', 'kid_size', 'brands', 'tag',
        ];

        $activeFilters = [];

        foreach ($filterKeys as $key) {
            if (!empty($queryParams[$key])) {
                $value = $queryParams[$key];
                $activeFilters[$key] = is_array($value) ? $value : [$value];
            }
        }

        return $activeFilters;
    }

    public function search(Request $r, ProductDataRepository $repo): Response
    {
        $q = $r->query;
        $search = $q->has('q') ? $q->get('q') : '';
        $results = [];
        $products = [];

        if (!empty($search)) {
            $products = $repo->instantSearch($search);

            $attributes = $this->staticFilters->getAttributeSet();
            if (in_array($search, $attributes)) {
                $attribute = array_search($search, $attributes);
            } else {
                $attribute = [];
            }

            $options = [
                'keys' => ['name', 'alias', 'fullName'],
                'includeScore' => true,
                'includeMatches' => true,
                'findAllMatches' => true,
                'threshold' => 0.1];

            $fuse = new Fuse($this->$attributes->staticFilters->getAttributeSet(), $options);
            $results = $fuse->search($search);

            return $this->render(
                'store/partials/search.html.twig',
                [
                    'results' => $results,
                    'products' => $products,
                    'brands' => $this->staticFilters->getBrands(),
                ]
            );
        }

        /* Return empty */
        return new Response('');
    }
}
