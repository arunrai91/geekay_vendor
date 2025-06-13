<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ValidProductSnapshot\ResourceModel;

use Amasty\Feed\Api\Data\ValidProductsSnapshotInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ValidProductSnapshot extends AbstractDb
{
    public const TABLE_NAME = 'amasty_feed_valid_products_snapshot';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ValidProductsSnapshotInterface::ID);
    }
}
