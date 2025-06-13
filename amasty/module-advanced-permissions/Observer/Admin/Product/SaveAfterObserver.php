<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Product;

use Amasty\Rolepermissions\Model\State\NewProductSavingFlag;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var NewProductSavingFlag
     */
    private $newProductSavingFlag;

    public function __construct(
        RequestInterface $request,
        Session $authSession,
        NewProductSavingFlag $newProductSavingFlag
    ) {
        $this->request = $request;
        $this->authSession = $authSession;
        $this->newProductSavingFlag = $newProductSavingFlag;
    }

    public function execute(Observer $observer): void
    {
        $this->newProductSavingFlag->setIsSaving(false);

        if ($this->request->getModuleName() == 'api') {
            return;
        }

        /** @var Product $product */
        $product = $observer->getProduct();

        if (!$product->getOrigData('entity_id') && !$product->getAmrolepermissionsOwner()) {
            $user = $this->authSession->getUser();

            if ($user) {
                $product->setAmrolepermissionsOwner($user->getId());
                $product->getResource()->saveAttribute($product, 'amrolepermissions_owner');
            }
        }
    }
}
