<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Utils;

use Magento\Framework\DB\Select;
use Zend_Db_Select_Exception;

class MainAliasResolver
{
    public function resolve(Select $select): ?string
    {
        try {
            $from = $select->getPart(Select::FROM);
        } catch (Zend_Db_Select_Exception $e) {
            return null;
        }

        foreach ($from as $alias => $data) {
            if ($data['joinType'] == 'from') {
                return $alias;
            }
        }

        return null;
    }
}
