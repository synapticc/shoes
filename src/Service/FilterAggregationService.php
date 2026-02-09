<?php

// src/Service/FilterAggregationService.php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class FilterAggregationService
{
    public function __construct(
        private LoggerInterface $logger,
        private ?Stopwatch $stopwatch = null,
    ) {
    }

    /**
     * Counts from product data
     * Single pass through products - O(n) complexity.
     */
    public function aggregateFilters(array $productData): array
    {
        // Measure how long this piece of  code takes to execute.
        // $startTime = microtime(true);

        /* 1. Start the timer.
          'filter_aggregation' = name in the timeline
          'analytics' = category (colors the bar in the timeline)
        */
        if ($this->stopwatch) {
            $this->stopwatch->start('filter_aggregation', 'analytics');
        }

        $count = [
            'brand' => [],
            'category' => [],
            'occasion' => [],
            'type' => [],
            'fabric' => [],
            'texture' => [],
            'color' => [],
        ];

        // Exit early
        if (empty($productData)) {
            // Good practice: Stop timer to avoid "open" events in the profiler.
            if ($this->stopwatch && $this->stopwatch->isStarted('filter_aggregation')) {
                $this->stopwatch->stop('filter_aggregation');
            }

            return $count;
        }

        try {
            // Single pass through products
            foreach ($productData as $product) {
                // dd($product,$count['brand'], $product['brand'],$product['brand'] ?? null);
                // Single values
                $this->incrementCount($count['brand'], $product['brand'] ?? null);
                $this->incrementCount($count['category'], $product['category'] ?? null);
                $this->incrementCount($count['type'], $product['type'] ?? null);

                // // Array values
                $this->incrementCountArray($count['occasion'], $product['occasion'] ?? []);
                // dd($product,$count, $product['fabric'] );
                $this->incrementCountArray($count['fabric'], $product['fabric'] ?? []);
                // dd($product,$count);
                $this->incrementCountArray($count['texture'], $product['texture'] ?? []);
                // dd($product,$count);
                $this->incrementCountArray($count['color'], $product['colors_set'] ?? []);
            }
            // dd($count);
            // Sort counts by frequency (descending)
            foreach ($count as &$filterCounts) {
                if (is_array($filterCounts) && !empty($filterCounts)) {
                    arsort($filterCounts, SORT_NUMERIC);
                }
            }

            unset($filterCounts); // <--- SAFETY TIP

            // $endTime = microtime(true);
            // $executionTime = $endTime - $startTime;

            // 2. Stop the timer and get the event info
            if ($this->stopwatch) {
                $event = $this->stopwatch->stop('filter_aggregation');
                $durationMs = $event->getDuration(); // Returns int/float in milliseconds
            } else {
                $durationMs = 0;
            }

            $this->logger->debug('Filter aggregation completed', [
                'products' => count($productData),
                'execution_time_ms' => round($executionTime * 1000, 2),
            ]);

            return $count;

        } catch (\Exception $e) {
            $this->logger->error('Error during filter aggregation', [
                'exception' => $e->getMessage(),
            ]);

            return $count;
        }
    }

    /**
     * Aggregate filters for specific types only
     * Optimized for partial aggregation.
     */
    public function aggregateFiltersByType(array $productData, array $types): array
    {
        $count = [];

        foreach ($types as $type) {
            $count[$type] = [];
        }

        if (empty($productData)) {
            return $count;
        }

        try {
            foreach ($productData as $product) {
                foreach ($types as $type) {
                    // Map filter types to product fields
                    $fieldName = $this->getFieldName($type);
                    $value = $product[$fieldName] ?? null;

                    if ('occasion' === $type || 'fabrics' === $type || 'textures' === $type || 'color' === $type) {
                        $this->incrementCountArray($count[$type], is_array($value) ? $value : ($value ? [$value] : []));
                    } else {
                        $this->incrementCount($count[$type], $value);
                    }
                }
            }

            // Sort by frequency
            foreach ($count as &$filterCounts) {
                if (!empty($filterCounts)) {
                    arsort($filterCounts, SORT_NUMERIC);
                }
            }

            return $count;

        } catch (\Exception $e) {
            $this->logger->warning('Error aggregating filters by type', [
                'types' => $types,
                'exception' => $e->getMessage(),
            ]);

            return $count;
        }
    }

    /**
     * Get filter count for specific type.
     */
    public function getFilterCount(array $productData, string $type): array
    {
        try {
            $counts = [];
            $fieldName = $this->getFieldName($type);

            foreach ($productData as $product) {
                $value = $product[$fieldName] ?? null;

                if ($this->isArrayField($type)) {
                    $this->incrementCountArray($counts, is_array($value) ? $value : ($value ? [$value] : []));
                } else {
                    $this->incrementCount($counts, $value);
                }
            }

            arsort($counts, SORT_NUMERIC);

            return $counts;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter count', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get top N items from filter.
     */
    public function getTopFilters(array $productData, string $type, int $limit = 10): array
    {
        try {
            $counts = $this->getFilterCount($productData, $type);

            return array_slice($counts, 0, $limit, true);

        } catch (\Exception $e) {
            $this->logger->warning('Error getting top filters', [
                'type' => $type,
                'limit' => $limit,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if filter value exists in products.
     */
    public function filterValueExists(array $productData, string $type, string $value): bool
    {
        try {
            $counts = $this->getFilterCount($productData, $type);

            return isset($counts[$value]);

        } catch (\Exception $e) {
            $this->logger->warning('Error checking filter value', [
                'type' => $type,
                'value' => $value,
            ]);

            return false;
        }
    }

    /**
     * Get filter statistics.
     */
    public function getFilterStatistics(array $productData): array
    {
        try {
            $aggregated = $this->aggregateFilters($productData);
            $stats = [];

            foreach ($aggregated as $type => $counts) {
                $total = array_sum($counts);
                $stats[$type] = [
                    'total' => $total,
                    'unique' => count($counts),
                    'max' => !empty($counts) ? max($counts) : 0,
                    'min' => !empty($counts) ? min($counts) : 0,
                    'avg' => !empty($counts) ? (int) ($total / count($counts)) : 0,
                ];
            }

            return $stats;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter statistics', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Filter products by multiple criteria.
     */
    public function filterProducts(array $productData, array $filters): array
    {
        try {
            if (empty($filters)) {
                return $productData;
            }

            return array_filter($productData, function ($product) use ($filters) {
                foreach ($filters as $type => $values) {
                    if (empty($values)) {
                        continue;
                    }

                    $fieldName = $this->getFieldName($type);
                    $productValue = $product[$fieldName] ?? null;

                    if ($this->isArrayField($type)) {
                        $productValues = is_array($productValue) ? $productValue : ($productValue ? [$productValue] : []);
                        $valuesArray = is_array($values) ? $values : [$values];

                        // Check if any product value matches filter values
                        $match = false;
                        foreach ($productValues as $pv) {
                            if (in_array($pv, $valuesArray, true)) {
                                $match = true;
                                break;
                            }
                        }

                        if (!$match) {
                            return false;
                        }
                    } else {
                        $valuesArray = is_array($values) ? $values : [$values];
                        if (!in_array($productValue, $valuesArray, true)) {
                            return false;
                        }
                    }
                }

                return true;
            });

        } catch (\Exception $e) {
            $this->logger->warning('Error filtering products', [
                'exception' => $e->getMessage(),
            ]);

            return $productData;
        }
    }

    /**
     * Get products with specific filter value.
     */
    public function getProductsByFilter(array $productData, string $type, string $value): array
    {
        try {
            $fieldName = $this->getFieldName($type);

            return array_filter($productData, function ($product) use ($fieldName, $value) {
                $productValue = $product[$fieldName] ?? null;

                if (is_array($productValue)) {
                    return in_array($value, $productValue, true);
                }

                return $productValue === $value;
            });

        } catch (\Exception $e) {
            $this->logger->warning('Error getting products by filter', [
                'type' => $type,
                'value' => $value,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Exclude products with specific filter value.
     */
    public function excludeByFilter(array $productData, string $type, string $value): array
    {
        try {
            $fieldName = $this->getFieldName($type);

            return array_filter($productData, function ($product) use ($fieldName, $value) {
                $productValue = $product[$fieldName] ?? null;

                if (is_array($productValue)) {
                    return !in_array($value, $productValue, true);
                }

                return $productValue !== $value;
            });

        } catch (\Exception $e) {
            $this->logger->warning('Error excluding by filter', [
                'type' => $type,
                'value' => $value,
                'exception' => $e->getMessage(),
            ]);

            return $productData;
        }
    }

    /**
     * Get filter suggestions based on current filters.
     */
    public function getSuggestions(array $productData, array $currentFilters, string $type, int $limit = 5): array
    {
        try {
            // Filter products by current filters
            $filtered = $this->filterProducts($productData, $currentFilters);

            // Get filter counts from filtered products
            $counts = $this->getFilterCount($filtered, $type);

            // Return top suggestions
            return array_slice($counts, 0, $limit, true);

        } catch (\Exception $e) {
            $this->logger->warning('Error getting suggestions', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get available filters for current product set.
     */
    public function getAvailableFilters(array $productData): array
    {
        try {
            $filterTypes = ['brand', 'category', 'type', 'occasion', 'fabrics', 'textures', 'color'];
            $available = [];

            foreach ($filterTypes as $type) {
                $counts = $this->getFilterCount($productData, $type);
                if (!empty($counts)) {
                    $available[$type] = array_keys($counts);
                }
            }

            return $available;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting available filters', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if filter combination is valid (has products).
     */
    public function isValidFilterCombination(array $productData, array $filters): bool
    {
        try {
            $filtered = $this->filterProducts($productData, $filters);

            return !empty($filtered);

        } catch (\Exception $e) {
            $this->logger->warning('Error validating filter combination', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get filter facets (counts for each value).
     */
    public function getFacets(array $productData, string $type): array
    {
        try {
            $counts = $this->getFilterCount($productData, $type);
            $facets = [];

            foreach ($counts as $value => $count) {
                $facets[] = [
                    'value' => $value,
                    'count' => $count,
                    'label' => ucfirst(str_replace('_', ' ', $value)),
                ];
            }

            return $facets;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting facets', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get breadcrumb data from active filters.
     */
    public function getBreadcrumbs(array $activeFilters): array
    {
        try {
            $breadcrumbs = [];

            foreach ($activeFilters as $type => $values) {
                if (empty($values)) {
                    continue;
                }

                $valuesArray = is_array($values) ? $values : [$values];

                foreach ($valuesArray as $value) {
                    $breadcrumbs[] = [
                        'type' => $type,
                        'value' => $value,
                        'label' => ucfirst(str_replace('_', ' ', $type)),
                        'display' => ucfirst(str_replace('_', ' ', $value)),
                    ];
                }
            }

            return $breadcrumbs;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting breadcrumbs', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build filter URL from active filters.
     */
    public function buildFilterUrl(array $activeFilters, int $page = 1): string
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

            if ($page > 1) {
                $params['page'] = $page;
            }

            return http_build_query($params);

        } catch (\Exception $e) {
            $this->logger->warning('Error building filter URL', [
                'exception' => $e->getMessage(),
            ]);

            return '';
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
                if ('page' === $type || 'sort' === $type) {
                    continue;
                }

                if (is_string($value) && !empty($value)) {
                    $filters[$type] = explode(',', $value);
                }
            }

            return $filters;

        } catch (\Exception $e) {
            $this->logger->warning('Error parsing filter URL', [
                'queryString' => $queryString,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get products count by filter.
     */
    public function getProductCountByFilter(array $productData, string $type, string $value): int
    {
        try {
            $filtered = $this->getProductsByFilter($productData, $type, $value);

            return count($filtered);

        } catch (\Exception $e) {
            $this->logger->warning('Error getting product count', [
                'type' => $type,
                'value' => $value,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Compare two filter sets.
     */
    public function compareFilters(array $filters1, array $filters2): array
    {
        try {
            $comparison = [
                'added' => [],
                'removed' => [],
                'unchanged' => [],
            ];

            // Find added and unchanged
            foreach ($filters2 as $type => $values) {
                $values2Array = is_array($values) ? $values : [$values];
                $values1Array = is_array($filters1[$type] ?? []) ? $filters1[$type] : [$filters1[$type] ?? null];

                foreach ($values2Array as $value) {
                    if (in_array($value, $values1Array, true)) {
                        $comparison['unchanged'][] = ['type' => $type, 'value' => $value];
                    } else {
                        $comparison['added'][] = ['type' => $type, 'value' => $value];
                    }
                }
            }

            // Find removed
            foreach ($filters1 as $type => $values) {
                $values1Array = is_array($values) ? $values : [$values];
                $values2Array = is_array($filters2[$type] ?? []) ? $filters2[$type] : [$filters2[$type] ?? null];

                foreach ($values1Array as $value) {
                    if (!in_array($value, $values2Array, true)) {
                        $comparison['removed'][] = ['type' => $type, 'value' => $value];
                    }
                }
            }

            return $comparison;

        } catch (\Exception $e) {
            $this->logger->warning('Error comparing filters', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'added' => [],
                'removed' => [],
                'unchanged' => [],
            ];
        }
    }

    /**
     * Merge multiple filter sets.
     */
    public function mergeFilters(array ...$filterSets): array
    {
        try {
            $merged = [];

            foreach ($filterSets as $filters) {
                foreach ($filters as $type => $values) {
                    if (!isset($merged[$type])) {
                        $merged[$type] = [];
                    }

                    $valuesArray = is_array($values) ? $values : [$values];
                    $merged[$type] = array_merge($merged[$type], $valuesArray);
                }
            }

            // Remove duplicates
            foreach ($merged as &$values) {
                $values = array_unique($values);
            }

            return $merged;

        } catch (\Exception $e) {
            $this->logger->warning('Error merging filters', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get filter impact (how many products will be removed).
     */
    public function getFilterImpact(array $productData, array $currentFilters, string $newType, string $newValue): array
    {
        try {
            $currentCount = count($this->filterProducts($productData, $currentFilters));

            $newFilters = $currentFilters;
            if (!isset($newFilters[$newType])) {
                $newFilters[$newType] = [];
            }

            $newFiltersArray = is_array($newFilters[$newType]) ? $newFilters[$newType] : [$newFilters[$newType]];
            $newFiltersArray[] = $newValue;
            $newFilters[$newType] = $newFiltersArray;

            $newCount = count($this->filterProducts($productData, $newFilters));

            return [
                'before' => $currentCount,
                'after' => $newCount,
                'removed' => $currentCount - $newCount,
                'percentChange' => $currentCount > 0 ? round((($newCount - $currentCount) / $currentCount) * 100, 2) : 0,
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating filter impact', [
                'type' => $newType,
                'value' => $newValue,
                'exception' => $e->getMessage(),
            ]);

            return [
                'before' => 0,
                'after' => 0,
                'removed' => 0,
                'percentChange' => 0,
            ];
        }
    }

    /**
     * Increment count for single value
     * $counts array is passed by reference,
     * so that it updates directly the original $count.
     */
    private function incrementCount(array &$counts, ?string $value): void
    {
        if (null !== $value && '' !== $value && '0' !== $value) {
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
    }

    /**
     * Increment count for array of values.
     */
    private function incrementCountArray(array &$counts, array $values): void
    {
        foreach ($values as $value) {
            if (null !== $value && '' !== $value && '0' !== $value) {
                $counts[$value] = ($counts[$value] ?? 0) + 1;
            }
        }
    }

    /**
     * Map filter type to product field name.
     */
    private function getFieldName(string $type): string
    {
        $mapping = [
            'brand' => 'brand',
            'category' => 'category',
            'type' => 'type',
            'occasion' => 'occasion',
            'fabrics' => 'fabrics',
            'fabric' => 'fabrics',
            'textures' => 'textures',
            'texture' => 'textures',
            'color' => 'colors_set',
            'colors' => 'colors_set',
        ];

        return $mapping[$type] ?? $type;
    }

    /**
     * Check if filter type is array field.
     */
    private function isArrayField(string $type): bool
    {
        $arrayFields = ['occasion', 'fabrics', 'fabric', 'textures', 'texture', 'color', 'colors'];

        return in_array($type, $arrayFields, true);
    }

    /**
     * Validate filter type.
     */
    private function isValidFilterType(string $type): bool
    {
        $validTypes = [
            'brand', 'category', 'type', 'occasion',
            'fabrics', 'fabric', 'textures', 'texture',
            'color', 'colors', 'colors_set',
        ];

        return in_array($type, $validTypes, true);
    }

    /**
     * Get all valid filter types.
     */
    public function getValidFilterTypes(): array
    {
        return [
            'brand' => 'Brand',
            'category' => 'Category',
            'type' => 'Type',
            'occasion' => 'Occasion',
            'fabrics' => 'Fabrics',
            'textures' => 'Textures',
            'color' => 'Color',
        ];
    }

    /**
     * Sanitize filters (remove invalid types/values).
     */
    public function sanitizeFilters(array $filters): array
    {
        try {
            $sanitized = [];

            foreach ($filters as $type => $values) {
                if (!$this->isValidFilterType($type)) {
                    continue;
                }

                $valuesArray = is_array($values) ? $values : [$values];
                $cleanValues = [];

                foreach ($valuesArray as $value) {
                    if (is_string($value) && !empty($value)) {
                        $cleanValues[] = trim($value);
                    }
                }

                if (!empty($cleanValues)) {
                    $sanitized[$type] = $cleanValues;
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
     * Export filter data as JSON.
     */
    public function exportAsJson(array $productData): string
    {
        try {
            $aggregated = $this->aggregateFilters($productData);
            $stats = $this->getFilterStatistics($productData);

            $export = [
                'timestamp' => date('Y-m-d H:i:s'),
                'total_products' => count($productData),
                'filters' => $aggregated,
                'statistics' => $stats,
            ];

            return json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            $this->logger->error('Error exporting filters as JSON', [
                'exception' => $e->getMessage(),
            ]);

            return '{}';
        }
    }

    /**
     * Get filter distribution (percentage of products with each filter value).
     */
    public function getFilterDistribution(array $productData, string $type): array
    {
        try {
            if (empty($productData)) {
                return [];
            }

            $counts = $this->getFilterCount($productData, $type);
            $total = count($productData);
            $distribution = [];

            foreach ($counts as $value => $count) {
                $distribution[$value] = [
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 2),
                ];
            }

            return $distribution;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter distribution', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get most and least common filter values.
     */
    public function getFilterExtremes(array $productData, string $type): array
    {
        try {
            $counts = $this->getFilterCount($productData, $type);

            if (empty($counts)) {
                return [
                    'most_common' => null,
                    'least_common' => null,
                ];
            }

            $sorted = $counts;
            arsort($sorted);

            return [
                'most_common' => [
                    'value' => key($sorted),
                    'count' => reset($sorted),
                ],
                'least_common' => [
                    'value' => key(array_slice($sorted, -1, 1, true)),
                    'count' => end($sorted),
                ],
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter extremes', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [
                'most_common' => null,
                'least_common' => null,
            ];
        }
    }

    /**
     * Check product diversity (how many different filters it has).
     */
    public function getProductDiversity(array $product): int
    {
        try {
            $diversity = 0;
            $filterFields = ['brand', 'category', 'type', 'occasion', 'fabrics', 'textures', 'colors_set'];

            foreach ($filterFields as $field) {
                $value = $product[$field] ?? null;

                if (null !== $value && '' !== $value) {
                    if (is_array($value)) {
                        $diversity += count($value);
                    } else {
                        ++$diversity;
                    }
                }
            }

            return $diversity;

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating product diversity', [
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get dataset summary.
     */
    public function getSummary(array $productData): array
    {
        try {
            if (empty($productData)) {
                return [
                    'total_products' => 0,
                    'filter_types' => [],
                    'total_filters' => 0,
                    'avg_filters_per_product' => 0,
                ];
            }

            $aggregated = $this->aggregateFilters($productData);
            $totalFilters = 0;
            $filterTypes = [];

            foreach ($aggregated as $type => $counts) {
                $count = count($counts);
                if ($count > 0) {
                    $filterTypes[$type] = $count;
                    $totalFilters += $count;
                }
            }

            // Calculate average filters per product
            $totalProductFilters = 0;
            foreach ($productData as $product) {
                $totalProductFilters += $this->getProductDiversity($product);
            }

            $avgFiltersPerProduct = count($productData) > 0
                ? round($totalProductFilters / count($productData), 2)
                : 0;

            return [
                'total_products' => count($productData),
                'filter_types' => $filterTypes,
                'total_unique_filters' => $totalFilters,
                'avg_filters_per_product' => $avgFiltersPerProduct,
            ];

        } catch (\Exception $e) {
            $this->logger->error('Error getting summary', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'total_products' => 0,
                'filter_types' => [],
                'total_filters' => 0,
                'avg_filters_per_product' => 0,
            ];
        }
    }

    /**
     * Get filter recommendations (which filters to add next).
     */
    public function getFilterRecommendations(array $productData, array $currentFilters, int $limit = 5): array
    {
        try {
            // Get current filtered products
            $filtered = $this->filterProducts($productData, $currentFilters);

            if (empty($filtered)) {
                return [];
            }

            $recommendations = [];
            $filterTypes = $this->getValidFilterTypes();

            // Get available filters in remaining products
            foreach (array_keys($filterTypes) as $type) {
                $counts = $this->getFilterCount($filtered, $type);

                // Only recommend filters not already applied
                $currentTypeFilters = $currentFilters[$type] ?? [];
                $currentArray = is_array($currentTypeFilters) ? $currentTypeFilters : [$currentTypeFilters];

                foreach ($counts as $value => $count) {
                    if (!in_array($value, $currentArray, true)) {
                        $recommendations[] = [
                            'type' => $type,
                            'value' => $value,
                            'count' => $count,
                            'percentage' => round(($count / count($filtered)) * 100, 2),
                        ];
                    }
                }
            }

            // Sort by count descending
            usort($recommendations, fn ($a, $b) => $b['count'] <=> $a['count']);

            return array_slice($recommendations, 0, $limit);

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter recommendations', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Detect filter conflicts (filters that result in no products).
     */
    public function detectConflicts(array $productData, array $filters): array
    {
        try {
            $conflicts = [];
            $filterTypes = array_keys($filters);

            // Test each filter combination
            foreach ($filterTypes as $type) {
                $values = $filters[$type];
                $valuesArray = is_array($values) ? $values : [$values];

                foreach ($valuesArray as $value) {
                    $testFilters = $filters;
                    // Remove this specific value
                    $testArray = is_array($testFilters[$type]) ? $testFilters[$type] : [$testFilters[$type]];
                    $testArray = array_filter($testArray, fn ($v) => $v !== $value);

                    if (empty($testArray)) {
                        unset($testFilters[$type]);
                    } else {
                        $testFilters[$type] = $testArray;
                    }

                    // Check if any products match without this filter
                    $filtered = $this->filterProducts($productData, $testFilters);

                    if (empty($filtered)) {
                        $conflicts[] = [
                            'type' => $type,
                            'value' => $value,
                            'reason' => 'No products match when this filter is removed',
                        ];
                    }
                }
            }

            return $conflicts;

        } catch (\Exception $e) {
            $this->logger->warning('Error detecting conflicts', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get filter correlation (which filters often appear together).
     */
    public function getFilterCorrelation(array $productData, string $type1, string $type2): array
    {
        try {
            $fieldName1 = $this->getFieldName($type1);
            $fieldName2 = $this->getFieldName($type2);

            $correlation = [];

            foreach ($productData as $product) {
                $value1 = $product[$fieldName1] ?? null;
                $value2 = $product[$fieldName2] ?? null;

                if (null === $value1 || null === $value2) {
                    continue;
                }

                $values1 = is_array($value1) ? $value1 : [$value1];
                $values2 = is_array($value2) ? $value2 : [$value2];

                foreach ($values1 as $v1) {
                    foreach ($values2 as $v2) {
                        $key = $v1.'|'.$v2;
                        $correlation[$key] = ($correlation[$key] ?? 0) + 1;
                    }
                }
            }

            // Sort by frequency
            arsort($correlation);

            return $correlation;

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating filter correlation', [
                'type1' => $type1,
                'type2' => $type2,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get filter hierarchy/dependencies.
     */
    public function getFilterHierarchy(array $productData): array
    {
        try {
            $hierarchy = [];
            $filterTypes = array_keys($this->getValidFilterTypes());

            // Build relationships
            for ($i = 0; $i < count($filterTypes); ++$i) {
                for ($j = $i + 1; $j < count($filterTypes); ++$j) {
                    $type1 = $filterTypes[$i];
                    $type2 = $filterTypes[$j];

                    $correlation = $this->getFilterCorrelation($productData, $type1, $type2);

                    if (!empty($correlation)) {
                        $hierarchy[$type1][$type2] = count($correlation);
                    }
                }
            }

            return $hierarchy;

        } catch (\Exception $e) {
            $this->logger->warning('Error getting filter hierarchy', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Performance analysis - get slowest filter operations.
     */
    public function analyzePerformance(array $productData): array
    {
        try {
            $analysis = [];

            // Test each filter type
            foreach ($this->getValidFilterTypes() as $type => $label) {
                $startTime = microtime(true);
                $this->getFilterCount($productData, $type);
                $executionTime = microtime(true) - $startTime;

                $analysis[$type] = [
                    'execution_time_ms' => round($executionTime * 1000, 4),
                    'products_processed' => count($productData),
                ];
            }

            // Sort by execution time
            uasort($analysis, fn ($a, $b) => $b['execution_time_ms'] <=> $a['execution_time_ms']);

            return $analysis;

        } catch (\Exception $e) {
            $this->logger->error('Error analyzing performance', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get data quality report.
     */
    public function getQualityReport(array $productData): array
    {
        try {
            $report = [
                'total_products' => count($productData),
                'missing_filters' => [],
                'empty_filters' => [],
                'data_completeness' => [],
            ];

            if (empty($productData)) {
                return $report;
            }

            $filterFields = ['brand', 'category', 'type', 'occasion', 'fabrics', 'textures', 'colors_set'];
            $totalProducts = count($productData);

            foreach ($filterFields as $field) {
                $missing = 0;
                $empty = 0;

                foreach ($productData as $product) {
                    $value = $product[$field] ?? null;

                    if (null === $value) {
                        ++$missing;
                    } elseif ('' === $value || (is_array($value) && empty($value))) {
                        ++$empty;
                    }
                }

                $completeness = (($totalProducts - $missing - $empty) / $totalProducts) * 100;

                $report['missing_filters'][$field] = [
                    'count' => $missing,
                    'percentage' => round(($missing / $totalProducts) * 100, 2),
                ];

                $report['empty_filters'][$field] = [
                    'count' => $empty,
                    'percentage' => round(($empty / $totalProducts) * 100, 2),
                ];

                $report['data_completeness'][$field] = round($completeness, 2);
            }

            return $report;

        } catch (\Exception $e) {
            $this->logger->error('Error generating quality report', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
