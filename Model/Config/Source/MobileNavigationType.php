<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MobileNavigationType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'arrows', 'label' => __('Flechas')],
            ['value' => 'swipe', 'label' => __('Deslizar (Táctil)')],
            ['value' => 'dots_click', 'label' => __('Click en indicadores')]
        ];
    }
}
