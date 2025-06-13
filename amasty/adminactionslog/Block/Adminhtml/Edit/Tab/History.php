<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\Edit\Tab;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\View\Element\Text\ListText;

class History extends ListText implements TabInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->authorization = $authorization;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('History of Changes');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('History of Changes');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->authorization->isAllowed(AbstractActionsLog::ADMIN_RESOURCE);
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    public function isAjaxLoaded()
    {
        return true;
    }
}
