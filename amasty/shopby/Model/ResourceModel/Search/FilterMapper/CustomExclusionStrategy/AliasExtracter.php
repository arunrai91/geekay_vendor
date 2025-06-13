<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\ResourceModel\Search\FilterMapper\CustomExclusionStrategy;

use Magento\Framework\DB\Select;

class AliasExtracter
{
    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     * @return string|null
     * @throws \Zend_Db_Select_Exception
     */
    public function execute(Select $select): ?string
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return array_keys($fromArr)[0] ?? null;
    }
}
