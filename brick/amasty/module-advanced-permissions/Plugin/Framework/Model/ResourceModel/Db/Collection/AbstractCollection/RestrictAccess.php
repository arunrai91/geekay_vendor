<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

use Amasty\Rolepermissions\Helper\Data as Helper;
use Amasty\Rolepermissions\Model\Restriction\ApplyStoreFilterToCollectionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class RestrictAccess
{
    /**
     * @var ApplyStoreFilterToCollectionInterface
     */
    private $applyStoreFilterToCollection;

    /**
     * @var Helper
     */
    private $helper;

    public function __construct(
        ApplyStoreFilterToCollectionInterface $applyStoreFilterToCollection,
        Helper $helper
    ) {
        $this->applyStoreFilterToCollection = $applyStoreFilterToCollection;
        $this->helper = $helper;
    }

    public function beforeLoad(AbstractCollection $collection): void
    {
        if ($rule = $this->helper->currentRule()) {
            $this->applyStoreFilterToCollection->execute($collection, $rule);
        }
    }
}
