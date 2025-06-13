<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page;

use Amasty\ShopbyPage\Model\Request\Page\Registry as PageRegistry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Selection extends Template
{
    /**
     * @var PageRegistry
     */
    private PageRegistry $pageRegistry;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'selection.phtml';

    public function __construct(
        PageRegistry $pageRegistry,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRegistry = $pageRegistry;
    }

    /**
     * Get attribute values url
     * @return string
     */
    public function getSelectionUrl()
    {
        return $this->getUrl('amasty_shopbypage/page/selection');
    }

    /**
     * Get add attribute values row url
     * @return string
     */
    public function getAddSelectionUrl()
    {
        return $this->getUrl('amasty_shopbypage/page/addSelection');
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        /** @var \Amasty\ShopbyPage\Model\Page $model */
        $model = $this->pageRegistry->get();
        $conditions = $model->getConditions();

        return $conditions && !is_string($conditions) ? count($conditions) : 0;
    }
}
