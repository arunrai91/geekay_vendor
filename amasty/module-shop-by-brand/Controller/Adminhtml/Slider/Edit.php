<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry as CoreRegistry;
use Amasty\ShopbyBrand\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\ShopbyBase\Helper\OptionSetting;

class Edit extends Action
{
    /** @var CoreRegistry */
    private $coreRegistry = null;

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var  OptionSetting */
    private $settingHelper;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        CoreRegistry $registry,
        OptionSetting $optionSetting
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->settingHelper = $optionSetting;
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
            $this->coreRegistry->register(RegistryConstants::FEATURED, $model);
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
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
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $optionId = (int) $this->getRequest()->getParam('option_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
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
