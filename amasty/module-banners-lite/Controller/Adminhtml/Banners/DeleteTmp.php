<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Controller\Adminhtml\Banners;

use Amasty\BannersLite\Model\BannerImageUpload;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class DeleteTmp extends Action
{
    /**
     * @var BannerImageUpload
     */
    private $imageUploader;

    public function __construct(
        Context $context,
        BannerImageUpload $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    public function execute()
    {
        $result = [];
        $file = $this->getRequest()->getParam(Upload::PARAM_NAME);

        if ($file) {
            try {
                $this->imageUploader->deleteFromTmp($file);
            } catch (\Exception $e) {
                $result[] = ['error' => true, 'message' => $e->getMessage()];
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($result);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_SalesRule::quote');
    }
}
