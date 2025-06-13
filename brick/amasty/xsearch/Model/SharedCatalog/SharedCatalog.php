<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\SharedCatalog;

use Magento\Framework\Module\Manager;

class SharedCatalog
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Resolver|null
     */
    private $sharedCatalog = null;

    public function __construct(
        Manager $moduleManager,
        Resolver $sharedCatalog
    ) {
        $this->moduleManager = $moduleManager;
        $this->init($sharedCatalog);
    }

    /**
     * @param Resolver
     */
    private function init($sharedCatalog)
    {
        if ($this->moduleManager->isEnabled('Magento_SharedCatalog')
            && $sharedCatalog->isEnabled()
        ) {
            $this->sharedCatalog = $sharedCatalog;
        }
    }

    public function get(): ?Resolver
    {
        return $this->sharedCatalog;
    }
}
