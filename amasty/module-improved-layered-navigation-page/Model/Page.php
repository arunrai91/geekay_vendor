<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class Page extends AbstractExtensibleModel
{
    /**
     * Position of placing meta data in category
     */
    public const POSITION_REPLACE = 'replace';
    public const POSITION_AFTER = 'after';
    public const POSITION_BEFORE = 'before';

    public const CATEGORY_FORCE_USE_CANONICAL = 'amshopby_page_force_use_canonical';
    public const MATCHED_PAGE = 'amshopby_matched_page';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyPage\Model\ResourceModel\Page::class);
    }
}
