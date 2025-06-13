<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks;

use Amasty\Base\Model\Feed\ExtensionsProvider;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template;

class ExistingConflicts extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Base::config/information/existing_conflicts.phtml';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ExtensionsProvider
     */
    private $extensionsProvider;

    public function __construct(
        Template\Context $context,
        Manager $moduleManager,
        ExtensionsProvider $extensionsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->extensionsProvider = $extensionsProvider;
    }

    public function getElement(): AbstractElement
    {
        return $this->getParentBlock()->getElement();
    }

    public function getConflictsMessages(): array
    {
        $messages = [];

        foreach ($this->getExistingConflicts() as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $messages[] = __(
                    'Incompatibility with 3rd party module %1 found. To avoid possible conflicts,'
                    . ' we strongly recommend turning it off with the following command: "%2". '
                    . '<a href="%3" target="_blank">Contact</a> our Support team for more information.',
                    $moduleName,
                    'magento module:disable ' . $moduleName,
                    'https://support.amasty.com/portal/en/newticket'
                );
            }
        }

        return $messages;
    }

    private function getExistingConflicts(): array
    {
        $conflicts = [];
        $moduleCode = $this->getElement()->getDataByPath('group/module_code');
        $module = $this->extensionsProvider->getFeedModuleData($moduleCode);
        if ($module && isset($module['conflictExtensions'])) {
            array_map(function ($extension) use (&$conflicts) {
                $conflicts[] = trim($extension);
            }, explode(',', $module['conflictExtensions']));
        }

        return $conflicts;
    }
}
