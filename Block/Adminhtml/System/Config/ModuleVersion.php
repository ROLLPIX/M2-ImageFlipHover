<?php
/**
 * @author Rollpix
 * @package Rollpix_ImageFlipHover
 */
declare(strict_types=1);

namespace Rollpix\ImageFlipHover\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\Serializer\Json;

class ModuleVersion extends Field
{
    private ComponentRegistrarInterface $componentRegistrar;
    private FileDriver $fileDriver;
    private Json $json;

    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        FileDriver $fileDriver,
        Json $json,
        array $data = []
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->fileDriver = $fileDriver;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $version = $this->getModuleVersion();
        $element->setValue($version);
        return '<strong>' . $this->escapeHtml($version) . '</strong>';
    }

    private function getModuleVersion(): string
    {
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                'Rollpix_ImageFlipHover'
            );
            $composerFile = $path . '/composer.json';
            if ($this->fileDriver->isExists($composerFile)) {
                $content = $this->fileDriver->fileGetContents($composerFile);
                $data = $this->json->unserialize($content);
                return $data['version'] ?? 'unknown';
            }
        } catch (\Exception $e) {
            // Fallback
        }
        return 'unknown';
    }
}
