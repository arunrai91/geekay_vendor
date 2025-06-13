<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

use Amasty\ShopbyBase\Controller\Adminhtml\Option\Save as ShopbyBaseOptionSave;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

class Save extends ShopbyBaseOptionSave
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyBrand::slider');
    }

    protected function _redirectRefer()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
