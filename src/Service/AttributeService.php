<?php

// src/Service/AttributeService.php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AttributeService
{
    private array $attributeData;
    private array $lookupCache = [];

    public function __construct(
        private CacheInterface $cache,
        private CacheItemPoolInterface $cachePool,
        private string $projectDir,
        private LoggerInterface $logger,
    ) {
        $this->attributeData = require $projectDir.'/config/attributes.php';
    }

    // Attributes that should be sorted alphabetically
    private const SORTABLE_ATTRIBUTES = [
        'brand',
        'type',
        'occasion',
        'category',
        'colors',
        'fabric',
        'texture',
        'tag',
        'job',
        'kid_sizes',
        'adult_sizes',
    ];

    /**
     * Get all attributes of a specific type.
     */
    public function getByType(string $type): array
    {
        return $this->cache->get(
            "attributes.{$type}",
            fn (ItemInterface $item) => $this->buildAttributeArray($type)
        );
    }

    /**
     * Build and sort attribute array.
     */
    private function buildAttributeArray(string $type): array
    {
        try {
            $this->logger->debug('Fetching attribute type', [
                'type' => $type,
                'available_types' => array_keys($this->attributeData),
            ]);

            if (!isset($this->attributeData[$type])) {
                $this->logger->warning('Attribute type not found in config', [
                    'requested_type' => $type,
                    'available_types' => array_keys($this->attributeData),
                ]);

                return [];
            }

            $data = $this->attributeData[$type];

            // Sort alphabetically if this type is in the sortable list
            if (!empty($data) && $this->shouldSort($type)) {
                $data = $this->sortAttributesByName($data);
            }

            $this->logger->debug('Attribute type found', [
                'type' => $type,
                'count' => count($data),
                'sorted' => $this->shouldSort($type),
            ]);

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('Error fetching attribute type', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if attribute type should be sorted.
     */
    private function shouldSort(string $type): bool
    {
        return in_array($type, self::SORTABLE_ATTRIBUTES, true);
    }

    private function sortAttributesByName(array $items): array
    {
        usort($items, function ($a, $b) {
            $nameA = strtolower($a['fullName'] ?? $a['name'] ?? '');
            $nameB = strtolower($b['fullName'] ?? $b['name'] ?? '');

            // Handle special cases like "Dr. Scholl's" vs "Dr Scholls"
            // Remove common prefixes/suffixes for better sorting
            $nameA = $this->normalizeForSort($nameA);
            $nameB = $this->normalizeForSort($nameB);

            return strcmp($nameA, $nameB);
        });

        return $items;
    }

    /**
     * Normalize name for sorting (remove articles, special characters).
     */
    private function normalizeForSort(string $name): string
    {
        // Remove leading articles
        $name = preg_replace('/^(the|a|an)\s+/i', '', $name);

        // Remove special characters but keep spaces
        $name = preg_replace('/[^\w\s]/u', '', $name);

        // Remove extra spaces
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return $name;
    }

    /**     * Convert abbreviated name to full name     */
    public function fullName(string $type, string $abbreviation): ?string
    {
        $cacheKey = "fullname.{$type}.{$abbreviation}";

        return $this->cache->get($cacheKey, fn (ItemInterface $item) => $this->findFullName($type, $abbreviation));
    }

    /**     * Get multiple full names at once     */
    public function fullNames(string $type, array $abbreviations): array
    {
        $result = [];
        foreach ($abbreviations as $abbr) {
            if ($fullName = $this->fullName($type, $abbr)) {
                $result[$abbr] = $fullName;
            }
        }

        return $result;
    }

    /**     * Resolve abbreviated value (handles aliases)     */
    public function resolve(string $type, string $value): ?string
    {
        if (!isset($this->attributeData[$type])) {
            return null;
        }
        // First try direct match
        foreach ($this->attributeData[$type] as $item) {
            if ($item['name'] === $value) {
                return $item['name'];
            }
        }
        // Then try aliases
        foreach ($this->attributeData[$type] as $item) {
            if (in_array($value, $item['alias'] ?? [], true)) {
                return $item['name'];
            }
        }

        return null;
    }

    /**     * Get as key-value pairs for forms     */
    public function getForForm(string $type, bool $keyValue = true): array
    {
        $cacheKey = "form.{$type}.".($keyValue ? 'kv' : 'vk');

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($type, $keyValue) {
            $result = [];
            foreach ($this->attributeData[$type] ?? [] as $attr) {
                if ($keyValue) {
                    $result[$attr['fullName']] = $attr['name'];
                } else {
                    $result[$attr['name']] = $attr['fullName'];
                }
            }

            return $result;
        });
    }

    /**     * Get sorted raw data     */
    public function getRaw(string $type): array
    {
        return $this->cache->get("raw.{$type}", fn (ItemInterface $item) => $this->sortByName($this->attributeData[$type] ?? []));
    }

    /**     * Special handling for sizes based on category     */
    public function getSizesByType(string $category, string $productType): array
    {
        if ('socks' === $productType) {
            return $this->getForForm('sock_size');
        }

        $type = 'kids' === $category ? 'kid_size' : 'adult_size';

        return $this->getForForm($type);
    }

    /**     * Get price ranges     */
    public function getPriceRanges(): array
    {
        return $this->cache->get('price_ranges', fn (ItemInterface $item) => $this->attributeData['price_ranges'] ?? []);
    }

    /**     * Get slider options     */
    public function getSliderOptions(string $sliderType): array
    {
        return $this->attributeData["slider_{$sliderType}"] ?? [];
    }

    /**     * Get all available attribute types     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->attributeData);
    }

    /**     * Get raw data for a specific type     */
    public function getData(string $type): array
    {
        return $this->attributeData[$type] ?? [];
    }

    /**     * Convert colors with hyphens to full names (e.g., 'blk-rd' => 'Black | Red')     */
    public function getColorFullNames(string $colorText): string
    {
        if (str_contains($colorText, '-')) {
            $colors = [];
            foreach (explode('-', $colorText) as $color) {
                if ($fullName = $this->fullName('colors', $color)) {
                    $colors[] = $fullName;
                }
            }

            return implode(' | ', $colors);
        }

        return $this->fullName('colors', $colorText) ?? $colorText;
    }

    /**
     * Get attribute by name.
     */
    public function getAttributeByName(string $type, string $name): ?array
    {
        $attributes = $this->getByType($type);

        foreach ($attributes as $attr) {
            if (($attr['name'] ?? null) === $name || ($attr['fullName'] ?? null) === $name) {
                return $attr;
            }
        }

        return null;
    }

    /**     * Get multiple attributes with full names (useful for product display)     */
    public function enrichProduct(array $product): array
    {
        $enriched = $product;
        // Enrich single values
        foreach (['brand', 'category', 'type'] as $key) {
            if (isset($product[$key])) {
                $enriched["{$key}_full"] = $this->fullName($key, $product[$key]) ?? $this->fullName($key, $product[$key]);
            }
        }
        // Enrich array values
        foreach (['occasion', 'fabric', 'texture', 'tag'] as $key) {
            if (isset($product[$key])) {
                // Check for string before decoding.
                if (is_string($product[$key])) {
                    $enriched[$key] = json_decode($product[$key], true) ?? [];
                }

                $values = is_array($product[$key]) ? $product[$key] : json_decode($product[$key], true);
                // dd($product,$key,$values);
                if (is_array($values)) {
                    $fullNames = [];
                    foreach ($values as $value) {
                        // dd($key, $value);
                        if ($fullName = $this->fullName($key, $value)) {
                            $fullNames[$value] = $fullName;
                        }
                    }
                    // dd('fullNames',$fullNames);
                    $enriched["{$key}_full_set"] = $fullNames;
                    $enriched["{$key}_full"] = implode(' | ', $fullNames);
                }
            }
        }

        // Enrich colors
        if (isset($product['color'])) {
            $enriched['colors_full'] = $this->getColorFullNames($product['color']);
            $enriched['colors_set'] = explode('-', $product['color']);
        }

        return $enriched;
    }

    // ============ Private Helpers ============

    private function findFullName(string $type, string $abbreviation): ?string
    {
        if (!isset($this->attributeData[$type])) {
            return null;
        }        foreach ($this->attributeData[$type] as $item) {
            if ($item['name'] === $abbreviation) {
                return $item['fullName'];
            }
        }

        return null;
    }

    private function sortByName(array $items): array
    {
        $names = array_column($items, 'name');
        array_multisort($names, SORT_ASC, $items);

        return $items;
    }

    /**     * Reload configuration from file and clear all related caches     */
    public function reloadConfiguration(): void
    {
        // Reload from file
        $this->attributeData = require $this->projectDir.'/config/attributes.php';
        // Clear all attribute caches
        $this->clearAllCaches();
    }

    // /**     * Clear all attribute-related caches     */
    // public function clearAllCaches(): void
    // {
    //     $patterns = ['attributes',            'fullname',            'form',            'raw',            'price_ranges'];
    //     foreach ($patterns as $pattern) {
    //         // For Symfony Cache, you need to manually clear related items
    //         $this->cachePool->deleteItems($this->getKeysMatchingPattern($pattern));
    //     }
    // }

    /**     * Get cache keys matching a pattern     */
    private function getKeysMatchingPattern(string $pattern): array
    {
        $keys = [];
        // Note: This is a simplified approach. For production, consider using Redis with SCAN
        $patterns = ['attributes' => ['attributes.sizes', 'attributes.brands', 'attributes.colors', 'attributes.types', 'attributes.occasions', 'attributes.fabrics', 'attributes.textures', 'attributes.tags'],            'fullname' => [],
            // Would need tracking of all possible keys
            'form' => [],
            // Would need tracking of all possible keys
        ];

        return $patterns[$pattern] ?? [];
    }

    /**
     * Count attributes of a type.
     */
    public function countByType(string $type): int
    {
        return count($this->getByType($type));
    }

    /**
     * Check if attribute exists.
     */
    public function existsByType(string $type, string $value): bool
    {
        $attributes = $this->getByType($type);

        foreach ($attributes as $attr) {
            if (($attr['name'] ?? null) === $value || ($attr['fullName'] ?? null) === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search in attributes.
     */
    public function searchByType(string $type, string $query): array
    {
        $attributes = $this->getByType($type);
        $query = strtolower($query);
        $results = [];

        foreach ($attributes as $attr) {
            $name = strtolower($attr['name'] ?? '');
            $fullName = strtolower($attr['fullName'] ?? '');
            $info = strtolower($attr['info'] ?? '');

            if (false !== strpos($name, $query)
                || false !== strpos($fullName, $query)
                || false !== strpos($info, $query)) {
                $results[] = $attr;
            }
        }

        return $results;
    }

    /**
     * Get all attribute types.
     */
    public function getAllTypes(): array
    {
        return array_keys($this->attributeData);
    }

    /**
     * Get sortable attribute types.
     */
    public function getSortableTypes(): array
    {
        return self::SORTABLE_ATTRIBUTES;
    }

    /**
     * Clear cache for specific type.
     */
    public function clearCacheForType(string $type): void
    {
        try {
            $this->cachePool->deleteItem("attributes.{$type}");
            $this->cachePool->deleteItem("form.{$type}.kv");
            $this->cachePool->deleteItem("form.{$type}.vk");

            $this->logger->debug('Cache cleared for attribute type', ['type' => $type]);
        } catch (\Exception $e) {
            $this->logger->warning('Error clearing cache', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all attribute caches.
     */
    public function clearAllCaches(): void
    {
        try {
            foreach ($this->getAllTypes() as $type) {
                $this->clearCacheForType($type);
            }

            $this->logger->info('All attribute caches cleared');
        } catch (\Exception $e) {
            $this->logger->warning('Error clearing all caches', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get attribute with info/details.
     */
    public function getAttributeWithInfo(string $type, string $name): ?array
    {
        return $this->getAttributeByName($type, $name);
    }

    /**
     * Get attributes by multiple names.
     */
    public function getAttributesByNames(string $type, array $names): array
    {
        $attributes = $this->getByType($type);
        $results = [];

        foreach ($names as $name) {
            foreach ($attributes as $attr) {
                if (($attr['name'] ?? null) === $name) {
                    $results[] = $attr;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Get paginated attributes.
     */
    public function getPaginatedByType(string $type, int $page = 1, int $perPage = 10): array
    {
        $attributes = $this->getByType($type);
        $totalItems = count($attributes);
        $totalPages = (int) ceil($totalItems / $perPage);

        $offset = ($page - 1) * $perPage;
        $items = array_slice($attributes, $offset, $perPage);

        return [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'hasNextPage' => $page < $totalPages,
            'hasPreviousPage' => $page > 1,
        ];
    }

    /**
     * Filter attributes by attribute field.
     */
    public function filterByAttribute(string $type, string $attributeName, string $value): array
    {
        $attributes = $this->getByType($type);

        return array_filter($attributes, function ($attr) use ($attributeName, $value) {
            return ($attr[$attributeName] ?? null) === $value;
        });
    }

    /**
     * Get attribute statistics.
     */
    public function getStatistics(string $type): array
    {
        $attributes = $this->getByType($type);

        return [
            'total' => count($attributes),
            'hasBrand' => isset($attributes[0]['brand']),
            'hasAlias' => isset($attributes[0]['alias']),
            'hasInfo' => isset($attributes[0]['info']),
            'hasFullName' => isset($attributes[0]['fullName']),
        ];
    }

    /**
     * Validate attribute exists before using.
     */
    public function validate(string $type, string $name): bool
    {
        if (!in_array($type, $this->getAllTypes(), true)) {
            return false;
        }

        return $this->existsByType($type, $name);
    }
}
