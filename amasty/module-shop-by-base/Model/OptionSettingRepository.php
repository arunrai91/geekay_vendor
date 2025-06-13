<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting as OptionSettingResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

class OptionSettingRepository implements OptionSettingRepositoryInterface
{
    /**
     * @var OptionSettingResource
     */
    private $resource;

    /**
     * @var OptionSettingFactory
     */
    private $factory;

    /**
     * @var OptionSettingResource\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param OptionSettingResource $resource
     * @param OptionSettingFactory $factory
     * @param OptionSettingResource\CollectionFactory $collectionFactory
     * @param Option\CollectionFactory|null $optionCollectionFactory @deprecated
     */
    public function __construct(
        OptionSettingResource $resource,
        OptionSettingFactory $factory,
        ResourceModel\OptionSetting\CollectionFactory $collectionFactory,
        Option\CollectionFactory $optionCollectionFactory = null
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return OptionSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($value, $field = null)
    {
        $entity = $this->factory->create();
        $this->resource->load($entity, $value, $field);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested option setting doesn\'t exist'));
        }

        return $entity;
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     * @deprecared use getByCode instead
     */
    public function getByParams($filterCode, $optionId, $storeId)
    {
        return $this->getByCode(FilterSetting::convertToAttributeCode($filterCode), (int) $optionId, (int) $storeId);
    }

    public function getByCode(string $attributeCode, int $optionId, int $storeId): OptionSettingInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addLoadFilters($attributeCode, $optionId, $storeId);

        /** @var OptionSettingInterface|AbstractModel $model */
        $model = $collection->getFirstItem();
        if ($storeId !== Store::DEFAULT_STORE_ID && $model->getStoreId() !== Store::DEFAULT_STORE_ID) {
            $defaultModel = $collection->getLastItem();
            foreach ($model->getData() as $key => $value) {
                $isDefault = $value === null;
                if ($isDefault) {
                    $model->setData($key, $defaultModel->getData($key));
                }

                $model->setData($key . '_use_default', $isDefault);
            }
        }

        if ($model->getTitle() === null || $model->getMetaTitle() === null) {
            $eavValue = $collection->getValueFromMagentoEav($optionId, $storeId);
            if ($model->getTitle() === null) {
                $model->setTitle($eavValue);
                //for $storeId == 0
                $model->setData('title_use_default', true);
            }
            if ($model->getMetaTitle() === null) {
                $model->setMetaTitle($eavValue);
                //for $storeId == 0
                $model->setData('meta_title_use_default', true);
            }
        }

        return $model;
    }

    /**
     * @param OptionSettingInterface $optionSetting
     * @return $this
     */
    public function save(OptionSettingInterface $optionSetting)
    {
        $this->resource->save($optionSetting);
        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId): array
    {
        return $this->resource->getAllFeaturedOptionsArray($storeId);
    }

    public function deleteByOptionId(int $optionId): void
    {
        try {
            $table = $this->resource->getTable(OptionSettingRepositoryInterface::TABLE);
            $this->resource->getConnection()->delete($table, ['value = ?' => $optionId]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Unable to delete option with ID %1. Error: %2',
                    [$optionId, $e->getMessage()]
                )
            );
        }
    }
}
