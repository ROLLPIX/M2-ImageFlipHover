<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ImageRoles implements OptionSourceInterface
{
    /**
     * @var ProductAttributeCollectionFactory
     */
    private ProductAttributeCollectionFactory $productAttributeCollectionFactory;

    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @param ProductAttributeCollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        ProductAttributeCollectionFactory $productAttributeCollectionFactory
    ) {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * Get all available image roles including custom ones
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [];

            // Add empty option
            $this->options[] = [
                'value' => '',
                'label' => __('-- Seleccionar --')
            ];

            // Add "Second Gallery Image" option (uses 2nd image in gallery regardless of role)
            $this->options[] = [
                'value' => 'second_image',
                'label' => __('Segunda Imagen de Galería (Posición #2)')
            ];

            // Get all media_image type attributes dynamically
            $imageAttributes = $this->getAllMediaImageAttributes();

            foreach ($imageAttributes as $code => $label) {
                $this->options[] = [
                    'value' => $code,
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }

    /**
     * Get all attributes with media_image frontend input type
     * This will include both native and custom image roles
     *
     * @return array
     */
    private function getAllMediaImageAttributes(): array
    {
        $attributes = [];

        try {
            // Create collection of product attributes with media_image frontend input
            $collection = $this->productAttributeCollectionFactory->create();
            $collection->addFieldToFilter('frontend_input', 'media_image');
            $collection->setOrder('frontend_label', 'ASC');

            foreach ($collection as $attribute) {
                $code = $attribute->getAttributeCode();
                $label = $attribute->getFrontendLabel();

                if ($code && $label) {
                    $attributes[$code] = $label;
                } elseif ($code) {
                    // Generate label from code if not available
                    $attributes[$code] = $this->generateLabelFromCode($code);
                }
            }

            // Ensure we have the standard Magento image roles
            $nativeRoles = $this->getNativeImageRoles();
            foreach ($nativeRoles as $code => $label) {
                if (!isset($attributes[$code])) {
                    $attributes[$code] = $label;
                }
            }

            // Sort by label
            asort($attributes);

        } catch (\Exception $e) {
            // Fallback to native roles if collection fails
            $attributes = $this->getNativeImageRoles();
        }

        return $attributes;
    }

    /**
     * Get native Magento image roles
     *
     * @return array
     */
    private function getNativeImageRoles(): array
    {
        return [
            'image' => __('Imagen Base'),
            'small_image' => __('Imagen Pequeña'),
            'thumbnail' => __('Miniatura'),
            'swatch_image' => __('Imagen de Muestra')
        ];
    }

    /**
     * Generate a human-readable label from attribute code
     *
     * @param string $code
     * @return string
     */
    private function generateLabelFromCode(string $code): string
    {
        // Convert snake_case to Title Case
        $label = str_replace('_', ' ', $code);
        return ucwords($label);
    }
}
