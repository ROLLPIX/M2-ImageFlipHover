<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Plugin\Product;

use Rollpix\ImageFlipHover\Helper\Config;
use Rollpix\ImageFlipHover\Model\ImageFlipService;
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product;

class ImagePlugin
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
     * @param Config $config
     * @param ImageFlipService $imageFlipService
     */
    public function __construct(
        Config $config,
        ImageFlipService $imageFlipService
    ) {
        $this->config = $config;
        $this->imageFlipService = $imageFlipService;
    }

    /**
     * Add flip image data to the image block
     *
     * @param ImageFactory $subject
     * @param ImageBlock $result
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return ImageBlock
     */
    public function afterCreate(
        ImageFactory $subject,
        ImageBlock $result,
        Product $product,
        string $imageId,
        array $attributes = []
    ): ImageBlock {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $flipImageData = $this->imageFlipService->getFlipImageData($product, $imageId);

        if ($flipImageData['hasFlipImage']) {
            $result->setData('flip_image_url', $flipImageData['flipImageUrl']);
            $result->setData('flip_animation_type', $flipImageData['animationType']);
            $result->setData('flip_animation_speed', $flipImageData['animationSpeed']);
            $result->setData('has_flip_image', true);
        }

        return $result;
    }
}
