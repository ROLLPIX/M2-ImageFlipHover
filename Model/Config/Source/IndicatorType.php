<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IndicatorType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'bars', 'label' => __('Barras proporcionales')],
            ['value' => 'dots', 'label' => __('Puntos')],
            ['value' => 'pills', 'label' => __('Puntos con activo alargado (Pills)')],
            ['value' => 'counter', 'label' => __('Contador (1/5)')],
            ['value' => 'none', 'label' => __('Ninguno')]
        ];
    }
}
