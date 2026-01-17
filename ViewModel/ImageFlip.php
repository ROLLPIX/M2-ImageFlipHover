<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\ViewModel;

use Rollpix\ImageFlipHover\Helper\Config;
use Rollpix\ImageFlipHover\Model\ImageFlipService;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\Serializer\Json;

class ImageFlip implements ArgumentInterface
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
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @param Config $config
     * @param ImageFlipService $imageFlipService
     * @param Json $jsonSerializer
     */
    public function __construct(
        Config $config,
        ImageFlipService $imageFlipService,
        Json $jsonSerializer
    ) {
        $this->config = $config;
        $this->imageFlipService = $imageFlipService;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Check if enabled for category page
     *
     * @return bool
     */
    public function isEnabledForCategoryPage(): bool
    {
        return $this->config->isEnabledForCategoryPage();
    }

    /**
     * Check if enabled for widget products
     *
     * @return bool
     */
    public function isEnabledForWidgetProducts(): bool
    {
        return $this->config->isEnabledForWidgetProducts();
    }

    /**
     * Check if enabled for search results
     *
     * @return bool
     */
    public function isEnabledForSearchResults(): bool
    {
        return $this->config->isEnabledForSearchResults();
    }

    /**
     * Check if enabled for related products
     *
     * @return bool
     */
    public function isEnabledForRelatedProducts(): bool
    {
        return $this->config->isEnabledForRelatedProducts();
    }

    /**
     * Get animation type
     *
     * @return string
     */
    public function getAnimationType(): string
    {
        return $this->config->getAnimationType();
    }

    /**
     * Get animation speed
     *
     * @return int
     */
    public function getAnimationSpeed(): int
    {
        return $this->config->getAnimationSpeed();
    }

    /**
     * Get flip image URL for product
     *
     * @param ProductInterface $product
     * @param string|null $imageId
     * @return string|null
     */
    public function getFlipImageUrl(ProductInterface $product, ?string $imageId = null): ?string
    {
        return $this->imageFlipService->getFlipImageUrl($product, $imageId);
    }

    /**
     * Check if product has flip image
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function hasFlipImage(ProductInterface $product): bool
    {
        return $this->imageFlipService->hasFlipImage($product);
    }

    /**
     * Get configuration as JSON for JavaScript
     *
     * @return string
     */
    public function getConfigJson(): string
    {
        return $this->jsonSerializer->serialize($this->config->getConfigArray());
    }

    /**
     * Get flip image data for product as JSON
     *
     * @param ProductInterface $product
     * @param string|null $imageId
     * @return string
     */
    public function getFlipImageDataJson(ProductInterface $product, ?string $imageId = null): string
    {
        return $this->jsonSerializer->serialize(
            $this->imageFlipService->getFlipImageData($product, $imageId)
        );
    }
}
