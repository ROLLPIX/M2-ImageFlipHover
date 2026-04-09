<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IndicatorPosition implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'top', 'label' => __('Sobre la imagen (arriba)')],
            ['value' => 'bottom', 'label' => __('Sobre la imagen (abajo)')],
            ['value' => 'below', 'label' => __('Debajo de la imagen')]
        ];
    }
}
