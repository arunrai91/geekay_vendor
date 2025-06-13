<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\FeedTemplate\ResourceModel;

use Amasty\Feed\Model\FeedTemplate as Model;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FeedTemplate extends AbstractDb
{
    public const TABLE_NAME = 'amasty_feed_template';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, Model::TEMPLATE_ID);
    }

    public function getNameByCode(string $templateCode): string
    {
        $select = $this->getConnection()->select();
        $select->from(
            $this->getMainTable(),
            ['template_name']
        )->where('template_code = ?', $templateCode);

        return $this->getConnection()->fetchOne($select) ?: '';
    }
}
