<?php

// src/Service/ProductListingService.php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductListingService
{
    public function __construct(
        private FilterAggregationService $filterAggregation,
        private PriceFilterService $priceFilter,
        private PaginatorInterface $paginator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Prepare all data for listing template
     * Most frequently used method.
     */
    public function prepareListing(
        $productData,
        ParameterBag $query,
        array $staticFilters,
        int $maxItems,
    ): array {
        try {
            $startTime = microtime(true);

            // Extract filter parameters
            $filter = $query->all();
            $price = $this->priceFilter->processPriceFilter($filter);
            $page = $query->getInt('page', 1);

            // Convert paginated data to array if needed
            $itemsForCounting = $this->convertToArray($productData);

            // Aggregate filter counts from current page items
            $counts = $this->filterAggregation->aggregateFilters($itemsForCounting);

            $executionTime = microtime(true) - $startTime;

            $this->logger->debug('Listing preparation completed', [
                'page' => $page,
                'items_per_page' => $maxItems,
                'execution_time_ms' => round($executionTime * 1000, 2),
            ]);

            return [
                'productData' => $productData,
                'get' => array_merge($filter, ['price' => $price]),
                'count' => $counts,
                'page' => $page,
                'maxItems' => $maxItems,
                ...$staticFilters,
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error preparing listing', [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get pagination metadata for template.
     */
    public function getPaginationMetadata($pagination): array
    {
        try {
            // Handle array pagination
            if (is_array($pagination)) {
                return [
                    'currentPage' => 1,
                    'totalPages' => 1,
                    'totalItems' => count($pagination),
                    'itemsPerPage' => count($pagination),
                    'hasNextPage' => false,
                    'hasPreviousPage' => false,
                    'nextPage' => null,
                    'previousPage' => null,
                    'startItem' => 1,
                    'endItem' => count($pagination),
                ];
            }

            // Handle KnpPaginator pagination
            $totalItems = $pagination->getTotalItemCount();
            $itemsPerPage = $pagination->getItemNumberPerPage();
            $currentPage = $pagination->getCurrentPageNumber();
            $totalPages = (int) ceil($totalItems / $itemsPerPage);

            return [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'hasNextPage' => $currentPage < $totalPages,
                'hasPreviousPage' => $currentPage > 1,
                'nextPage' => $currentPage < $totalPages ? $currentPage + 1 : null,
                'previousPage' => $currentPage > 1 ? $currentPage - 1 : null,
                'startItem' => (($currentPage - 1) * $itemsPerPage) + 1,
                'endItem' => min($currentPage * $itemsPerPage, $totalItems),
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error getting pagination metadata', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'currentPage' => 1,
                'totalPages' => 1,
                'itemsPerPage' => 0,
                'totalItems' => 0,
                'hasNextPage' => false,
                'hasPreviousPage' => false,
                'nextPage' => null,
                'previousPage' => null,
                'startItem' => 0,
                'endItem' => 0,
            ];
        }
    }

    /**
     * Get page range for pagination controls.
     */
    public function getPageRange($pagination, int $adjacentPages = 2): array
    {
        try {
            // Handle array pagination
            if (is_array($pagination)) {
                return [[
                    'number' => 1,
                    'isCurrent' => true,
                ]];
            }

            // Handle KnpPaginator pagination
            $currentPage = $pagination->getCurrentPageNumber();
            $totalItems = $pagination->getTotalItemCount();
            $itemsPerPage = $pagination->getItemNumberPerPage();
            $totalPages = (int) ceil($totalItems / $itemsPerPage);

            $startPage = max(1, $currentPage - $adjacentPages);
            $endPage = min($totalPages, $currentPage + $adjacentPages);

            $pages = [];
            for ($i = $startPage; $i <= $endPage; ++$i) {
                $pages[] = [
                    'number' => $i,
                    'isCurrent' => $i === $currentPage,
                ];
            }

            return $pages;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting page range', [
                'exception' => $e->getMessage(),
            ]);

            return [[
                'number' => 1,
                'isCurrent' => true,
            ]];
        }
    }

    /**
     * Get sort options for dropdown.
     */
    public function getSortOptions(): array
    {
        return [
            'nameAsc' => 'Name: A to Z',
            'nameDesc' => 'Name: Z to A',
            'priceAsc' => 'Price: Low to High',
            'priceDesc' => 'Price: High to Low',
            'newest' => 'Newest First',
            'popular' => 'Most Popular',
        ];
    }

    /**
     * Get product by ID.
     */
    public function getProductById(array $products, int $productId): ?array
    {
        foreach ($products as $product) {
            if (($product['id'] ?? null) === $productId) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Convert pagination object to array.
     */
    private function convertToArray($pagination): array
    {
        if (is_array($pagination)) {
            return $pagination;
        }

        // Convert Paginator result to array
        $items = [];
        foreach ($pagination as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
