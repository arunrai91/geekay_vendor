<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model;

class UserSearch extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\Xsearch\Model\ResourceModel\UserSearch::class);
    }
}
