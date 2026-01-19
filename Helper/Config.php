<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_ENABLED = 'rollpix_imageflip/general/enabled';
    private const XML_PATH_PRIMARY_ROLE = 'rollpix_imageflip/general/primary_role';
    private const XML_PATH_FALLBACK_ROLE = 'rollpix_imageflip/general/fallback_role';
    private const XML_PATH_ANIMATION_TYPE = 'rollpix_imageflip/general/animation_type';
    private const XML_PATH_ANIMATION_SPEED = 'rollpix_imageflip/general/animation_speed';
    private const XML_PATH_DESKTOP_ONLY = 'rollpix_imageflip/general/desktop_only';
    private const XML_PATH_CATEGORY_PAGE = 'rollpix_imageflip/locations/category_page';
    private const XML_PATH_WIDGET_PRODUCTS = 'rollpix_imageflip/locations/widget_products';
    private const XML_PATH_SEARCH_RESULTS = 'rollpix_imageflip/locations/search_results';
    private const XML_PATH_RELATED_PRODUCTS = 'rollpix_imageflip/locations/related_products';
    private const XML_PATH_CMS_BLOCKS = 'rollpix_imageflip/locations/cms_blocks';
    private const XML_PATH_PAGE_BUILDER = 'rollpix_imageflip/locations/page_builder';

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get primary image role
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPrimaryRole(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PRIMARY_ROLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fallback image role
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFallbackRole(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_FALLBACK_ROLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get animation type
     *
     * @param int|null $storeId
     * @return string
     */
    public function getAnimationType(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ANIMATION_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get animation speed in milliseconds
     *
     * @param int|null $storeId
     * @return int
     */
    public function getAnimationSpeed(?int $storeId = null): int
    {
        $speed = (int) $this->scopeConfig->getValue(
            self::XML_PATH_ANIMATION_SPEED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $speed > 0 ? $speed : 300;
    }

    /**
     * Check if desktop only mode is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDesktopOnly(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DESKTOP_ONLY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for category pages
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForCategoryPage(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for widget products
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForWidgetProducts(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_WIDGET_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for search results
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForSearchResults(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEARCH_RESULTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for related products
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForRelatedProducts(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_RELATED_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for CMS blocks
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForCmsBlocks(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_CMS_BLOCKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled for Page Builder
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForPageBuilder(?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAGE_BUILDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get all configuration as array for frontend
     *
     * @param int|null $storeId
     * @return array
     */
    public function getConfigArray(?int $storeId = null): array
    {
        return [
            'enabled' => $this->isEnabled($storeId),
            'primaryRole' => $this->getPrimaryRole($storeId),
            'fallbackRole' => $this->getFallbackRole($storeId),
            'animationType' => $this->getAnimationType($storeId),
            'animationSpeed' => $this->getAnimationSpeed($storeId),
            'desktopOnly' => $this->isDesktopOnly($storeId),
            'locations' => [
                'categoryPage' => $this->isEnabledForCategoryPage($storeId),
                'widgetProducts' => $this->isEnabledForWidgetProducts($storeId),
                'searchResults' => $this->isEnabledForSearchResults($storeId),
                'relatedProducts' => $this->isEnabledForRelatedProducts($storeId),
                'cmsBlocks' => $this->isEnabledForCmsBlocks($storeId),
                'pageBuilder' => $this->isEnabledForPageBuilder($storeId)
            ]
        ];
    }
}
