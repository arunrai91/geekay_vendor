<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Restriction;

use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface ApplyStoreFilterToCollectionInterface
{
    /**
     * @param AbstractCollection $collection
     * @param RuleInterface $rule
     *
     * @return void
     */
    public function execute(AbstractCollection $collection, RuleInterface $rule): void;
}
