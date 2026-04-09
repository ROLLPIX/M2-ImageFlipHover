<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class HoverMode implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'flip', 'label' => __('Cambio de imagen (Flip)')],
            ['value' => 'slider', 'label' => __('Slider de galería')]
        ];
    }
}
