<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Context as BackendContext;

class ModuleInfo extends Fieldset
{
    private const MODULE_NAME = 'Rollpix_ImageFlipHover';
    private const MODULE_LABEL = 'Rollpix Image Flip Hover';
    private const GITHUB_URL = 'https://github.com/ROLLPIX/M2-ImageFlipHover';

    /**
     * @var PackageInfoFactory
     */
    private PackageInfoFactory $packageInfoFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\Helper\Js $jsHelper,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        parent::__construct($context, $jsHelper, $data);
        $this->packageInfoFactory = $packageInfoFactory;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $packageInfo = $this->packageInfoFactory->create();
        $version = $packageInfo->getVersion(self::MODULE_NAME) ?: 'N/A';

        $html = '<tr id="row_' . $element->getHtmlId() . '">'
            . '<td class="label" colspan="4" style="padding: 15px;">'
            . '<strong>' . $this->escapeHtml(self::MODULE_LABEL) . '</strong>'
            . ' &nbsp;|&nbsp; v' . $this->escapeHtml($version)
            . ' &nbsp;|&nbsp; <a href="' . $this->escapeUrl(self::GITHUB_URL) . '" target="_blank">GitHub</a>'
            . '</td></tr>';

        return $html;
    }
}
