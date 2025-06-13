<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

class SalesRule extends \Magento\SalesRule\Model\Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Acart\Model\ResourceModel\Rule::class);
        $this->setIdFieldName('rule_id');
    }
}
