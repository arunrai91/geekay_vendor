<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\OptionSettings;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Store\Model\Store;

class Save
{
    public const SLIDER_IMAGE_DELETE = 'slider_image_delete';

    public const IMAGE_DELETE = 'image_delete';

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $repository;

    /**
     * @var ImageFileResolver
     */
    private $imageFileResolver;

    public function __construct(OptionSettingRepositoryInterface $repository, ImageFileResolver $imageFileResolver)
    {
        $this->repository = $repository;
        $this->imageFileResolver = $imageFileResolver;
    }

    /**
     * Resolve option settings data save.
     */
    public function saveData(string $attributeCode, int $optionId, int $storeId, array $data): OptionSettingInterface
    {
        $model = $this->repository->getByCode($attributeCode, $optionId, $storeId);
        if (!$model->getId()) {
            $model->setValue($optionId);
            //backward compatibility
            $model->setFilterCode(FilterSetting::convertToFilterCode($attributeCode));
            $model->setAttributeCode($attributeCode);
            $model->setStoreId($storeId);
        } elseif ($model->getStoreId() != $storeId) {
            $model->setId(null);
            $model->isObjectNew(true);
            $model->setStoreId($storeId);
        }

        if (!empty($data)) {
            $defaultModel = $this->repository->getByCode($attributeCode, $optionId, Store::DEFAULT_STORE_ID);
            $this->processImages($model, $defaultModel, $data);
            $this->processSliderImage($model, $defaultModel, $data);
            $this->processUseDefault($data);
        }

        $model->addData($data);
        $this->repository->save($model);

        return $model;
    }

    private function processUseDefault(array &$data): void
    {
        if (empty($data['use_default']) || !is_array($data['use_default'])) {
            return;
        }

        foreach ($data['use_default'] as $field) {
            $data[$field] = null;
        }
    }

    /**
     * Save image.
     */
    private function processImages(
        OptionSettingInterface $model,
        OptionSettingInterface $defaultModel,
        array &$data
    ): void {
        $field = OptionSettingInterface::IMAGE;
        $useDefaultImage = $this->isUseDefault($field, $data);

        if (isset($data[self::IMAGE_DELETE])
            || ($useDefaultImage && $model->getImage() !== $defaultModel->getImage())
        ) {
            $this->imageFileResolver->resolveRemoveImage($model);
            $data[$field] = '';
        }

        if (!$useDefaultImage) {
            try {
                $imageName = $this->imageFileResolver->resolveImageUpload($model, $field);
                $data[$field] = $imageName;
            } catch (\Exception $e) {
                $this->processImageException($e);
            }
        }
    }

    /**
     * Save slider_image.
     */
    private function processSliderImage(
        OptionSettingInterface $model,
        OptionSettingInterface $defaultModel,
        array &$data
    ): void {
        $field = OptionSettingInterface::SLIDER_IMAGE;
        $useDefaultImage = $this->isUseDefault($field, $data);

        if (isset($data[self::SLIDER_IMAGE_DELETE])
            || ($useDefaultImage && $model->getSliderImage() !== $defaultModel->getSliderImage())
        ) {
            $this->imageFileResolver->resolveRemoveSliderImage($model);
            $data[$field] = '';
        }

        if (!$useDefaultImage) {
            try {
                $data[$field] = $this->imageFileResolver->resolveImageSliderUpload($model, $field);
            } catch (\Exception $e) {
                $this->processImageException($e);
            }
        }
    }

    /**
     * @param string $field
     * @param array $data
     */
    private function isUseDefault($field, $data): bool
    {
        return isset($data['use_default']) && in_array($field, $data['use_default']);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processImageException(\Exception $e): void
    {
        if ($e->getCode() !== \Magento\Framework\File\Uploader::TMP_NAME_EMPTY
            && $e->getMessage() !== '$_FILES array is empty'
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
