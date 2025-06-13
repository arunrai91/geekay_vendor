<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Model\Request\PageTitleRegistry;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;
use Magento\Framework\View\Page\Config as PageConfig;

class Meta extends AbstractHelper
{
    /**
     * @var PageConfig
     */
    private PageConfig $pageConfig;

    /**
     * @var PageTitleRegistry
     */
    private PageTitleRegistry $pageTitleRegistry;

    public function __construct(
        Context $context,
        PageConfig $pageConfig,
        PageTitleRegistry $pageTitleRegistry
    ) {
        parent::__construct($context);
        $this->pageConfig = $pageConfig;
        $this->pageTitleRegistry = $pageTitleRegistry;
    }

    /**
     * @param Category $category
     *
     * @return mixed|string
     */
    public function getOriginPageMetaTitle(Category $category)
    {
        return $category->getData('meta_title')
            ?: (string)$this->pageTitleRegistry->get();
    }

    /**
     * @param Category $category
     *
     * @return mixed|string
     */
    public function getOriginPageMetaDescription(Category $category)
    {
        return $category->getData('meta_description') ?: $this->pageConfig->getDescription();
    }
}
