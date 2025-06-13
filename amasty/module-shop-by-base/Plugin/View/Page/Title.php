<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\View\Page;

use Amasty\ShopbyBase\Model\Request\PageTitleRegistry;

/**
 * Save category meta title (!without prefixes and suffixes) for further use in customizers.
 */
class Title
{
    /**
     * @var PageTitleRegistry
     */
    private PageTitleRegistry $pageTitleRegistry;

    public function __construct(
        PageTitleRegistry $pageTitleRegistry
    ) {
        $this->pageTitleRegistry = $pageTitleRegistry;
    }

    /**
     * @param \Magento\Framework\View\Page\Title $subject
     * @param string $title
     */
    public function beforeSet(\Magento\Framework\View\Page\Title $subject, $title)
    {
        $this->pageTitleRegistry->set($title, true);
    }

    /**
     * @param \Magento\Framework\View\Page\Title $subject
     */
    public function beforeUnsetValue(\Magento\Framework\View\Page\Title $subject)
    {
        $this->pageTitleRegistry->set('', true);
    }
}
