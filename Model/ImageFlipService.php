<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model;

use Rollpix\ImageFlipHover\Helper\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;

class ImageFlipService
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ImageHelper
     */
    private ImageHelper $imageHelper;

    /**
     * @var MediaConfig
     */
    private MediaConfig $mediaConfig;

    /**
     * @var AssetRepository
     */
    private AssetRepository $assetRepository;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Config $config
     * @param ImageHelper $imageHelper
     * @param MediaConfig $mediaConfig
     * @param AssetRepository $assetRepository
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        ImageHelper $imageHelper,
        MediaConfig $mediaConfig,
        AssetRepository $assetRepository,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->mediaConfig = $mediaConfig;
        $this->assetRepository = $assetRepository;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * Get flip image URL for a product
     *
     * @param ProductInterface|Product $product
     * @param string|null $imageId Optional image ID for resizing
     * @return string|null
     */
    public function getFlipImageUrl($product, ?string $imageId = null): ?string
    {
        if (!$this->config->isEnabled()) {
            return null;
        }

        $primaryRole = $this->config->getPrimaryRole();
        $fallbackRole = $this->config->getFallbackRole();

        // Get the product's current base image to avoid returning the same image as flip
        $baseImage = $product->getData('image');
        $baseImageUrl = null;
        if ($baseImage && $baseImage !== 'no_selection') {
            $baseImageUrl = $this->buildImageUrl($product, $baseImage, $imageId);
        }

        // Try primary role first
        $imageUrl = $this->getImageByRoleOrSecond($product, $primaryRole, $imageId);

        // If no primary image found, try fallback role
        if (!$imageUrl && $fallbackRole && $fallbackRole !== $primaryRole) {
            $imageUrl = $this->getImageByRoleOrSecond($product, $fallbackRole, $imageId);
        }

        // Don't return flip image if it's the same as the base image
        if ($imageUrl && $baseImageUrl && $imageUrl === $baseImageUrl) {
            return null;
        }

        return $imageUrl;
    }

    /**
     * Get image by role, or second gallery image if role is 'second_image'
     *
     * @param ProductInterface|Product $product
     * @param string $role
     * @param string|null $imageId
     * @return string|null
     */
    private function getImageByRoleOrSecond($product, string $role, ?string $imageId = null): ?string
    {
        if (empty($role)) {
            return null;
        }

        // Special handling for "second_image" option
        if ($role === 'second_image') {
            $secondImage = $this->getSecondGalleryImage($product);
            if ($secondImage) {
                return $this->buildImageUrl($product, $secondImage, $imageId);
            }
            return null;
        }

        // Regular role handling
        return $this->getImageUrlByRole($product, $role, $imageId);
    }

    /**
     * Build resized image URL
     *
     * @param ProductInterface|Product $product
     * @param string $imageValue
     * @param string|null $imageId
     * @return string|null
     */
    private function buildImageUrl($product, string $imageValue, ?string $imageId = null): ?string
    {
        try {
            $this->imageHelper->init($product, $imageId ?: 'category_page_list')
                ->setImageFile($imageValue);

            $url = $this->imageHelper->getUrl();

            // If imageHelper returned the placeholder, treat as no image found
            if ($url && strpos($url, '/placeholder/') !== false) {
                return null;
            }

            return $url;
        } catch (\Exception $e) {
            return $this->mediaConfig->getMediaUrl($imageValue);
        }
    }

    /**
     * Get image URL by role
     *
     * @param ProductInterface|Product $product
     * @param string $role
     * @param string|null $imageId
     * @return string|null
     */
    private function getImageUrlByRole($product, string $role, ?string $imageId = null): ?string
    {
        if (empty($role)) {
            return null;
        }

        // Get image value for the specified role
        $imageValue = $product->getData($role);

        // Check if image exists and is not the placeholder
        if (!$imageValue || $imageValue === 'no_selection') {
            // Try to find image in media gallery with this role
            $imageValue = $this->getImageFromGalleryByRole($product, $role);
        }

        if (!$imageValue || $imageValue === 'no_selection') {
            return null;
        }

        // Generate resized image URL using image helper
        try {
            $this->imageHelper->init($product, $imageId ?: 'category_page_list')
                ->setImageFile($imageValue);

            return $this->imageHelper->getUrl();
        } catch (\Exception $e) {
            // If image helper fails, return direct URL
            return $this->mediaConfig->getMediaUrl($imageValue);
        }
    }

    /**
     * Get image from gallery by role (queries the database directly for custom roles)
     *
     * @param ProductInterface|Product $product
     * @param string $role
     * @return string|null
     */
    private function getImageFromGalleryByRole($product, string $role): ?string
    {
        $productId = $product->getId();
        if (!$productId) {
            return null;
        }

        // First, check if the role is stored as a product attribute value
        // This works for custom media_image attributes like rpx_product_image_on_hover
        $connection = $this->resourceConnection->getConnection();

        // Get the attribute ID for the role
        $eavAttributeTable = $this->resourceConnection->getTableName('eav_attribute');
        $attributeId = $connection->fetchOne(
            $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', $role)
                ->where('entity_type_id = ?', 4) // catalog_product entity type
        );

        if ($attributeId) {
            // Check varchar table for the image value, preferring store-specific over default
            $storeId = (int) $this->storeManager->getStore()->getId();
            $varcharTable = $this->resourceConnection->getTableName('catalog_product_entity_varchar');
            $imageValue = $connection->fetchOne(
                $connection->select()
                    ->from($varcharTable, ['value'])
                    ->where('attribute_id = ?', $attributeId)
                    ->where('entity_id = ?', $productId)
                    ->where('store_id IN (?)', [0, $storeId])
                    ->where('value IS NOT NULL')
                    ->where('value != ?', 'no_selection')
                    ->where('value != ?', '')
                    ->order('store_id DESC')
                    ->limit(1)
            );

            if ($imageValue) {
                return $imageValue;
            }
        }

        // Fallback: check media gallery images collection
        $mediaGallery = $product->getMediaGalleryImages();

        if ($mediaGallery && $mediaGallery->getSize() > 0) {
            foreach ($mediaGallery as $image) {
                $types = $image->getData('types') ?? [];
                if (is_string($types)) {
                    $types = explode(',', $types);
                }

                // Check if this image has the requested role
                if (in_array($role, $types, true)) {
                    return $image->getData('file');
                }
            }
        }

        return null;
    }

    /**
     * Get second image from product gallery (common fallback approach)
     *
     * @param ProductInterface|Product $product
     * @return string|null
     */
    private function getSecondGalleryImage($product): ?string
    {
        $productId = $product->getId();
        if (!$productId) {
            return null;
        }

        $storeId = (int) $this->storeManager->getStore()->getId();
        $connection = $this->resourceConnection->getConnection();
        $galleryTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery');
        $galleryValueTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value');
        $galleryEntityTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        // Get the second image from the gallery ordered by position.
        // Join gallery_value twice (store-specific + default) to:
        // 1. Filter by entity_id to avoid matching child product overrides
        // 2. Filter by store_id to avoid duplicate rows that break LIMIT/OFFSET
        // 3. Use COALESCE to prefer store-specific position/disabled over default
        $select = $connection->select()
            ->from(['mg' => $galleryTable], ['value'])
            ->join(
                ['mgvte' => $galleryEntityTable],
                'mg.value_id = mgvte.value_id',
                []
            )
            ->joinLeft(
                ['mgv_store' => $galleryValueTable],
                'mg.value_id = mgv_store.value_id'
                . ' AND mgv_store.entity_id = ' . (int) $productId
                . ' AND mgv_store.store_id = ' . $storeId,
                []
            )
            ->joinLeft(
                ['mgv_default' => $galleryValueTable],
                'mg.value_id = mgv_default.value_id'
                . ' AND mgv_default.entity_id = ' . (int) $productId
                . ' AND mgv_default.store_id = 0',
                []
            )
            ->where('mgvte.entity_id = ?', $productId)
            ->where('mg.media_type = ?', 'image')
            ->where('COALESCE(mgv_store.disabled, mgv_default.disabled, 0) = 0')
            ->order('COALESCE(mgv_store.position, mgv_default.position, 999) ASC')
            ->limit(1, 1); // Skip first, get second

        return $connection->fetchOne($select) ?: null;
    }

    /**
     * Check if product has a flip image
     *
     * @param ProductInterface|Product $product
     * @return bool
     */
    public function hasFlipImage($product): bool
    {
        return $this->getFlipImageUrl($product) !== null;
    }

    /**
     * Get flip image data for product (for use in templates)
     *
     * @param ProductInterface|Product $product
     * @param string|null $imageId
     * @return array
     */
    public function getFlipImageData($product, ?string $imageId = null): array
    {
        $flipImageUrl = $this->getFlipImageUrl($product, $imageId);

        return [
            'hasFlipImage' => $flipImageUrl !== null,
            'flipImageUrl' => $flipImageUrl,
            'animationType' => $this->config->getAnimationType(),
            'animationSpeed' => $this->config->getAnimationSpeed()
        ];
    }
}
