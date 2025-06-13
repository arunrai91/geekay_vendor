<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Plugin\ShopbySeo\Helper;

use Amasty\ShopbyPage\Api\Data\PageInterface;
use Amasty\ShopbyPage\Model\Page;
use Amasty\ShopbySeo\Helper\Meta as ShopbySeoMeta;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Overrides Amasty_ShopbySeo settings for custom pages.
 * Checks if a current page is custom to replace Amasty_ShopbySeo settings with Amasty_ShopbyPage settings.
 */
class Meta
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function aroundGetIndexTagByData(
        ShopbySeoMeta $subject,
        \Closure $proceed,
        bool $indexTag,
        DataObject $data
    ): bool {
        /** @var PageInterface $page */
        $page = $this->registry->registry(Page::MATCHED_PAGE);
        if (!$page || !$page->getTagRobots()) {
            return $proceed($indexTag, $data);
        }

        return $indexTag;
    }

    /**
     * If a current page is custom Amasty_ShopbyPage robots setting will be used.
     */
    public function aroundSetPageTags(
        ShopbySeoMeta $subject,
        \Closure $proceed,
        PageConfig $pageConfig
    ): void {
        /** @var PageInterface $page */
        $page = $this->registry->registry(Page::MATCHED_PAGE);
        if (!$page || !$page->getTagRobots()) {
            $proceed($pageConfig);
        }
    }
}
