<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AnimationType implements OptionSourceInterface
{
    /**
     * Get available animation types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'fade', 'label' => __('Desvanecimiento (Fade)')],
            ['value' => 'slide-left', 'label' => __('Deslizar Izquierda')],
            ['value' => 'slide-right', 'label' => __('Deslizar Derecha')],
            ['value' => 'slide-up', 'label' => __('Deslizar Arriba')],
            ['value' => 'slide-down', 'label' => __('Deslizar Abajo')],
            ['value' => 'zoom', 'label' => __('Zoom')],
            ['value' => 'flip-horizontal', 'label' => __('Voltear Horizontal')],
            ['value' => 'flip-vertical', 'label' => __('Voltear Vertical')]
        ];
    }
}
