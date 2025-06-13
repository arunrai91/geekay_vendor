<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ResourceModel\Feed;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\ResourceModel\Feed as Resource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_feed_entity_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'collection';

    protected function _construct()
    {
        parent::_construct();
        $this->_init(Feed::class, Resource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
