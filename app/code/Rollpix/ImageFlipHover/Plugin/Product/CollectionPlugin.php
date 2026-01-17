<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Plugin\Product;

use Rollpix\ImageFlipHover\Helper\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class CollectionPlugin
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
