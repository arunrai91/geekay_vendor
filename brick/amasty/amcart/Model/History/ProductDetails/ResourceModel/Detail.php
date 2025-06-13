<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\History\ProductDetails\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Detail extends AbstractDb
{
    public const TABLE_NAME = 'amasty_acart_history_details';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, \Amasty\Acart\Model\History\ProductDetails\Detail::DETAIL_ID);
    }
}
