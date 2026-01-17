<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Plugin\Block\Product;

use Rollpix\ImageFlipHover\Helper\Config;
use Magento\Catalog\Block\Product\Image as ImageBlock;

class ImagePlugin
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
     * Override the template for product image block when flip image is available
     *
     * @param ImageBlock $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(ImageBlock $subject, string $result): string
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        // Check if this image block has flip image data
        if (!$subject->getData('has_flip_image')) {
            return $result;
        }

        $flipImageUrl = $subject->getData('flip_image_url');
        $animationType = $subject->getData('flip_animation_type') ?: 'fade';
        $animationSpeed = $subject->getData('flip_animation_speed') ?: 300;

        if (!$flipImageUrl) {
            return $result;
        }

        // Parse the existing HTML and inject flip image functionality
        return $this->injectFlipImage($result, $flipImageUrl, $animationType, $animationSpeed);
    }

    /**
     * Inject flip image into existing product image HTML
     *
     * @param string $html
     * @param string $flipImageUrl
     * @param string $animationType
     * @param int $animationSpeed
     * @return string
     */
    private function injectFlipImage(
        string $html,
        string $flipImageUrl,
        string $animationType,
        int $animationSpeed
    ): string {
        // Add flip image classes and data attributes to the container
        $html = preg_replace(
            '/class="product-image-container([^"]*)"/',
            'class="product-image-container$1 has-flip-image flip-animation-' . htmlspecialchars($animationType) . '" '
            . 'data-flip-image="true" '
            . 'data-flip-url="' . htmlspecialchars($flipImageUrl) . '" '
            . 'data-animation-type="' . htmlspecialchars($animationType) . '" '
            . 'data-animation-speed="' . $animationSpeed . '" '
            . 'style="--flip-animation-speed: ' . $animationSpeed . 'ms;"',
            $html
        );

        // Find the img tag and wrap it with flip container, adding the flip image
        $pattern = '/(<img\s+class="product-image-photo"[^>]*>)/';

        if (preg_match($pattern, $html, $matches)) {
            $originalImg = $matches[1];

            // Create the modified primary image
            $primaryImg = str_replace(
                'class="product-image-photo"',
                'class="product-image-photo primary-image"',
                $originalImg
            );

            // Extract attributes for flip image
            $width = $this->extractAttribute($originalImg, 'width');
            $height = $this->extractAttribute($originalImg, 'height');
            $alt = $this->extractAttribute($originalImg, 'alt');

            // Create flip image
            $flipImg = sprintf(
                '<img class="product-image-photo flip-image" data-src="%s" loading="lazy" width="%s" height="%s" alt="%s - Alternate View"/>',
                htmlspecialchars($flipImageUrl),
                htmlspecialchars($width),
                htmlspecialchars($height),
                htmlspecialchars($alt)
            );

            // Wrap both images in flip container
            $flipContainer = '<span class="flip-image-container">' . $primaryImg . $flipImg . '</span>';

            $html = str_replace($originalImg, $flipContainer, $html);
        }

        return $html;
    }

    /**
     * Extract attribute value from HTML tag
     *
     * @param string $html
     * @param string $attribute
     * @return string
     */
    private function extractAttribute(string $html, string $attribute): string
    {
        $pattern = '/' . preg_quote($attribute) . '="([^"]*)"/';
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
