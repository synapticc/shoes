<?php

// src/Service/StaticFiltersService.php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class StaticFiltersService
{
    private const CACHE_KEY_PREFIX = 'store_filters_';
    private const CACHE_TTL = 86400; // 24 hours
    private const BRAND_SHOW_LIMIT = 5; // Show top 5 brands

    public function __construct(
        private AttributeService $attributeService,
        private CacheInterface $cache,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Get all static filters at once (cached)
     * Uses AttributeService to fetch all filter data.
     */
    public function getStaticFilters(): array
    {
        // dd($this->attributeService->reloadConfiguration());
        // dd($this->clearCache());
        return $this->cache->get(
            self::CACHE_KEY_PREFIX.'all',
            function () {
                $startTime = microtime(true);

                try {
                    // Get all brands once
                    $allBrands = $this->attributeService->getByType('brand');
                    // Split brands into show and hidden
                    $brandSplit = $this->splitBrands($allBrands, self::BRAND_SHOW_LIMIT);

                    // Get individual attribute type
                    $occasion = $this->attributeService->getByType('occasion');
                    $categories = $this->attributeService->getByType('category');
                    $type = $this->attributeService->getByType('type');
                    $sockSizes = $this->attributeService->getByType('sock_size');
                    $adultSizes = $this->attributeService->getByType('adult_size');
                    $kidSizes = $this->attributeService->getByType('kid_size');
                    $color = $this->attributeService->getByType('color');
                    $fabrics = $this->attributeService->getByType('fabric');
                    $textures = $this->attributeService->getByType('texture');
                    $tags = $this->attributeService->getByType('tag');
                    // $brand = $this->attributeService->getByType('brand');
                    $priceRange = $this->getPriceRange();
                    $sorting = $this->attributeService->getByType('sorting');

                    // Combine all sizes
                    $allSizes = $this->mergeSizes($sockSizes, $adultSizes, $kidSizes);

                    // Get form pairs
                    $brandPair = $this->attributeService->getForForm('brand');
                    $occasionPair = $this->attributeService->getForForm('occasion');
                    $typePair = $this->attributeService->getForForm('type');
                    $categoryPair = $this->attributeService->getForForm('category');
                    $colorPair = $this->attributeService->getForForm('color');
                    $fabricPair = $this->attributeService->getForForm('fabric');
                    $texturePair = $this->attributeService->getForForm('texture');
                    $sizePair = $this->mergeSizesForms(
                        $this->attributeService->getForForm('sock_size'),
                        $this->attributeService->getForForm('adult_size'),
                        $this->attributeService->getForForm('kid_size')
                    );

                    $filters = [
                        // Display values
                        'occasion' => $occasion,
                        'categories' => $categories,
                        'type' => $type,
                        'sockSizes' => $sockSizes,
                        'adultSizes' => $adultSizes,
                        'kidSizes' => $kidSizes,
                        'sizes' => $allSizes,
                        'colors' => $color,
                        'fabrics' => $fabrics,
                        'textures' => $textures,
                        'tags' => $tags,
                        'brands' => $allBrands,
                        'brandShow' => $brandSplit['show'],
                        'brandHidden' => $brandSplit['hidden'],
                        'price_range' => $priceRange,
                        'sorting' => $sorting,

                        // Form pairs (for dropdowns, selects, etc.)
                        'brandPair' => $brandPair,
                        'occasionPair' => $occasionPair,
                        'typePair' => $typePair,
                        'categoryPair' => $categoryPair,
                        'colorPair' => $colorPair,
                        'fabricPair' => $fabricPair,
                        'texturePair' => $texturePair,
                        'sizePair' => $sizePair,
                    ];

                    $executionTime = microtime(true) - $startTime;

                    $this->logger->debug('Static filters loaded from AttributeService', [
                        'execution_time_ms' => round($executionTime * 1000, 2),
                        'brands_total' => count($allBrands),                        'brands_shown' => count($brandSplit['show']),                        'brands_hidden' => count($brandSplit['hidden']),
                        'filters_count' => count($filters),
                    ]);

                    return $filters;

                } catch (\Exception $e) {
                    $this->logger->error('Error loading static filters', [
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    dd($e->getMessage());
                    // Return empty structure on error
                    // return $this->getEmptyFiltersStructure();
                }
            },
            self::CACHE_TTL
        );
    }

    /**
     * Get specific filter type.
     */
    public function getFilter(string $type): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX.$type;

        return $this->cache->get(
            $cacheKey,
            function () use ($type) {
                try {
                    return $this->attributeService->getByType($type);
                } catch (\Exception $e) {
                    $this->logger->warning('Error loading filter type', [
                        'type' => $type,
                        'exception' => $e->getMessage(),
                    ]);

                    return [];
                }
            },
            self::CACHE_TTL
        );
    }

    /**
     * Get form pairs for specific type.
     */
    public function getFilterPair(string $type): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX.'pair_'.$type;

        return $this->cache->get(
            $cacheKey,
            function () use ($type) {
                try {
                    return $this->attributeService->getForForm($type);
                } catch (\Exception $e) {
                    $this->logger->warning('Error loading filter pair', [
                        'type' => $type,
                        'exception' => $e->getMessage(),
                    ]);

                    return [];
                }
            },
            self::CACHE_TTL
        );
    }

    /**
     * Merge size arrays (sock + adult + kid).
     */
    private function mergeSizes(array ...$sizeArrays): array
    {
        $merged = [];

        foreach ($sizeArrays as $sizeArray) {
            $merged = array_merge($merged, $sizeArray);
        }

        // Remove duplicates by name
        $unique = [];
        foreach ($merged as $size) {
            $key = $size['name'] ?? uniqid();
            if (!isset($unique[$key])) {
                $unique[$key] = $size;
            }
        }

        return array_values($unique);
    }

    /**
     * Merge size form pairs.
     */
    private function mergeSizesForms(array ...$sizePairs): array
    {
        $merged = [];

        foreach ($sizePairs as $pair) {
            $merged = array_merge($merged, $pair);
        }

        return $merged;
    }

    /**
     * Get empty filters structure for error handling.
     */
    private function getEmptyFiltersStructure(): array
    {
        return [
            'occasion' => [],
            'categories' => [],
            'type' => [],
            'sockSizes' => [],
            'adultSizes' => [],
            'kidSizes' => [],
            'sizes' => [],
            'color' => [],
            'fabrics' => [],
            'textures' => [],
            'tags' => [],
            'brand' => [],
            'price_range' => [],
            'sorting' => [],
            'brandPair' => [],
            'occasionPair' => [],
            'typePair' => [],
            'categoryPair' => [],
            'colorPair' => [],
            'fabricPair' => [],
            'texturePair' => [],
            'sizePair' => [],
        ];
    }

    /**
     * Clear all filter caches.
     */
    public function clearCache(): void
    {
        try {
            // Clear main cache
            $this->cache->delete(self::CACHE_KEY_PREFIX.'all');

            // Clear individual filter caches
            $filterTypes = [
                'occasion',
                'category',
                'type',
                'sock_size',
                'adult_size',
                'kid_size',
                'color',
                'fabric',
                'texture',
                'tag',
                'brand',
                'price_range',
                'sorting',
            ];

            foreach ($filterTypes as $type) {
                $this->cache->delete(self::CACHE_KEY_PREFIX.$type);
                $this->cache->delete(self::CACHE_KEY_PREFIX.'pair_'.$type);
            }

            $this->logger->info('Static filters cache cleared');

        } catch (\Exception $e) {
            $this->logger->warning('Error clearing filters cache', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Refresh cache immediately (for admin updates).
     */
    public function refreshCache(): array
    {
        $this->clearCache();

        return $this->getStaticFilters();
    }

    /**
     * Get occasion filter.
     */
    public function getOccasions(): array
    {
        return $this->getFilter('occasion');
    }

    /**
     * Get occasion as form pairs.
     */
    public function getOccasionPair(): array
    {
        return $this->getFilterPair('occasion');
    }

    /**
     * Get categories filter.
     */
    public function getCategories(): array
    {
        return $this->getFilter('category');
    }

    /**
     * Get categories as form pairs.
     */
    public function getCategoryPair(): array
    {
        return $this->getFilterPair('category');
    }

    /**
     * Get type filter.
     */
    public function getTypes(): array
    {
        return $this->getFilter('type');
    }

    /**
     * Get type as form pairs.
     */
    public function getTypePair(): array
    {
        return $this->getFilterPair('type');
    }

    /**
     * Get color filter.
     */
    public function getColors(): array
    {
        return $this->getFilter('color');
    }

    /**
     * Get color as form pairs.
     */
    public function getColorPair(): array
    {
        return $this->getFilterPair('color');
    }

    /**
     * Get fabrics filter.
     */
    public function getFabrics(): array
    {
        return $this->getFilter('fabric');
    }

    /**
     * Get fabrics as form pairs.
     */
    public function getFabricPair(): array
    {
        return $this->getFilterPair('fabric');
    }

    /**
     * Get textures filter.
     */
    public function getTextures(): array
    {
        return $this->getFilter('texture');
    }

    /**
     * Get textures as form pairs.
     */
    public function getTexturePair(): array
    {
        return $this->getFilterPair('texture');
    }

    /**
     * Get tags filter.
     */
    public function getTags(): array
    {
        return $this->getFilter('tag');
    }

    /**
     * Get brand filter.
     */
    public function getBrands(): array
    {
        return $this->getFilter('brand');
    }

    /**     * Get displayed brands only     */
    public function getBrandsShow(int $limit = 5): array
    {
        try {
            $brands = $this->getFilterSafely('brands');

            return array_slice($brands, 0, $limit);
        } catch (\Exception $e) {
            $this->logger->warning('Error getting brands show', ['exception' => $e->getMessage()]);

            return [];
        }
    }

    /**     * Get hidden brands     */
    public function getBrandsHidden(int $showLimit = 5): array
    {
        try {
            $brands = $this->getFilterSafely('brands');

            return array_slice($brands, $showLimit);
        } catch (\Exception $e) {
            $this->logger->warning('Error getting brands hidden', ['exception' => $e->getMessage()]);

            return [];
        }
    }

    /**     * Split brands into show and hidden     * Recommended: Cleaner and reusable     */
    private function splitBrands(array $brands, int $showLimit = 5): array
    {
        try {
            return [
                'show' => array_slice($brands, 0, $showLimit),
                'hidden' => array_slice($brands, $showLimit)];
        } catch (\Exception $e) {
            $this->logger->warning(
                'Error splitting brands',
                ['exception' => $e->getMessage()]
            );

            return [
                'show' => $brands,
                'hidden' => []];
        }
    }

    /**     * Get brands with split (show/hidden)     * Use this in controller instead     */
    public function getBrandsWithSplit(int $showLimit = 5): array
    {
        try {
            $allBrands = $this->getFilterSafely('brands');

            return $this->splitBrands($allBrands, $showLimit);
        } catch (\Exception $e) {
            $this->logger->warning('Error getting brands with split', ['exception' => $e->getMessage()]);

            return ['show' => [],                'hidden' => []];
        }
    }

    /**
     * Get brand as form pairs.
     */
    public function getBrandPair(): array
    {
        return $this->getFilterPair('brand');
    }

    /**
     * Get sock sizes filter.
     */
    public function getSockSizes(): array
    {
        return $this->getFilter('sock_size');
    }

    /**
     * Get sock sizes as form pairs.
     */
    public function getSockSizesPair(): array
    {
        return $this->getFilterPair('sock_size');
    }

    /**
     * Get adult sizes filter.
     */
    public function getAdultSizes(): array
    {
        return $this->getFilter('adult_size');
    }

    /**
     * Get adult sizes as form pairs.
     */
    public function getAdultSizesPair(): array
    {
        return $this->getFilterPair('adult_size');
    }

    /**
     * Get kid sizes filter.
     */
    public function getKidSizes(): array
    {
        return $this->getFilter('kid_size');
    }

    /**
     * Get kid sizes as form pairs.
     */
    public function getKidSizesPair(): array
    {
        return $this->getFilterPair('kid_size');
    }

    /**
     * Get all sizes combined.
     */
    public function getAllSizes(): array
    {
        return $this->mergeSizes(
            $this->getSockSizes(),
            $this->getAdultSizes(),
            $this->getKidSizes()
        );
    }

    /**
     * Get all sizes as form pairs.
     */
    public function getAllSizesPair(): array
    {
        return $this->mergeSizesForms(
            $this->getSockSizesPair(),
            $this->getAdultSizesPair(),
            $this->getKidSizesPair()
        );
    }

    /**
     * Get price range filter.
     */
    public function getPriceRange(): array
    {
        return $this->getFilter('price_range');
    }

    /**
     * Get sorting options.
     */
    public function getSorting(): array
    {
        return $this->getFilter('sorting');
    }

    /**
     * Get filter by type with fallback.
     */
    public function getByType(string $type, array $fallback = []): array
    {
        try {
            $result = $this->attributeService->getByType($type);

            return !empty($result) ? $result : $fallback;
        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter by type', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }

    public function getAttributeSet(): array
    {
        $attributeSet =
          array_merge(
              $this->getOccasions();
              $this->getCategories();
              $this->getTypes();
              $this->getColors();
              $this->getFabrics();
              $this->getTextures();
              $this->getTags();
              $this->getBrands();
              $this->getSockSizes();
              $this->getAdultSizes();
              $this->getKidSizes();

          );

        return $attributeSet;
    }

    /**
     * Get form pairs with fallback.
     */
    public function getForForm(string $type, bool $keyValue = true, array $fallback = []): array
    {
        try {
            $result = $this->attributeService->getForForm($type, $keyValue);

            return !empty($result) ? $result : $fallback;
        } catch (\Exception $e) {
            $this->logger->warning('Error getting form pairs', [
                'type' => $type,
                'keyValue' => $keyValue,
                'exception' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }

    /**
     * Check if filter type exists.
     */
    public function hasFilter(string $type): bool
    {
        try {
            $filter = $this->attributeService->getByType($type);

            return !empty($filter);
        } catch (\Exception $e) {
            $this->logger->debug('Filter type does not exist', [
                'type' => $type,
            ]);

            return false;
        }
    }

    /**
     * Count items in filter type.
     */
    public function countFilter(string $type): int
    {
        try {
            $filter = $this->attributeService->getByType($type);

            return count($filter);
        } catch (\Exception $e) {
            $this->logger->warning('Error counting filter items', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get filter item by name.
     */
    public function getFilterItemByName(string $type, string $name): ?array
    {
        try {
            $filter = $this->attributeService->getByType($type);

            foreach ($filter as $item) {
                if (($item['name'] ?? null) === $name) {
                    return $item;
                }
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter item by name', [
                'type' => $type,
                'name' => $name,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get filter item by full name.
     */
    public function getFilterItemByFullName(string $type, string $fullName): ?array
    {
        try {
            $filter = $this->attributeService->getByType($type);

            foreach ($filter as $item) {
                if (($item['fullName'] ?? null) === $fullName) {
                    return $item;
                }
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter item by full name', [
                'type' => $type,
                'fullName' => $fullName,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get filter items grouped by attribute.
     */
    public function getFilterGroupedByAttribute(string $type): array
    {
        try {
            $filter = $this->attributeService->getByType($type);
            $grouped = [];

            foreach ($filter as $item) {
                $attribute = $item['attribute'] ?? 'default';
                if (!isset($grouped[$attribute])) {
                    $grouped[$attribute] = [];
                }
                $grouped[$attribute][] = $item;
            }

            return $grouped;

        } catch (\Exception $e) {
            $this->logger->warning('Error grouping filter by attribute', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get paginated filter items.
     */
    public function getFilterPaginated(string $type, int $page = 1, int $perPage = 10): array
    {
        try {
            $filter = $this->attributeService->getByType($type);
            $totalItems = count($filter);
            $totalPages = (int) ceil($totalItems / $perPage);

            // Validate page
            $page = max(1, min($page, $totalPages ?: 1));

            $offset = ($page - 1) * $perPage;
            $items = array_slice($filter, $offset, $perPage, true);

            return [
                'items' => $items,
                'page' => $page,
                'perPage' => $perPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error getting paginated filter', [
                'type' => $type,
                'page' => $page,
                'exception' => $e->getMessage(),
            ]);

            return [
                'items' => [],
                'page' => 1,
                'perPage' => $perPage,
                'totalItems' => 0,
                'totalPages' => 0,
                'hasNextPage' => false,
                'hasPreviousPage' => false,
            ];
        }
    }

    /**
     * Get filter statistics.
     */
    public function getFilterStatistics(): array
    {
        try {
            $filterTypes = [
                'occasion',
                'category',
                'type',
                'sock_size',
                'adult_size',
                'kid_size',
                'color',
                'fabric',
                'texture',
                'tag',
                'brand',
                'price_range',
                'sorting',
            ];

            $stats = [];
            foreach ($filterTypes as $type) {
                $stats[$type] = [
                    'count' => $this->countFilter($type),
                    'exists' => $this->hasFilter($type),
                ];
            }

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error('Error getting filter statistics', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Validate filter value against available options.
     */
    public function validateFilterValue(string $type, string $value): bool
    {
        try {
            $filter = $this->attributeService->getByType($type);

            foreach ($filter as $item) {
                if (($item['name'] ?? null) === $value
                    || ($item['fullName'] ?? null) === $value) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->warning('Error validating filter value', [
                'type' => $type,
                'value' => $value,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate multiple filter values.
     */
    public function validateFilterValues(string $type, array $values): array
    {
        $validated = [
            'valid' => [],
            'invalid' => [],
        ];

        foreach ($values as $value) {
            if ($this->validateFilterValue($type, $value)) {
                $validated['valid'][] = $value;
            } else {
                $validated['invalid'][] = $value;
            }
        }

        return $validated;
    }

    /**
     * Get filter value translation/mapping.
     */
    public function translateFilterValue(string $type, string $value): ?string
    {
        try {
            $item = $this->getFilterItemByName($type, $value);

            if (null === $item) {
                $item = $this->getFilterItemByFullName($type, $value);
            }

            return $item ? ($item['fullName'] ?? $item['name'] ?? null) : null;

        } catch (\Exception $e) {
            $this->logger->warning('Error translating filter value', [
                'type' => $type,
                'value' => $value,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get related filters.
     */
    public function getRelatedFilters(string $type): array
    {
        $related = [];

        // Define relationships between filter type
        $relationships = [
            'sock_size' => ['color', 'fabrics'],
            'adult_size' => ['color', 'fabrics'],
            'kid_size' => ['color', 'fabrics'],
            'color' => ['fabrics', 'textures'],
            'fabrics' => ['textures', 'color'],
            'categories' => ['type', 'occasion'],
            'type' => ['categories', 'occasion'],
            'occasion' => ['categories', 'type'],
        ];

        if (isset($relationships[$type])) {
            foreach ($relationships[$type] as $relatedType) {
                try {
                    $related[$relatedType] = $this->getFilter($relatedType);
                } catch (\Exception $e) {
                    $this->logger->debug('Error getting related filter', [
                        'type' => $type,
                        'relatedType' => $relatedType,
                    ]);
                }
            }
        }

        return $related;
    }

    /**
     * Build filter hierarchy/tree.
     */
    public function buildFilterHierarchy(): array
    {
        try {
            return [
                'sizes' => [
                    'sock_size' => $this->getSockSizes(),
                    'adult_size' => $this->getAdultSizes(),
                    'kid_size' => $this->getKidSizes(),
                ],
                'attributes' => [
                    'color' => $this->getColors(),
                    'fabrics' => $this->getFabrics(),
                    'textures' => $this->getTextures(),
                ],
                'categories' => [
                    'occasion' => $this->getOccasions(),
                    'categories' => $this->getCategories(),
                    'type' => $this->getTypes(),
                ],
                'metadata' => [
                    'brand' => $this->getBrands(),
                    'tags' => $this->getTags(),
                    'price_range' => $this->getPriceRange(),
                    'sorting' => $this->getSorting(),
                ],
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error building filter hierarchy', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Warm up cache (call during cache:warmup command).
     */
    public function warmUpCache(): bool
    {
        try {
            $this->logger->info('Starting cache warmup for filters');

            // Load main filters
            $this->getStaticFilters();

            // Load individual filters
            $this->getOccasions();
            $this->getCategories();
            $this->getTypes();
            $this->getColors();
            $this->getFabrics();
            $this->getTextures();
            $this->getTags();
            $this->getBrands();
            $this->getSockSizes();
            $this->getAdultSizes();
            $this->getKidSizes();
            $this->getPriceRange();
            $this->getSorting();

            // Load form pairs
            $this->getOccasionPair();
            $this->getCategoryPair();
            $this->getTypePair();
            $this->getColorPair();
            $this->getFabricPair();
            $this->getTexturePair();
            $this->getBrandPair();
            $this->getSockSizesPair();
            $this->getAdultSizesPair();
            $this->getKidSizesPair();
            $this->getAllSizesPair();

            $this->logger->info('Cache warmup completed successfully');

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Error during cache warmup', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get filters for API response.
     */
    public function getFiltersForApi(): array
    {
        try {
            $filters = $this->getStaticFilters();

            return [
                'success' => true,
                'data' => [
                    'filters' => $filters,
                    'summary' => $this->getFilterSummary(),
                    'timestamp' => date('Y-m-d H:i:s'),
                ],
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error getting filters for API', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to load filters',
                'data' => $this->getEmptyFiltersStructure(),
            ];
        }
    }

    /**
     * Get filter options for form field.
     */
    public function getFormOptions(string $type): array
    {
        try {
            $pair = $this->getFilterPair($type);

            return [
                'choices' => $pair,
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'required' => false,
                'expanded' => false,
                'multiple' => false,
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error getting form options', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [
                'choices' => [],
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'required' => false,
            ];
        }
    }

    /**
     * Get filter options for multi-select form field.
     */
    public function getFormOptionsMultiple(string $type): array
    {
        $options = $this->getFormOptions($type);
        $options['multiple'] = true;
        $options['expanded'] = true;

        return $options;
    }

    /**     * Get filter breadcrumbs for display     */
    public function getBreadcrumbs(array $activeFilters): array
    {
        $breadcrumbs = [];
        foreach ($activeFilters as $type => $values) {
            if (empty($values)) {
                continue;
            }            $valuesArray = is_array($values) ? $values : [$values];
            foreach ($valuesArray as $value) {
                $item = $this->getFilterItem($type, $value);
                if ($item) {
                    $breadcrumbs[] = ['type' => $type,                        'name' => $item['name'] ?? $value,                        'fullName' => $item['fullName'] ?? $item['name'] ?? $value,                        'label' => ucfirst(str_replace('_', ' ', $type))];
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Get breadcrumb trail for current filters.
     */
    public function getBreadcrumbTrail(array $activeFilters): array
    {
        $breadcrumbs = [];

        foreach ($activeFilters as $type => $values) {
            if (empty($values)) {
                continue;
            }

            // Ensure values is an array
            $valuesArray = is_array($values) ? $values : [$values];

            foreach ($valuesArray as $value) {
                $item = $this->getFilterItemByName($type, $value) ??
                        $this->getFilterItemByFullName($type, $value);

                if ($item) {
                    $breadcrumbs[] = [
                        'type' => $type,
                        'name' => $item['name'] ?? $value,
                        'fullName' => $item['fullName'] ?? $item['name'] ?? $value,
                        'label' => ucfirst(str_replace('_', ' ', $type)),
                    ];
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Check if filter combination is valid.
     */
    public function isValidFilterCombination(array $filters): bool
    {
        try {
            foreach ($filters as $type => $values) {
                if (!$this->hasFilter($type)) {
                    return false;
                }

                $valuesArray = is_array($values) ? $values : [$values];
                $validated = $this->validateFilterValues($type, $valuesArray);

                if (!empty($validated['invalid'])) {
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->warning('Error validating filter combination', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get filter item by name or fullName.
     */
    public function getFilterItem(string $type, string $value): ?array
    {
        try {
            $filter = $this->getFilter($type);

            foreach ($filter as $item) {
                if (($item['name'] ?? null) === $value
                    || ($item['fullName'] ?? null) === $value) {
                    return $item;
                }
            }

            return null;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter item', [
                'type' => $type,
                'value' => $value,
            ]);

            return null;
        }
    }

    /**
     * Search in filter items.
     */
    public function searchInFilter(string $type, string $query): array
    {
        try {
            if (empty($query)) {
                return [];
            }

            $filter = $this->getFilter($type);
            $results = [];
            $query = strtolower($query);

            foreach ($filter as $item) {
                $name = strtolower($item['name'] ?? '');
                $fullName = strtolower($item['fullName'] ?? '');
                $info = strtolower($item['info'] ?? '');

                if (
                    false !== strpos($name, $query)
                    || false !== strpos($fullName, $query)
                    || false !== strpos($info, $query)
                ) {
                    $results[] = $item;
                }
            }

            return $results;

        } catch (\Exception $e) {
            $this->logger->warning('Error searching in filter', [
                'type' => $type,
                'query' => $query,
            ]);

            return [];
        }
    }

    /**
     * Get filter summary (count of each type).
     */
    public function getFilterSummary(): array
    {
        try {
            return [
                'occasion' => $this->countFilter('occasion'),
                'categories' => $this->countFilter('category'),
                'type' => $this->countFilter('type'),
                'color' => $this->countFilter('color'),
                'fabrics' => $this->countFilter('fabric'),
                'textures' => $this->countFilter('texture'),
                'sizes' => count($this->getAllSizes()),
                'brand' => $this->countFilter('brand'),
                'tags' => $this->countFilter('tag'),
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error getting filter summary', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Sanitize filter values (remove invalid ones).
     */
    public function sanitizeFilters(array $filters): array
    {
        try {
            $sanitized = [];

            foreach ($filters as $type => $values) {
                if (!$this->hasFilter($type)) {
                    continue;
                }

                $valuesArray = is_array($values) ? $values : [$values];
                $validValues = [];

                foreach ($valuesArray as $value) {
                    if ($this->validateFilterValue($type, $value)) {
                        $validValues[] = $value;
                    }
                }

                if (!empty($validValues)) {
                    $sanitized[$type] = $validValues;
                }
            }

            return $sanitized;

        } catch (\Exception $e) {
            $this->logger->warning('Error sanitizing filters', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build URL query string from filters.
     */
    public function buildFilterUrl(array $activeFilters, string $baseUrl = ''): string
    {
        try {
            $params = [];

            foreach ($activeFilters as $type => $values) {
                if (empty($values)) {
                    continue;
                }

                $valuesArray = is_array($values) ? $values : [$values];
                $params[$type] = implode(',', $valuesArray);
            }

            $query = http_build_query($params);

            return $baseUrl.($query ? '?'.$query : '');

        } catch (\Exception $e) {
            $this->logger->warning('Error building filter URL', [
                'exception' => $e->getMessage(),
            ]);

            return $baseUrl;
        }
    }

    /**
     * Parse URL query string to filters.
     */
    public function parseFilterUrl(string $queryString): array
    {
        try {
            parse_str($queryString, $params);

            $filters = [];
            foreach ($params as $type => $value) {
                if (is_string($value) && !empty($value)) {
                    $filters[$type] = explode(',', $value);
                }
            }

            return $filters;

        } catch (\Exception $e) {
            $this->logger->warning('Error parsing filter URL', [
                'queryString' => $queryString,
            ]);

            return [];
        }
    }

    /**
     * Get filter safely (with detailed error reporting).
     */
    private function getFilterSafely(string $type): array
    {
        try {
            $this->logger->debug('Attempting to fetch filter', [
                'type' => $type,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            $result = $this->attributeService->getByType($type);

            if (empty($result)) {
                $this->logger->warning('Filter returned empty result', [
                    'type' => $type,
                    'reason' => 'AttributeService returned empty array',
                ]);

                return [];
            }

            $this->logger->debug('Filter loaded successfully', [
                'type' => $type,
                'items_count' => count($result),
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Error loading filter', [
                'type' => $type,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
