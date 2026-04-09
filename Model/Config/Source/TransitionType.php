<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TransitionType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'fade', 'label' => __('Fundido (Fade)')],
            ['value' => 'slide', 'label' => __('Deslizamiento lateral (Slide)')],
            ['value' => 'instant', 'label' => __('Instantáneo (sin animación)')]
        ];
    }
}
