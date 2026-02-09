<?php

// src/Service/PriceFilterService.php

namespace App\Service;

use Psr\Log\LoggerInterface;

class PriceFilterService
{
    private const DEFAULT_MIN_PRICE = 500;
    private const DEFAULT_MAX_PRICE = 25000;
    private const DEFAULT_ORDER = 'nameAsc';

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Process and normalize price filter with validation
     * Most frequently used method.
     */
    public function processPriceFilter(array $filter): array
    {
        try {
            $price = $filter['price'] ?? [];

            // Initialize with defaults
            $price['min'] = $this->validatePrice(
                $price['min'] ?? self::DEFAULT_MIN_PRICE,
                self::DEFAULT_MIN_PRICE
            );
            $price['max'] = $this->validatePrice(
                $price['max'] ?? self::DEFAULT_MAX_PRICE,
                self::DEFAULT_MAX_PRICE
            );
            $price['order'] = $this->validateSortOrder(
                $price['order'] ?? self::DEFAULT_ORDER
            );

            // Override with price_range if provided
            if (!empty($filter['price_range']) && is_array($filter['price_range'])) {
                [$min, $max] = $this->extractPriceRange($filter['price_range']);
                $price['min'] = $min;
                $price['max'] = $max;
            }

            // Ensure min <= max
            if ($price['min'] > $price['max']) {
                [$price['min'], $price['max']] = [$price['max'], $price['min']];
            }

            return $price;

        } catch (\Exception $e) {
            $this->logger->warning('Error processing price filter', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'min' => self::DEFAULT_MIN_PRICE,
                'max' => self::DEFAULT_MAX_PRICE,
                'order' => self::DEFAULT_ORDER,
            ];
        }
    }

    /**
     * Validate and sanitize price value.
     */
    private function validatePrice($value, int $default): int
    {
        try {
            $price = (int) $value;

            return ($price > 0) ? $price : $default;
        } catch (\Exception $e) {
            $this->logger->warning('Error validating price', [
                'value' => $value,
                'default' => $default,
            ]);

            return $default;
        }
    }

    /**
     * Extract min/max from price range array.
     */
    private function extractPriceRange(array $priceRange): array
    {
        try {
            $prices = [];

            foreach ($priceRange as $rangeValue) {
                if (is_string($rangeValue)) {
                    $parts = explode('_', $rangeValue);
                    foreach ($parts as $part) {
                        $price = (int) trim($part);
                        if ($price > 0) {
                            $prices[] = $price;
                        }
                    }
                }
            }

            if (empty($prices)) {
                return [self::DEFAULT_MIN_PRICE, self::DEFAULT_MAX_PRICE];
            }

            sort($prices);

            return [reset($prices), end($prices)];

        } catch (\Exception $e) {
            $this->logger->warning('Error extracting price range', [
                'exception' => $e->getMessage(),
            ]);

            return [self::DEFAULT_MIN_PRICE, self::DEFAULT_MAX_PRICE];
        }
    }

    /**
     * Get valid sorting options.
     */
    public function getValidSortOrders(): array
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
     * Validate sort order.
     */
    public function validateSortOrder(string $order): string
    {
        try {
            $validOrders = array_keys($this->getValidSortOrders());

            if (!in_array($order, $validOrders, true)) {
                $this->logger->warning('Invalid sort order provided', [
                    'provided' => $order,
                    'default' => self::DEFAULT_ORDER,
                ]);

                return self::DEFAULT_ORDER;
            }

            return $order;

        } catch (\Exception $e) {
            $this->logger->warning('Error validating sort order', [
                'order' => $order,
                'exception' => $e->getMessage(),
            ]);

            return self::DEFAULT_ORDER;
        }
    }

    /**
     * Get default price range.
     */
    public function getDefaultPriceRange(): array
    {
        return [
            'min' => self::DEFAULT_MIN_PRICE,
            'max' => self::DEFAULT_MAX_PRICE,
        ];
    }

    /**
     * Get default sort order.
     */
    public function getDefaultSortOrder(): string
    {
        return self::DEFAULT_ORDER;
    }

    /**
     * Build price range options for filter display.
     */
    public function buildPriceRangeOptions(): array
    {
        return [
            ['min' => 0, 'max' => 500, 'label' => '$0 - $500'],
            ['min' => 500, 'max' => 1000, 'label' => '$500 - $1,000'],
            ['min' => 1000, 'max' => 5000, 'label' => '$1,000 - $5,000'],
            ['min' => 5000, 'max' => 10000, 'label' => '$5,000 - $10,000'],
            ['min' => 10000, 'max' => 25000, 'label' => '$10,000 - $25,000'],
            ['min' => 25000, 'max' => PHP_INT_MAX, 'label' => '$25,000+'],
        ];
    }

    /**
     * Validate price is within range.
     */
    public function isValidPrice(int $price, int $minPrice, int $maxPrice): bool
    {
        try {
            return $price >= $minPrice && $price <= $maxPrice;
        } catch (\Exception $e) {
            $this->logger->warning('Error validating price range', [
                'price' => $price,
                'min' => $minPrice,
                'max' => $maxPrice,
            ]);

            return false;
        }
    }

    /**
     * Format price for display.
     */
    public function formatPrice(int $price, string $currency = 'USD'): string
    {
        try {
            $currencySymbols = [
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'JPY' => '¥',
            ];

            $symbol = $currencySymbols[$currency] ?? '$';

            return $symbol.number_format($price, 0, '.', ',');

        } catch (\Exception $e) {
            $this->logger->warning('Error formatting price', [
                'price' => $price,
                'currency' => $currency,
            ]);

            return '$'.$price;
        }
    }

    /**
     * Parse price string to integer.
     */
    public function parsePrice(string $priceString): int
    {
        try {
            // Remove currency symbols and non-numeric characters
            $price = (int) preg_replace('/[^0-9]/', '', $priceString);

            return max(0, $price);

        } catch (\Exception $e) {
            $this->logger->warning('Error parsing price string', [
                'priceString' => $priceString,
            ]);

            return 0;
        }
    }

    /**
     * Calculate discount amount and percentage.
     */
    public function calculateDiscount(int $originalPrice, int $discountedPrice): array
    {
        try {
            if ($originalPrice <= 0 || $discountedPrice < 0) {
                return [
                    'amount' => 0,
                    'percentage' => 0,
                    'savings' => 0,
                ];
            }

            $savings = $originalPrice - $discountedPrice;
            $percentage = ($savings / $originalPrice) * 100;

            return [
                'amount' => $savings,
                'percentage' => round($percentage, 2),
                'savings' => $this->formatPrice($savings),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating discount', [
                'originalPrice' => $originalPrice,
                'discountedPrice' => $discountedPrice,
            ]);

            return [
                'amount' => 0,
                'percentage' => 0,
                'savings' => 0,
            ];
        }
    }

    /**
     * Get price statistics from products.
     */
    public function getPriceStatistics(array $products): array
    {
        try {
            if (empty($products)) {
                return [
                    'min' => 0,
                    'max' => 0,
                    'avg' => 0,
                    'median' => 0,
                    'count' => 0,
                ];
            }

            $prices = [];
            foreach ($products as $product) {
                $price = (int) ($product['price'] ?? 0);
                if ($price > 0) {
                    $prices[] = $price;
                }
            }

            if (empty($prices)) {
                return [
                    'min' => 0,
                    'max' => 0,
                    'avg' => 0,
                    'median' => 0,
                    'count' => 0,
                ];
            }

            sort($prices);
            $count = count($prices);
            $sum = array_sum($prices);

            // Calculate median
            $median = 0 === $count % 2
                ? (($prices[$count / 2 - 1] + $prices[$count / 2]) / 2)
                : $prices[floor($count / 2)];

            return [
                'min' => min($prices),
                'max' => max($prices),
                'avg' => (int) ($sum / $count),
                'median' => (int) $median,
                'count' => $count,
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating price statistics', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'median' => 0,
                'count' => 0,
            ];
        }
    }

    /**
     * Get price tier (budget, mid, premium, luxury).
     */
    public function getPriceTier(int $price): string
    {
        try {
            if ($price < 500) {
                return 'budget';
            } elseif ($price < 1000) {
                return 'mid';
            } elseif ($price < 5000) {
                return 'premium';
            }

            return 'luxury';


        } catch (\Exception $e) {
            $this->logger->warning('Error determining price tier', [
                'price' => $price,
            ]);

            return 'unknown';
        }
    }

    /**
     * Get price tier label.
     */
    public function getPriceTierLabel(int $price): string
    {
        $tiers = [
            'budget' => 'Budget',
            'mid' => 'Mid Range',
            'premium' => 'Premium',
            'luxury' => 'Luxury',
        ];

        $tier = $this->getPriceTier($price);

        return $tiers[$tier] ?? 'Unknown';
    }

    /**
     * Check if price is on sale (discounted).
     */
    public function isOnSale(int $originalPrice, int $currentPrice): bool
    {
        try {
            return $originalPrice > 0 && $currentPrice > 0 && $currentPrice < $originalPrice;
        } catch (\Exception $e) {
            $this->logger->warning('Error checking if price is on sale', [
                'originalPrice' => $originalPrice,
                'currentPrice' => $currentPrice,
            ]);

            return false;
        }
    }

    /**
     * Calculate total price with tax.
     */
    public function calculateWithTax(int $price, float $taxRate = 0.0): array
    {
        try {
            if ($price < 0 || $taxRate < 0) {
                return [
                    'subtotal' => $price,
                    'tax_amount' => 0,
                    'total' => $price,
                    'tax_rate' => $taxRate,
                ];
            }

            $taxAmount = (int) ($price * $taxRate);
            $total = $price + $taxAmount;

            return [
                'subtotal' => $price,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'tax_rate' => $taxRate,
                'subtotal_formatted' => $this->formatPrice($price),
                'tax_formatted' => $this->formatPrice($taxAmount),
                'total_formatted' => $this->formatPrice($total),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating price with tax', [
                'price' => $price,
                'taxRate' => $taxRate,
            ]);

            return [
                'subtotal' => $price,
                'tax_amount' => 0,
                'total' => $price,
                'tax_rate' => $taxRate,
            ];
        }
    }

    /**
     * Build price filter URL parameters.
     */
    public function buildPriceFilterUrl(int $minPrice, int $maxPrice, string $sortOrder = ''): string
    {
        try {
            $params = [
                'price' => [
                    'min' => $minPrice,
                    'max' => $maxPrice,
                ],
            ];

            if (!empty($sortOrder)) {
                $params['price']['order'] = $this->validateSortOrder($sortOrder);
            }

            return http_build_query($params);

        } catch (\Exception $e) {
            $this->logger->warning('Error building price filter URL', [
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'sortOrder' => $sortOrder,
            ]);

            return '';
        }
    }

    /**
     * Get price range label.
     */
    public function getPriceRangeLabel(int $minPrice, int $maxPrice, string $currency = 'USD'): string
    {
        try {
            $minFormatted = $this->formatPrice($minPrice, $currency);
            $maxFormatted = $this->formatPrice($maxPrice, $currency);

            return "$minFormatted - $maxFormatted";

        } catch (\Exception $e) {
            $this->logger->warning('Error getting price range label', [
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
            ]);

            return '';
        }
    }

    /**
     * Get price comparison.
     */
    public function comparePrices(int $price1, int $price2): array
    {
        try {
            $difference = abs($price1 - $price2);
            $percentDifference = $price1 > 0 ? round(($difference / $price1) * 100, 2) : 0;
            $cheaper = $price1 > $price2 ? 'price2' : 'price1';

            return [
                'difference' => $difference,
                'difference_formatted' => $this->formatPrice($difference),
                'percent_difference' => $percentDifference,
                'cheaper' => $cheaper,
                'price1' => $price1,
                'price2' => $price2,
                'price1_formatted' => $this->formatPrice($price1),
                'price2_formatted' => $this->formatPrice($price2),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error comparing prices', [
                'price1' => $price1,
                'price2' => $price2,
            ]);

            return [
                'difference' => 0,
                'percent_difference' => 0,
                'cheaper' => null,
            ];
        }
    }

    /**
     * Get price tiers with ranges.
     */
    public function getPriceTiers(): array
    {
        return [
            [
                'tier' => 'budget',
                'label' => 'Budget',
                'min' => 0,
                'max' => 500,
            ],
            [
                'tier' => 'mid',
                'label' => 'Mid Range',
                'min' => 500,
                'max' => 1000,
            ],
            [
                'tier' => 'premium',
                'label' => 'Premium',
                'min' => 1000,
                'max' => 5000,
            ],
            [
                'tier' => 'luxury',
                'label' => 'Luxury',
                'min' => 5000,
                'max' => PHP_INT_MAX,
            ],
        ];
    }

    /**
     * Check if price is in tier.
     */
    public function isPriceInTier(int $price, string $tier): bool
    {
        try {
            $tiers = $this->getPriceTiers();

            foreach ($tiers as $tierData) {
                if ($tierData['tier'] === $tier) {
                    return $price >= $tierData['min'] && $price < $tierData['max'];
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->warning('Error checking price tier', [
                'price' => $price,
                'tier' => $tier,
            ]);

            return false;
        }
    }

    /**
     * Validate price range.
     */
    public function validatePriceRange(int $minPrice, int $maxPrice): bool
    {
        try {
            return $minPrice >= 0 && $maxPrice >= $minPrice;
        } catch (\Exception $e) {
            $this->logger->warning('Error validating price range', [
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
            ]);

            return false;
        }
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(string $currency = 'USD'): string
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
            'MXN' => '$',
        ];

        return $currencySymbols[$currency] ?? '$';
    }

    /**
     * Round price to nearest value.
     */
    public function roundPrice(int $price, int $roundTo = 100): int
    {
        try {
            if ($roundTo <= 0) {
                return $price;
            }

            return (int) round($price / $roundTo) * $roundTo;

        } catch (\Exception $e) {
            $this->logger->warning('Error rounding price', [
                'price' => $price,
                'roundTo' => $roundTo,
            ]);

            return $price;
        }
    }

    /**
     * Get price brackets for display.
     */
    public function getPriceBrackets(): array
    {
        return [
            ['min' => 0, 'max' => 500],
            ['min' => 500, 'max' => 1000],
            ['min' => 1000, 'max' => 2500],
            ['min' => 2500, 'max' => 5000],
            ['min' => 5000, 'max' => 10000],
            ['min' => 10000, 'max' => 25000],
            ['min' => 25000, 'max' => PHP_INT_MAX],
        ];
    }

    /**
     * Get products in price range.
     */
    public function getProductsInRange(array $products, int $minPrice, int $maxPrice): array
    {
        try {
            return array_filter(
                $products,
                fn ($product) => $this->isValidPrice(
                    (int) ($product['price'] ?? 0),
                    $minPrice,
                    $maxPrice
                )
            );

        } catch (\Exception $e) {
            $this->logger->warning('Error filtering products by price range', [
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
            ]);

            return [];
        }
    }

    /**
     * Sort products by price.
     */
    public function sortByPrice(array $products, string $order = 'asc'): array
    {
        try {
            $order = strtolower($order);

            if (!in_array($order, ['asc', 'desc'], true)) {
                $order = 'asc';
            }

            usort($products, function ($a, $b) use ($order) {
                $priceA = (int) ($a['price'] ?? 0);
                $priceB = (int) ($b['price'] ?? 0);

                $comparison = $priceA <=> $priceB;

                return 'desc' === $order ? -$comparison : $comparison;
            });

            return $products;

        } catch (\Exception $e) {
            $this->logger->warning('Error sorting products by price', [
                'order' => $order,
            ]);

            return $products;
        }
    }

    /**
     * Calculate average price.
     */
    public function getAveragePrice(array $products): int
    {
        try {
            if (empty($products)) {
                return 0;
            }

            $prices = [];
            foreach ($products as $product) {
                $price = (int) ($product['price'] ?? 0);
                if ($price > 0) {
                    $prices[] = $price;
                }
            }

            if (empty($prices)) {
                return 0;
            }

            return (int) (array_sum($prices) / count($prices));

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating average price', [
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get price summary.
     */
    public function getPriceSummary(array $products): array
    {
        try {
            $stats = $this->getPriceStatistics($products);

            return [
                'min_price' => $stats['min'],
                'min_price_formatted' => $this->formatPrice($stats['min']),
                'max_price' => $stats['max'],
                'max_price_formatted' => $this->formatPrice($stats['max']),
                'avg_price' => $stats['avg'],
                'avg_price_formatted' => $this->formatPrice($stats['avg']),
                'median_price' => $stats['median'],
                'median_price_formatted' => $this->formatPrice($stats['median']),
                'product_count' => $stats['count'],
                'price_range' => $this->getPriceRangeLabel($stats['min'], $stats['max']),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error getting price summary', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'min_price' => 0,
                'max_price' => 0,
                'avg_price' => 0,
                'median_price' => 0,
                'product_count' => 0,
            ];
        }
    }

    /**
     * Get best deal from products.
     */
    public function getBestDeal(array $products): ?array
    {
        try {
            if (empty($products)) {
                return null;
            }

            $bestPrice = PHP_INT_MAX;
            $bestProduct = null;

            foreach ($products as $product) {
                $price = (int) ($product['price'] ?? 0);
                if ($price > 0 && $price < $bestPrice) {
                    $bestPrice = $price;
                    $bestProduct = $product;
                }
            }

            return $bestProduct;

        } catch (\Exception $e) {
            $this->logger->warning('Error finding best deal', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Apply bulk discount.
     */
    public function applyBulkDiscount(int $price, int $quantity, array $discountTiers = []): array
    {
        try {
            // Default discount tiers
            if (empty($discountTiers)) {
                $discountTiers = [
                    ['quantity' => 1, 'discount' => 0],
                    ['quantity' => 5, 'discount' => 5],
                    ['quantity' => 10, 'discount' => 10],
                    ['quantity' => 20, 'discount' => 15],
                ];
            }

            $discount = 0;
            foreach ($discountTiers as $tier) {
                if ($quantity >= $tier['quantity']) {
                    $discount = $tier['discount'];
                }
            }

            $discountAmount = (int) ($price * $discount / 100);
            $finalPrice = $price - $discountAmount;

            return [
                'original_price' => $price,
                'quantity' => $quantity,
                'discount_percent' => $discount,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice,
                'original_price_formatted' => $this->formatPrice($price),
                'discount_amount_formatted' => $this->formatPrice($discountAmount),
                'final_price_formatted' => $this->formatPrice($finalPrice),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error applying bulk discount', [
                'price' => $price,
                'quantity' => $quantity,
            ]);

            return [
                'original_price' => $price,
                'quantity' => $quantity,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'final_price' => $price,
            ];
        }
    }

    /**
     * Get price change percentage.
     */
    public function getPriceChangePercentage(int $oldPrice, int $newPrice): float
    {
        try {
            if ($oldPrice <= 0) {
                return 0.0;
            }

            return round((($newPrice - $oldPrice) / $oldPrice) * 100, 2);

        } catch (\Exception $e) {
            $this->logger->warning('Error calculating price change percentage', [
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
            ]);

            return 0.0;
        }
    }

    /**
     * Get price trend.
     */
    public function getPriceTrend(int $oldPrice, int $newPrice): string
    {
        try {
            if ($newPrice > $oldPrice) {
                return 'up';
            } elseif ($newPrice < $oldPrice) {
                return 'down';
            }

            return 'stable';


        } catch (\Exception $e) {
            $this->logger->warning('Error determining price trend', [
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
            ]);

            return 'unknown';
        }
    }

    /**
     * Get price breakdown for display.
     */
    public function getPriceBreakdown(int $subtotal, int $tax = 0, int $shipping = 0, int $discount = 0): array
    {
        try {
            $total = $subtotal + $tax + $shipping - $discount;

            return [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => max(0, $total),
                'subtotal_formatted' => $this->formatPrice($subtotal),
                'tax_formatted' => $this->formatPrice($tax),
                'shipping_formatted' => $this->formatPrice($shipping),
                'discount_formatted' => $this->formatPrice($discount),
                'total_formatted' => $this->formatPrice(max(0, $total)),
            ];

        } catch (\Exception $e) {
            $this->logger->warning('Error getting price breakdown', [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
            ]);

            return [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => max(0, $subtotal + $tax + $shipping - $discount),
            ];
        }
    }

    /**
     * Check if price difference is significant.
     */
    public function isSignificantDifference(int $price1, int $price2, float $percentThreshold = 10.0): bool
    {
        try {
            if ($price1 <= 0) {
                return false;
            }

            $percentDifference = abs(($price2 - $price1) / $price1) * 100;

            return $percentDifference >= $percentThreshold;

        } catch (\Exception $e) {
            $this->logger->warning('Error checking significant difference', [
                'price1' => $price1,
                'price2' => $price2,
            ]);

            return false;
        }
    }
}
