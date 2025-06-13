<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

use Amasty\ShopbyBrand\Model\Request\BrandRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\ShopbyBase\Helper\OptionSetting;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var  OptionSetting
     */
    private $settingHelper;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var BrandRegistry
     */
    private BrandRegistry $brandRegistry;

    public function __construct(
        PageFactory $resultPageFactory,
        OptionSetting $optionSetting,
        BrandRegistry $brandRegistry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->settingHelper = $optionSetting;
        $this->request = $context->getRequest();
        $this->brandRegistry = $brandRegistry;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyBrand::slider');
    }

    /**
     * Edit page
     *
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $model = $this->loadSettingModel();
            $model->setData('id', $model->getData('option_setting_id'));
            $this->brandRegistry->set($model);
            /** @var \Magento\Backend\Model\View\Result\Page $result */
            $result = $this->resultPageFactory->create();
            $result->addBreadcrumb(__('Manage Brand Slider'), __('Manage Brand Slider'));
            $result->addBreadcrumb(
                __('Edit Improved Navigation Brand Slider'),
                __('Edit Improved Navigation Brand Slider')
            );
            $result->getConfig()->getTitle()->prepend(__('Improved Navigation Brand Slider'));
            $result->getConfig()->getTitle()->prepend($model->getData('title'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the brand.'));
            $result = $this->resultRedirectFactory->create();
            $result->setPath('*/*/');
        }

        return $result;
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\OptionSettingInterface
     * @throws NoSuchEntityException
     */
    private function loadSettingModel()
    {
        $attributeCode = $this->request->getParam('attribute_code');
        $optionId = (int) $this->request->getParam('option_id');
        $storeId = (int) $this->request->getParam('store', 0);
        if (!$attributeCode || !$optionId) {
            throw new NoSuchEntityException();
        }
        $model = $this->settingHelper->getSettingByOption($optionId, $attributeCode, $storeId);
        if (!$model->getId()) {
            throw new NoSuchEntityException();
        }
        $model->setCurrentStoreId($storeId);

        return $model;
    }
}
