<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Plugin\Product;

use Rollpix\ImageFlipHover\Helper\Config;
use Rollpix\ImageFlipHover\Model\ImageFlipService;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class CollectionPlugin
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ImageFlipService
     */
    private ImageFlipService $imageFlipService;

    /**
     * @var bool Guard against re-entrant calls
     */
    private bool $isPreloading = false;

    /**
     * @param Config $config
     * @param ImageFlipService $imageFlipService
     */
    public function __construct(Config $config, ImageFlipService $imageFlipService)
    {
        $this->config = $config;
        $this->imageFlipService = $imageFlipService;
    }

    /**
     * Add flip image attribute to product collection
     *
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false): array
    {
        if (!$this->config->isEnabled()) {
            return [$printQuery, $logQuery];
        }

        if ($subject->isLoaded()) {
            return [$printQuery, $logQuery];
        }

        $primaryRole = $this->config->getPrimaryRole();
        $fallbackRole = $this->config->getFallbackRole();

        // Add the primary role attribute to the collection
        if ($primaryRole && !$this->hasAttribute($subject, $primaryRole)) {
            try {
                $subject->addAttributeToSelect($primaryRole);
            } catch (\Exception $e) {
                // Attribute might not exist, ignore
            }
        }

        // Add the fallback role attribute to the collection
        if ($fallbackRole && $fallbackRole !== $primaryRole && !$this->hasAttribute($subject, $fallbackRole)) {
            try {
                $subject->addAttributeToSelect($fallbackRole);
            } catch (\Exception $e) {
                // Attribute might not exist, ignore
            }
        }

        return [$printQuery, $logQuery];
    }

    /**
     * After collection loads, preload gallery images for slider mode
     *
     * @param Collection $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterLoad(Collection $subject, Collection $result): Collection
    {
        if ($this->isPreloading) {
            return $result;
        }

        $this->isPreloading = true;
        try {
            if (!$this->config->isEnabled() || !$this->config->isSliderMode()) {
                return $result;
            }

            // Extract IDs directly from loaded items to avoid triggering load() again
            $productIds = [];
            foreach ($result->getItems() as $item) {
                $productIds[] = (int) $item->getId();
            }

            if (!empty($productIds)) {
                $this->imageFlipService->preloadGalleryBatch(
                    $productIds,
                    $this->config->getMaxImages()
                );
            }
        } finally {
            $this->isPreloading = false;
        }

        return $result;
    }

    /**
     * Check if collection already has attribute selected
     *
     * @param Collection $collection
     * @param string $attribute
     * @return bool
     */
    private function hasAttribute(Collection $collection, string $attribute): bool
    {
        $select = $collection->getSelect();
        $selectString = (string) $select;

        return strpos($selectString, $attribute) !== false;
    }
}
