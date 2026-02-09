<?php

// src/Service/MaxItemsService.php

namespace App\Service;

use App\Repository\User\Settings\MaxItemsRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MaxItemsService
{
    private const CACHE_KEY = 'max_items_listing';
    private const CACHE_TTL = 86400; // 24 hours
    private const DEFAULT_LISTING = 12;
    private const MIN_LISTING = 5;
    private const MAX_LISTING = 75;

    private ?int $cachedValue = null;

    public function __construct(
        private MaxItemsRepository $maxItemsRepo,
        private CacheInterface $cache,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Get maximum items for listing (cached).
     */
    public function listing(): int
    {
        // Check in-memory cache first (fastest)
        if (null !== $this->cachedValue) {
            return $this->cachedValue;
        }

        try {
            // Try to get from Symfony cache (second fastest)
            $value = $this->cache->get(self::CACHE_KEY, function () {
                return $this->maxItemsRepo->getListingCount();
            });

            $this->cachedValue = $value;

            $this->logger->debug('Max items retrieved', [
                'value' => $value,
                'source' => 'cache',
            ]);

            return $value;

        } catch (\Exception $e) {
            $this->logger->error('Error getting max items, using default', [
                'exception' => $e->getMessage(),
                'default' => self::DEFAULT_LISTING,
            ]);

            return self::DEFAULT_LISTING;
        }
    }

    /**
     * Update listing items count and clear cache.
     */
    public function updateListing(int $listing): bool
    {
        try {
            // Validate range
            if ($listing < self::MIN_LISTING || $listing > self::MAX_LISTING) {
                throw new \InvalidArgumentException(sprintf('Listing must be between %d and %d', self::MIN_LISTING, self::MAX_LISTING));
            }

            // Update in database
            $success = $this->maxItemsRepo->updateListing($listing);

            if ($success) {
                // Clear caches
                $this->clearCache();
                $this->cachedValue = $listing;

                $this->logger->info('Max items listing updated', [
                    'value' => $listing,
                ]);
            }

            return $success;

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Invalid listing value', [
                'value' => $listing,
                'message' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error updating listing', [
                'value' => $listing,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear all caches.
     */
    public function clearCache(): void
    {
        try {
            $this->cache->delete(self::CACHE_KEY);
            $this->cachedValue = null;
            $this->logger->debug('Max items cache cleared');
        } catch (\Exception $e) {
            $this->logger->warning('Error clearing cache', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Refresh cache immediately.
     */
    public function refreshCache(): int
    {
        $this->clearCache();

        return $this->listing();
    }

    /**
     * Validate listing value.
     */
    public function validate(int $value): bool
    {
        return $value >= self::MIN_LISTING && $value <= self::MAX_LISTING;
    }

    /**
     * Get validation constraints.
     */
    public function getConstraints(): array
    {
        return [
            'min' => self::MIN_LISTING,
            'max' => self::MAX_LISTING,
            'default' => self::DEFAULT_LISTING,
        ];
    }
}
