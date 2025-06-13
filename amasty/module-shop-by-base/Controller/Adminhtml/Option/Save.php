<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Controller\Adminhtml\Option;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface as OSInterface;
use Amasty\ShopbyBase\Model\Cache\Type;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class Save extends \Amasty\ShopbyBase\Controller\Adminhtml\Option
{
    /**
     * @var  TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSettings\Save
     */
    private $saveOptionSettings;

    public function __construct(
        Action\Context $context,
        TypeListInterface $typeList,
        LoggerInterface $logger,
        OptionSettingRepositoryInterface $optionSettingRepository,
        IndexerRegistry $indexerRegistry,
        \Amasty\ShopbyBase\Model\OptionSettings\Save $saveOptionSettings
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $typeList;
        $this->logger = $logger;
        $this->optionSettingRepository = $optionSettingRepository;
        $this->indexerRegistry = $indexerRegistry;
        $this->saveOptionSettings = $saveOptionSettings;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $optionId = (int)$this->getRequest()->getParam('option_id');
        $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);

        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $issetUrlAlias = isset($data['url_alias']) && $data['url_alias'];
                if ($issetUrlAlias && !$this->isUniqueAlias($data['url_alias'], $optionId)) {
                    $this->messageManager->addErrorMessage(
                        __('A brand with the same URL alias already exists. Please enter a unique value.')
                    );

                    if ($this->getRequest()->isAjax()) {
                        return $this->_redirectRefer();
                    } else {
                        return $this->redirectBack($attributeCode, $optionId, $storeId);
                    }
                }
                $data = $this->filterData($data);

                if ($storeId != Store::DEFAULT_STORE_ID) {
                    $this->checkDefaultOption($attributeCode, $optionId);
                }
                $this->saveOptionSettings->saveData($attributeCode, $optionId, $storeId, $data);

                $this->indexerRegistry->get(Fulltext::INDEXER_ID)->invalidate();
                $this->cacheTypeList->invalidate(Type::TYPE_IDENTIFIER);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));
                $this->_session->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $this->redirectBack($attributeCode, $optionId, $storeId);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
            }
        }

        return $this->_redirectRefer();
    }

    private function redirectBack(string $attributeCode, int $optionId, int $storeId): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath(
            '*/*/edit',
            [
                'attribute_code' => $attributeCode,
                'option_id' => $optionId,
                'store' => $storeId
            ]
        );

        return $resultRedirect;
    }

    protected function _redirectRefer()
    {
        /** @var Redirect $resultRedirect */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $resultForward->forward('settings');
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function filterData($data)
    {
        $data[OSInterface::TOP_CMS_BLOCK_ID] = ($data[OSInterface::TOP_CMS_BLOCK_ID] ?? null) ?: null;
        $data[OSInterface::BOTTOM_CMS_BLOCK_ID] = ($data[OSInterface::BOTTOM_CMS_BLOCK_ID] ?? null) ?: null;

        return $data;
    }

    private function isUniqueAlias(string $alias, int $optionId): bool
    {
        try {
            $option = $this->optionSettingRepository->get($alias, 'url_alias');
            $isUnique = $option->getValue() == $optionId;
        } catch (NoSuchEntityException $e) {
            $isUnique = true;
        }

        return $isUnique;
    }

    private function checkDefaultOption(string $attributeCode, int $optionId): void
    {
        $setting = $this->optionSettingRepository->getByCode($attributeCode, $optionId, Store::DEFAULT_STORE_ID);
        if (!$setting->getId()) {
            $this->saveOptionSettings->saveData($attributeCode, $optionId, Store::DEFAULT_STORE_ID, []);
        }
    }
}
