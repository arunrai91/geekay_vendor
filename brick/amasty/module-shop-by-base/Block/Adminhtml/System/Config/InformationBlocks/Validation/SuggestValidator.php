<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\System\Config\InformationBlocks\Validation;

use Magento\Framework\Module\Manager as ModuleManager;

class SuggestValidator implements MessageValidatorInterface
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var string[]
     */
    private $suggestModules;

    public function __construct(ModuleManager $moduleManager, array $suggestModules = [])
    {
        $this->moduleManager = $moduleManager;
        $this->suggestModules = $suggestModules;
    }

    public function isValid(): bool
    {
        foreach ($this->suggestModules as $module) {
            if (!$this->moduleManager->isEnabled($module)) {
                return true;
            }
        }

        return false;
    }
}
