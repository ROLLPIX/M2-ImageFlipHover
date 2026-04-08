<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Backend\Block\Template\Context;

class ModuleVersion extends Field
{
    private const MODULE_NAME = 'Rollpix_ImageFlipHover';

    /**
     * @var PackageInfoFactory
     */
    private PackageInfoFactory $packageInfoFactory;

    /**
     * @param Context $context
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->packageInfoFactory = $packageInfoFactory;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $packageInfo = $this->packageInfoFactory->create();
        $version = $packageInfo->getVersion(self::MODULE_NAME) ?: __('N/A');

        return '<strong>' . $this->escapeHtml((string) $version) . '</strong>';
    }
}
