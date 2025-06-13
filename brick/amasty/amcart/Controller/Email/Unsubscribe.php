<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Email;

use Amasty\Acart\Model\Blacklist;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NotFoundException;

class Unsubscribe extends Url
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $history = $this->getHistory();

        if ($history) {
            try {
                $blacklist = $this->blacklistRepository->getByCustomerEmail($history->getCustomerEmail());
            } catch (NotFoundException $e) {
                $blacklist = $this->blacklistFactory->create();
            }

            if (!$blacklist->getBlacklistId()) {
                $blacklist->addData(
                    [
                        Blacklist::CUSTOMER_EMAIL => $history->getCustomerEmail()
                    ]
                );

                try {
                    $this->blacklistRepository->save($blacklist);
                } catch (CouldNotSaveException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(__('You have been unsubscribed'));
        }

        return $resultRedirect->setPath('checkout/cart');
    }
}
