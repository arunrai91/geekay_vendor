<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\ObjectManagerInterface;

class Allowed extends Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Amasty\Rolepermissions\Model\RuleFactory
     */
    protected $userFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\Rolepermissions\Model\RuleFactory $userFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry = $registry;
        $this->userFactory = $userFactory;
    }

    public function execute()
    {
        $rule = $this->userFactory->create();
        if ($rid = (int) $this->_request->getParam('rid')) {
            $rule->load($rid, 'role_id');
        }

        $this->_coreRegistry->register('amrolepermissions_current_rule', $rule, true);

        return $this->resultLayoutFactory->create();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_roles');
    }
}
