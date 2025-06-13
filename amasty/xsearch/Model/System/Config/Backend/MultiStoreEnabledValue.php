<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;

class MultiStoreEnabledValue extends \Magento\Framework\App\Config\Value
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->moduleManager = $moduleManager;
    }

    public function getValue(): string
    {
        if (!$this->moduleManager->isEnabled('Amasty_AdvancedSearchMultiStore')) {
            return '0';
        }

        return parent::getValue();
    }
}
