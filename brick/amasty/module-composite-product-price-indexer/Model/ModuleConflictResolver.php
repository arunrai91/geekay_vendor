<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

namespace Amasty\CompositeProductPriceIndexer\Model;

use Magento\Framework\Module\Manager;

/**
 * Temporal Class to resolve conflicts between modules
 * Remove after all conflicted modules will be using this one
 */
class ModuleConflictResolver
{
    /**
     * @var string[]
     */
    private $modules;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager,
        array $modules = []
    ) {
        $this->modules = $modules;
        $this->moduleManager = $moduleManager;
    }

    public function hasConflicts(): bool
    {
        foreach ($this->modules as $module) {
            if ($this->moduleManager->isEnabled($module)) {
                return true;
            }
        }

        return false;
    }
}
