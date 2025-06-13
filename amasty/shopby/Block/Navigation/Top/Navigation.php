<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation\Top;

use Amasty\Shopby\ViewModel\Navigation\Toolbar;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    public const NAME = 'amshopby.catalog.topnav';

    /**
     * @var Toolbar
     */
    private $toolbarModel;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        FilterList $filterList,
        AvailabilityFlagInterface $visibilityFlag,
        Toolbar $toolbarModel,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
        $this->toolbarModel = $toolbarModel;
    }

    /**
     * @return \Magento\LayeredNavigation\Block\Navigation
     * @throws LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->toolbarModel->resolveSearchLayoutToolbar($this->getLayout());

        return parent::_beforeToHtml();
    }
}
