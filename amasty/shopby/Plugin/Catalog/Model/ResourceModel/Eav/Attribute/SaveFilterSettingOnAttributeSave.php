<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Eav\Attribute;

use Amasty\Base\Model\Serializer;
use Amasty\Shopby\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Model\FilterSettingFactory;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Model\Cache\Type;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttributeResource;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveFilterSettingOnAttributeSave
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var FilterSettingFactory
     */
    private $filterSettingFactory;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var  TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        FilterSettingFactory $filterSettingFactory,
        ConfigFactory $configFactory,
        FilterSettingHelper $filterSettingHelper,
        Serializer $serializer,
        TypeListInterface $typeList,
        OptionSettingRepositoryInterface $optionSettingRepository
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->configFactory = $configFactory;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->serializer = $serializer;
        $this->cacheTypeList = $typeList;
        $this->optionSettingRepository = $optionSettingRepository;
    }

    /**
     * @param EavAttributeResource $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws \Exception
     */
    public function aroundSave(EavAttributeResource $subject, \Closure $proceed)
    {
        if (!$subject->hasData('attribute_code')) {
            return $proceed();
        }

        $filterSetting = $this->getFilterSetting($subject);
        $this->prepareFilterSettingData($subject, $filterSetting);

        $connection = $filterSetting->getResource()->getConnection();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $result */
        $result = $proceed();
        try {
            $filterSetting->setAttributeId((int)$result->getAttributeId());
            $connection->beginTransaction();
            $this->filterSettingRepository->save($filterSetting);

            foreach ($this->filterSettingHelper->getKeyValueForCategoryFilterConfig() as $dataKey => $configPath) {
                if ($subject->getData($dataKey) !== null) {
                    $configModel = $this->configFactory->create();
                    $configModel->setDataByPath($configPath, $subject->getData($dataKey));
                    $configModel->save();
                }
            }

            $connection->commit();
            $this->cacheTypeList->invalidate(Type::TYPE_IDENTIFIER);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }

    private function getFilterSetting(EavAttributeResource $attributeResource): FilterSettingInterface
    {
        try {
            $filterSetting = $this->filterSettingRepository
                ->loadByAttributeCode($attributeResource->getAttributeCode());
        } catch (NoSuchEntityException $e) {
            $filterSetting = $this->filterSettingFactory->create();
        }

        return $filterSetting;
    }

    private function prepareData(array $data): array
    {
        $multipleData = ['categories_filter', 'attributes_filter', 'attributes_options_filter'];
        foreach ($multipleData as $multiple) {
            if (array_key_exists($multiple, $data) && is_array($data[$multiple])) {
                $data[$multiple] = implode(',', array_filter($data[$multiple], [$this, 'callbackNotEmpty']));
            } elseif (!array_key_exists($multiple, $data)) {
                $data[$multiple] = '';
            }
        }

        $nullableFields = [
            FilterSettingInterface::SLIDER_MIN,
            FilterSettingInterface::SLIDER_MAX,
            FilterSettingInterface::RANGE_STEP
        ];
        foreach ($nullableFields as $nullableField) {
            if (!isset($data[$nullableField]) || $data[$nullableField] === '') {
                $data[$nullableField] = null;
            }
        }

        return $data;
    }

    private function callbackNotEmpty(string $element): bool
    {
        return $element !== '';
    }

    private function prepareFilterSettingData(
        EavAttributeResource $attributeResource,
        FilterSettingInterface $filterSetting
    ): void {
        $data = $this->prepareData($attributeResource->getData());
        $data['tooltip'] = $this->serializer->serialize($data['tooltip'] ?? '');
        $data['attribute_url_alias'] =
            isset($data['attribute_url_alias'])
                ? $this->serializer->serialize($data['attribute_url_alias'])
                : '';
        //in the case of a conflict when column 'tooltip' exists in catalog_eav_attribute
        $attributeResource->setData('tooltip', null);
        $filterSetting->addData($data);

        if (empty($filterSetting->getAttributeCode())) {
            $filterSetting->setAttributeCode($attributeResource->getAttributeCode());
        }
    }
}
