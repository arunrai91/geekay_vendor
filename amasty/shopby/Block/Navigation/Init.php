<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;

class Init extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Template\Context $context,
        \Amasty\ShopbyBase\Model\Category\Manager $categoryManager,
        array $data = []
    ) {
        $categoryManager->init();
        parent::__construct($context, $data);
    }
}
