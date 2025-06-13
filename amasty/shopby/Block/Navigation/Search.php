<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Block\Navigation\Top\Navigation;
use Amasty\Shopby\ViewModel\Navigation\Toolbar;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template\Context;

class Search extends \Magento\LayeredNavigation\Block\Navigation
{
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
     * Prevent pre-processing product collection more than 1 time.
     *
     * Some layouts (like 1column layout) may not contain "amshopby.catalog.topnav" Block.
     * In such case, this block should prepare toolbar.
     *
     * @return $this|\Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\Template
     */
    protected function _beforeToHtml()
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock(Navigation::NAME);

        if (!$block || !$block->getDisplay()) {
            $this->toolbarModel->resolveSearchLayoutToolbar($layout);

            return parent::_beforeToHtml();
        }
        
        return $this;
    }
}
