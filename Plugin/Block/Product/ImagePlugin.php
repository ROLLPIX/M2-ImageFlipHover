<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Plugin\Block\Product;

use Rollpix\ImageFlipHover\Helper\Config;
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Framework\Serialize\Serializer\Json;

class ImagePlugin
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @param Config $config
     * @param Json $jsonSerializer
     */
    public function __construct(Config $config, Json $jsonSerializer)
    {
        $this->config = $config;
        $this->jsonSerializer = $jsonSerializer;
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

        if (!$subject->getData('has_flip_image')) {
            return $result;
        }

        // Slider mode
        if ($subject->getData('slider_mode')) {
            return $this->handleSliderMode($subject, $result);
        }

        // Flip mode (existing behavior)
        $flipImageUrl = $subject->getData('flip_image_url');
        $animationType = $subject->getData('flip_animation_type') ?: 'fade';
        $animationSpeed = $subject->getData('flip_animation_speed') ?: 300;

        if (!$flipImageUrl) {
            return $result;
        }

        return $this->injectFlipImage($result, $flipImageUrl, $animationType, $animationSpeed);
    }

    /**
     * Handle slider mode injection
     */
    private function handleSliderMode(ImageBlock $subject, string $result): string
    {
        $galleryUrls = $subject->getData('gallery_urls') ?: [];
        $imageCount = $subject->getData('image_count') ?: 0;

        if ($imageCount < 2) {
            return $result;
        }

        return $this->injectSliderHtml($result, $galleryUrls);
    }

    /**
     * Inject slider HTML into product image output
     */
    private function injectSliderHtml(string $html, array $galleryUrls): string
    {
        $galleryJson = htmlspecialchars($this->jsonSerializer->serialize($galleryUrls), ENT_QUOTES, 'UTF-8');
        $sliderConfig = $this->buildSliderConfig();
        $configJson = htmlspecialchars($this->jsonSerializer->serialize($sliderConfig), ENT_QUOTES, 'UTF-8');
        $transitionSpeed = $this->config->getTransitionSpeed();

        $sliderAttrs = 'data-hover-slider="true" '
            . 'data-gallery="' . $galleryJson . '" '
            . 'data-slider-config="' . $configJson . '" '
            . 'style="--slider-transition-speed: ' . $transitionSpeed . 'ms;"';

        $hasContainer = strpos($html, 'product-image-container') !== false;

        if ($hasContainer) {
            // Luma path: add attributes to existing container
            $html = preg_replace(
                '/class="product-image-container([^"]*)"/',
                'class="product-image-container$1 has-hover-slider" ' . $sliderAttrs,
                $html
            );

            // Wrap the existing img in a slider viewport
            $pattern = '/(<img\s+class="product-image-photo"[^>]*>)/s';
            if (preg_match($pattern, $html, $matches)) {
                $originalImg = $matches[1];
                $sliderContainer = '<span class="hover-slider-viewport">' . $originalImg . '</span>';
                $html = str_replace($originalImg, $sliderContainer, $html);
            }
        } else {
            // Hyvä path: no wrapper container, wrap the img directly
            $pattern = '/(<img\s[^>]*class="product-image-photo"[^>]*>)/s';
            if (preg_match($pattern, $html, $matches)) {
                $originalImg = $matches[1];
                $wrapped = '<span class="has-hover-slider" ' . $sliderAttrs . '>'
                    . '<span class="hover-slider-viewport">' . $originalImg . '</span>'
                    . '</span>';
                $html = str_replace($originalImg, $wrapped, $html);
            }
        }

        return $html;
    }

    /**
     * Build slider config array from admin settings
     */
    private function buildSliderConfig(): array
    {
        return [
            'hoverFlip' => $this->config->isHoverFlipEnabled(),
            'transition' => $this->config->getTransitionType(),
            'speed' => $this->config->getTransitionSpeed(),
            'loop' => $this->config->isLoopEnabled(),
            'autoReturn' => $this->config->isAutoReturnEnabled(),
            'desktop' => [
                'nav' => $this->config->getDesktopNavigation(),
                'indicator' => $this->config->getDesktopIndicator(),
                'indicatorPos' => $this->config->getDesktopIndicatorPosition()
            ],
            'mobile' => [
                'nav' => $this->config->getMobileNavigation(),
                'indicator' => $this->config->getMobileIndicator(),
                'indicatorPos' => $this->config->getMobileIndicatorPosition()
            ]
        ];
    }

    /**
     * Inject flip image into existing product image HTML
     */
    private function injectFlipImage(
        string $html,
        string $flipImageUrl,
        string $animationType,
        int $animationSpeed
    ): string {
        $flipAttrs = 'data-flip-image="true" '
            . 'data-flip-url="' . htmlspecialchars($flipImageUrl) . '" '
            . 'data-animation-type="' . htmlspecialchars($animationType) . '" '
            . 'data-animation-speed="' . $animationSpeed . '" '
            . 'style="--flip-animation-speed: ' . $animationSpeed . 'ms;"';

        $hasContainer = strpos($html, 'product-image-container') !== false;

        if ($hasContainer) {
            // Luma path: add attributes to existing container
            $html = preg_replace(
                '/class="product-image-container([^"]*)"/',
                'class="product-image-container$1 has-flip-image flip-animation-' . htmlspecialchars($animationType) . '" '
                . $flipAttrs,
                $html
            );
        }

        // Find the img tag and wrap it with flip container, adding the flip image
        $pattern = '/(<img\s[^>]*class="product-image-photo"[^>]*>)/s';

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
                '<img class="product-image-photo flip-image" data-src="%s" loading="lazy" width="%s" height="%s" alt="%s - %s"/>',
                htmlspecialchars($flipImageUrl),
                htmlspecialchars($width),
                htmlspecialchars($height),
                htmlspecialchars($alt),
                htmlspecialchars((string) __('Alternate View'))
            );

            if ($hasContainer) {
                // Luma: wrap inside existing container
                $flipContainer = '<span class="flip-image-container">' . $primaryImg . $flipImg . '</span>';
            } else {
                // Hyvä: create outer container with data attrs
                $flipContainer = '<span class="has-flip-image flip-animation-' . htmlspecialchars($animationType) . '" ' . $flipAttrs . '>'
                    . '<span class="flip-image-container">' . $primaryImg . $flipImg . '</span>'
                    . '</span>';
            }

            $html = str_replace($originalImg, $flipContainer, $html);
        }

        return $html;
    }

    /**
     * Extract attribute value from HTML tag
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
