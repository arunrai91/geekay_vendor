<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Restriction;

use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zend_Db_Select_Exception;

class ApplyStoreFilterToCollection implements ApplyStoreFilterToCollectionInterface
{
    public const STORE_ID_KEY = 'store_id';

    public function execute(AbstractCollection $collection, RuleInterface $rule): void
    {
        if (in_array(self::STORE_ID_KEY, $this->getCollectionKeys($collection))
            && $rule->getScopeStoreviews()
        ) {
            $alias = '';

            if ($mainAlias = $this->getMainAlias($collection->getSelect())) {
                $alias = $mainAlias . '.';
            }

            $collection->addFieldToFilter(
                $alias . self::STORE_ID_KEY,
                ['in' => $rule->getScopeStoreviews()]
            );
        }
    }

    private function getCollectionKeys(AbstractDb $collection): array
    {
        return array_keys(
            $collection->getConnection()->describeTable($collection->getMainTable())
        );
    }

    private function getMainAlias(Select $select): ?string
    {
        try {
            $from = $select->getPart(Select::FROM);
        } catch (Zend_Db_Select_Exception $e) {
            return null;
        }

        foreach ($from as $alias => $data) {
            if ($data['joinType'] == 'from') {
                return (string) $alias;
            }
        }

        return null;
    }
}
