<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Controller\Adminhtml\Option;

class Settings extends \Amasty\ShopbyBase\Controller\Adminhtml\Option
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);
        return $resultLayout;
    }
}
