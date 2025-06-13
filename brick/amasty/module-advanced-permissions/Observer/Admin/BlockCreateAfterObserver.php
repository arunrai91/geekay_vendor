<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Scope;
use Magento\Backend\Block\Store\Switcher as StoreSwitcher;
use Magento\Backend\Block\Template;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit as AttributeEdit;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BlockCreateAfterObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $backendHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $classesToCheck;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Backend\Helper\Data $backendHelper,
        array $classesToCheck = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->response = $response;
        $this->backendHelper = $backendHelper;
        $this->classesToCheck = $classesToCheck;
    }

    public function execute(Observer $observer): void
    {
        $block = $observer->getBlock();

        if (!($block instanceof StoreSwitcher)) {
            return;
        }

        foreach ($this->classesToCheck as $class) {
            if (is_a($block, $class)) {
                $this->modifyBlockAfterCreation($block);

                return;
            }
        }

        $this->redirectToAvailableStoreView();
    }

    private function redirectToAvailableStoreView(): void
    {
        $rule = $this->helper->currentRule();

        if (!$this->request->getParam('store')
            && !$this->request->getParam('website')
        ) {
            $views = $rule->getScopeStoreviews();
            if ($views) { // Redirect to first available store view
                $redirectUrl = $this->backendHelper->getUrl(
                    '*/*/*',
                    [
                        '_current' => true,
                        'store'    => $views[0]
                    ]
                );

                $this->response->setRedirect($redirectUrl);
            }
        }
    }

    private function modifyBlockAfterCreation(Template $block): void
    {
        $rule = $this->helper->currentRule();

        if ($block instanceof AttributeEdit
            && $rule->getScopeAccessMode() != Scope::MODE_NONE
        ) {
            $block->removeButton('save')->removeButton('save_and_edit_button')->removeButton('delete');
        }
    }
}
