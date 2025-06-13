<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Product;

use Amasty\Rolepermissions\Helper\Data;
use Amasty\Rolepermissions\Model\State\NewProductSavingFlag;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveBeforeObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var NewProductSavingFlag
     */
    private $newProductSavingFlag;

    public function __construct(
        Data $helper,
        RequestInterface $request,
        AuthorizationInterface $authorization,
        Session $authSession,
        NewProductSavingFlag $newProductSavingFlag
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->authorization = $authorization;
        $this->authSession = $authSession;
        $this->newProductSavingFlag = $newProductSavingFlag;
    }

    public function execute(Observer $observer): void
    {
        if ($this->request->getModuleName() == 'api') {
            return;
        }

        /** @var Product $product */
        $product = $observer->getProduct();

        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::save_products')) {
            $this->helper->redirectHome();
        }

        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::product_owner')
            && $this->authSession->getUser()) {
            $product->unsetData('amrolepermissions_owner');
        }

        $rule = $this->helper->currentRule();

        if (!$rule) {
            return;
        }

        if (!$rule->checkProductPermissions($product)
            && !$rule->checkProductOwner($product)
        ) {
            $this->helper->redirectHome();
        } elseif (!$product->getId()) {
            $this->newProductSavingFlag->setIsSaving(true);
        }
    }
}
