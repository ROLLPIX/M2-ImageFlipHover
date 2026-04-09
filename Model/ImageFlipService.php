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
     * @var array In-memory cache of gallery URLs keyed by product ID
     */
    private array $galleryCache = [];

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

        $baseImage = $product->getData('image');
        $primaryRole = $this->config->getPrimaryRole();
        $fallbackRole = $this->config->getFallbackRole();

        // Try primary role first
        $imageUrl = $this->getImageByRoleOrSecond($product, $primaryRole, $imageId, $baseImage);

        // If no primary image found, try fallback role
        if (!$imageUrl && $fallbackRole && $fallbackRole !== $primaryRole) {
            $imageUrl = $this->getImageByRoleOrSecond($product, $fallbackRole, $imageId, $baseImage);
        }

        // For configurable products: try child simple products if parent has no flip image
        if (!$imageUrl && $product->getTypeId() === 'configurable') {
            $imageUrl = $this->getFlipImageFromChildren($product, $imageId);
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
    private function getImageByRoleOrSecond($product, string $role, ?string $imageId = null, ?string $baseImage = null): ?string
    {
        if (empty($role)) {
            return null;
        }

        // Special handling for "second_image" option
        if ($role === 'second_image') {
            $secondImage = $this->getAlternateGalleryImage($product, $baseImage);
            if ($secondImage) {
                return $this->buildImageUrl($product, $secondImage, $imageId);
            }
            return null;
        }

        // Regular role handling
        return $this->getImageUrlByRole($product, $role, $imageId, $baseImage);
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

            return $this->imageHelper->getUrl();
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
    private function getImageUrlByRole($product, string $role, ?string $imageId = null, ?string $baseImage = null): ?string
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

        // Skip if flip image is the same as the base image
        if ($baseImage && $imageValue === $baseImage) {
            return null;
        }

        // Skip invalid image values (.tmp files, missing extensions, etc.)
        if (!$this->isValidImageValue($imageValue)) {
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
     * Check if an image value is a valid image file path
     *
     * @param string $imageValue
     * @return bool
     */
    private function isValidImageValue(string $imageValue): bool
    {
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($imageValue, PATHINFO_EXTENSION));

        return in_array($extension, $validExtensions, true);
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
            // Check varchar table for the image value
            $varcharTable = $this->resourceConnection->getTableName('catalog_product_entity_varchar');
            $imageValue = $connection->fetchOne(
                $connection->select()
                    ->from($varcharTable, ['value'])
                    ->where('attribute_id = ?', $attributeId)
                    ->where('entity_id = ?', $productId)
                    ->where('value IS NOT NULL')
                    ->where('value != ?', 'no_selection')
                    ->where('value != ?', '')
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
     * Get the first gallery image that is different from the base image
     *
     * @param ProductInterface|Product $product
     * @param string|null $baseImage
     * @return string|null
     */
    private function getAlternateGalleryImage($product, ?string $baseImage = null): ?string
    {
        $productId = $product->getId();
        if (!$productId) {
            return null;
        }

        return $this->getAlternateGalleryImageByProductId((int) $productId, $baseImage);
    }

    /**
     * Get the first gallery image by position that is not the base image
     *
     * @param int $productId
     * @param string|null $baseImage Image path to exclude
     * @return string|null
     */
    private function getAlternateGalleryImageByProductId(int $productId, ?string $baseImage = null): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $galleryTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery');
        $galleryValueTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value');
        $galleryEntityTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        $storeId = (int) $this->storeManager->getStore()->getId();

        // Get the first gallery image that is not the base image, ordered by position
        $select = $connection->select()
            ->from(['mg' => $galleryTable], ['value'])
            ->join(
                ['mgvte' => $galleryEntityTable],
                'mg.value_id = mgvte.value_id',
                []
            )
            ->joinLeft(
                ['mgv' => $galleryValueTable],
                'mg.value_id = mgv.value_id AND mgvte.entity_id = mgv.entity_id'
                . ' AND mgv.store_id IN (0, ' . $storeId . ')',
                []
            )
            ->where('mgvte.entity_id = ?', $productId)
            ->where('mg.media_type = ?', 'image')
            ->where('COALESCE(mgv.disabled, 0) = 0')
            ->group('mg.value_id')
            ->order('MIN(COALESCE(mgv.position, 999)) ASC');

        // Exclude the base image so we always get a different one
        if ($baseImage) {
            $select->where('mg.value != ?', $baseImage);
        }

        $select->limit(1);

        return $connection->fetchOne($select) ?: null;
    }

    /**
     * Get flip image from child products of a configurable product
     *
     * @param ProductInterface|Product $product
     * @param string|null $imageId
     * @return string|null
     */
    private function getFlipImageFromChildren($product, ?string $imageId): ?string
    {
        $childIds = $this->getConfigurableChildIds((int) $product->getId());
        if (empty($childIds)) {
            return null;
        }

        $primaryRole = $this->config->getPrimaryRole();
        $fallbackRole = $this->config->getFallbackRole();

        // Try primary role on children
        $imageValue = $this->findChildImage($childIds, $primaryRole);

        // Try fallback role on children
        if (!$imageValue && $fallbackRole && $fallbackRole !== $primaryRole) {
            $imageValue = $this->findChildImage($childIds, $fallbackRole);
        }

        if ($imageValue) {
            return $this->buildImageUrl($product, $imageValue, $imageId);
        }

        return null;
    }

    /**
     * Find an image from child products by role
     *
     * @param array $childIds
     * @param string $role
     * @return string|null
     */
    private function findChildImage(array $childIds, string $role): ?string
    {
        if (empty($role)) {
            return null;
        }

        if ($role === 'second_image') {
            foreach ($childIds as $childId) {
                $image = $this->getAlternateGalleryImageByProductId((int) $childId);
                if ($image) {
                    return $image;
                }
            }
            return null;
        }

        // For regular roles, check EAV attribute values on children
        return $this->getAttributeValueFromChildren($childIds, $role);
    }

    /**
     * Get child product IDs for a configurable product
     *
     * @param int $parentId
     * @return array
     */
    private function getConfigurableChildIds(int $parentId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $superLinkTable = $this->resourceConnection->getTableName('catalog_product_super_link');

        return $connection->fetchCol(
            $connection->select()
                ->from($superLinkTable, ['product_id'])
                ->where('parent_id = ?', $parentId)
        );
    }

    /**
     * Get image attribute value from child products
     *
     * @param array $childIds
     * @param string $role
     * @return string|null
     */
    private function getAttributeValueFromChildren(array $childIds, string $role): ?string
    {
        $connection = $this->resourceConnection->getConnection();

        $eavAttributeTable = $this->resourceConnection->getTableName('eav_attribute');
        $attributeId = $connection->fetchOne(
            $connection->select()
                ->from($eavAttributeTable, ['attribute_id'])
                ->where('attribute_code = ?', $role)
                ->where('entity_type_id = ?', 4) // catalog_product entity type
        );

        if (!$attributeId) {
            return null;
        }

        $varcharTable = $this->resourceConnection->getTableName('catalog_product_entity_varchar');
        $imageValue = $connection->fetchOne(
            $connection->select()
                ->from($varcharTable, ['value'])
                ->where('attribute_id = ?', $attributeId)
                ->where('entity_id IN (?)', $childIds)
                ->where('value IS NOT NULL')
                ->where('value != ?', 'no_selection')
                ->where('value != ?', '')
                ->limit(1)
        );

        return $imageValue ?: null;
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

    /**
     * Preload gallery images for a batch of product IDs (single query)
     *
     * @param array $productIds
     * @param int $maxImages
     * @return void
     */
    public function preloadGalleryBatch(array $productIds, int $maxImages = 8): void
    {
        if (empty($productIds)) {
            return;
        }

        // Filter out already cached
        $productIds = array_filter($productIds, function ($id) {
            return !isset($this->galleryCache[(int) $id]);
        });

        if (empty($productIds)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $galleryTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery');
        $galleryValueTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value');
        $galleryEntityTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        $storeId = (int) $this->storeManager->getStore()->getId();

        $select = $connection->select()
            ->from(['mg' => $galleryTable], ['value'])
            ->join(
                ['mgvte' => $galleryEntityTable],
                'mg.value_id = mgvte.value_id',
                ['entity_id']
            )
            ->joinLeft(
                ['mgv' => $galleryValueTable],
                'mg.value_id = mgv.value_id AND mgvte.entity_id = mgv.entity_id'
                . ' AND mgv.store_id IN (0, ' . $storeId . ')',
                []
            )
            ->where('mgvte.entity_id IN (?)', $productIds)
            ->where('mg.media_type = ?', 'image')
            ->where('COALESCE(mgv.disabled, 0) = 0')
            ->group(['mgvte.entity_id', 'mg.value_id'])
            ->order(['mgvte.entity_id ASC', 'MIN(COALESCE(mgv.position, 999)) ASC']);

        $rows = $connection->fetchAll($select);

        // Group by entity_id and slice to maxImages
        $grouped = [];
        foreach ($rows as $row) {
            $entityId = (int) $row['entity_id'];
            if (!isset($grouped[$entityId])) {
                $grouped[$entityId] = [];
            }
            if (count($grouped[$entityId]) < $maxImages) {
                $imageValue = $row['value'];
                if ($this->isValidImageValue($imageValue)) {
                    $grouped[$entityId][] = $imageValue;
                }
            }
        }

        // Store in cache (raw paths, URLs will be built on demand)
        foreach ($productIds as $id) {
            $this->galleryCache[(int) $id] = $grouped[(int) $id] ?? [];
        }
    }

    /**
     * Get slider image data for a product
     *
     * @param ProductInterface|Product $product
     * @param string|null $imageId
     * @return array
     */
    public function getSliderImageData($product, ?string $imageId = null): array
    {
        $productId = (int) $product->getId();

        // Try cache first
        if (isset($this->galleryCache[$productId])) {
            $imagePaths = $this->galleryCache[$productId];
        } else {
            $imagePaths = $this->getAllGalleryImagesByProductId(
                $productId,
                $this->config->getMaxImages()
            );
        }

        // For configurables with no gallery, try children
        if (empty($imagePaths) && $product->getTypeId() === 'configurable') {
            $childIds = $this->getConfigurableChildIds($productId);
            foreach ($childIds as $childId) {
                $childId = (int) $childId;
                if (isset($this->galleryCache[$childId])) {
                    $childPaths = $this->galleryCache[$childId];
                } else {
                    $childPaths = $this->getAllGalleryImagesByProductId(
                        $childId,
                        $this->config->getMaxImages()
                    );
                }
                if (!empty($childPaths)) {
                    $imagePaths = $childPaths;
                    break;
                }
            }
        }

        if (count($imagePaths) < 2) {
            return [
                'hasSliderImages' => false,
                'galleryUrls' => [],
                'imageCount' => count($imagePaths)
            ];
        }

        // Build resized URLs
        $galleryUrls = [];
        foreach ($imagePaths as $path) {
            $url = $this->buildImageUrl($product, $path, $imageId);
            if ($url) {
                $galleryUrls[] = $url;
            }
        }

        return [
            'hasSliderImages' => count($galleryUrls) >= 2,
            'galleryUrls' => $galleryUrls,
            'imageCount' => count($galleryUrls)
        ];
    }

    /**
     * Get all gallery image paths for a product ID
     *
     * @param int $productId
     * @param int $maxImages
     * @return array Raw image paths
     */
    private function getAllGalleryImagesByProductId(int $productId, int $maxImages = 8): array
    {
        $connection = $this->resourceConnection->getConnection();
        $galleryTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery');
        $galleryValueTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value');
        $galleryEntityTable = $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value_to_entity');

        $storeId = (int) $this->storeManager->getStore()->getId();

        $select = $connection->select()
            ->from(['mg' => $galleryTable], ['value'])
            ->join(
                ['mgvte' => $galleryEntityTable],
                'mg.value_id = mgvte.value_id',
                []
            )
            ->joinLeft(
                ['mgv' => $galleryValueTable],
                'mg.value_id = mgv.value_id AND mgvte.entity_id = mgv.entity_id'
                . ' AND mgv.store_id IN (0, ' . $storeId . ')',
                []
            )
            ->where('mgvte.entity_id = ?', $productId)
            ->where('mg.media_type = ?', 'image')
            ->where('COALESCE(mgv.disabled, 0) = 0')
            ->group('mg.value_id')
            ->order('MIN(COALESCE(mgv.position, 999)) ASC')
            ->limit($maxImages);

        $values = $connection->fetchCol($select);

        return array_filter($values, [$this, 'isValidImageValue']);
    }
}
