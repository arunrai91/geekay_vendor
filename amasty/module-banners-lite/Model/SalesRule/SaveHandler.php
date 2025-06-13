<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\SalesRule;

use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\Banner;
use Amasty\BannersLite\Model\BannerFactory;
use Amasty\BannersLite\Model\BannerRuleFactory;
use Amasty\BannersLite\Model\Cache;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;
use Magento\SalesRule\Model\Rule;

/**
 * Sales Rule additional save handler
 * save image banner and banner rule data
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * @var BannerRuleRepositoryInterface
     */
    private $bannerRuleRepository;

    /**
     * @var BannerRuleFactory
     */
    private $bannerRuleFactory;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Snapshot
     */
    private $snapshot;

    /**
     * Flag for flush product cache on banner rule save.
     *
     * @var bool
     */
    private $isBannerModified = false;

    public function __construct(
        BannerRepositoryInterface $bannerRepository,
        MetadataPool $metadataPool,
        BannerFactory $bannerFactory,
        BannerRuleRepositoryInterface $bannerRuleRepository,
        BannerRuleFactory $bannerRuleFactory,
        ImageProcessor $imageProcessor,
        Serializer $serializerBase, //@deprecated backward compatibility
        Cache $cache,
        Snapshot $snapshot
    ) {
        $this->bannerRepository = $bannerRepository;
        $this->metadataPool = $metadataPool;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRuleRepository = $bannerRuleRepository;
        $this->bannerRuleFactory = $bannerRuleFactory;
        $this->imageProcessor = $imageProcessor;
        $this->cache = $cache;
        $this->snapshot = $snapshot;
    }

    /**
     * Stores Promo Banners value from Sales Rule extension attributes
     *
     * @param Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attributes = $entity->getExtensionAttributes() ?: [];

        $this->isBannerModified = false;
        if (isset($attributes[BannerInterface::EXTENSION_CODE])) {
            $inputData = $attributes[BannerInterface::EXTENSION_CODE];
            unset($attributes[BannerInterface::EXTENSION_CODE]);
            $this->saveBannerData($entity, $inputData);
        }
        $this->saveBannerRule($entity, $attributes);

        return $entity;
    }

    private function saveBannerData(Rule $entity, array $inputData): void
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);

        /** @var BannerInterface|array $data */
        foreach ($inputData as $key => $data) {
            $promoBanner = $this->getPromoBanner($ruleLinkId, $key);
            $snapshotData = $promoBanner->getData();

            $this->prepareData($data, $key);
            $this->prepareBannerForSave($promoBanner, $data, $ruleLinkId);
            if (!$this->isBannerModified) {
                $this->isBannerModified = $this->isBannerModified($snapshotData, $promoBanner->getData());
            }
            if ($this->isBannerModified) {
                $this->bannerRepository->save($promoBanner);
            }
        }
    }

    private function getPromoBanner(int $ruleLinkId, int $type): Banner
    {
        try {
            $promoBanner = $this->bannerRepository->getByBannerType($ruleLinkId, $type);
        } catch (NoSuchEntityException $exception) {
            $promoBanner = $this->bannerFactory->create();
        }

        return $promoBanner;
    }

    /**
     * @param array|BannerInterface $data
     * @param int $type
     *
     * @return void
     */
    private function prepareData(&$data, int $type): void
    {
        if ($data instanceof BannerInterface) {
            $data = $data->getData();
        }
        if (isset($data[BannerInterface::BANNER_IMAGE])
            && is_array($data[BannerInterface::BANNER_IMAGE])
        ) {
            $data[BannerInterface::BANNER_IMAGE] = $data[BannerInterface::BANNER_IMAGE][0]['name'] ?? '';
        }
        $data[BannerInterface::BANNER_TYPE] = $type;
    }

    private function prepareBannerForSave(Banner $promoBanner, array $data, int $ruleLinkId): void
    {
        if (!$this->isEqualImage($promoBanner, $data)) {
            if ($promoBanner->getBannerImage()) {
                //delete old banner
                $this->imageProcessor->deleteImage($promoBanner->getBannerImage());
                $promoBanner->setBannerImage(null);
            }
            $this->isBannerModified = true;
        }

        $promoBanner->addData($data);
        if ((int)$promoBanner->getSalesruleId() !== $ruleLinkId) {
            $promoBanner->setEntityId(null);
            $promoBanner->setSalesruleId($ruleLinkId);
        }
    }

    private function isEqualImage(Banner $promoBanner, array $newData): bool
    {
        $oldImage = $promoBanner->getBannerImage();
        $newImage = $newData[BannerInterface::BANNER_IMAGE] ?? null;

        return $oldImage == $newImage;
    }

    private function isBannerModified(array $snapshotData, array $promoBanner): bool
    {
        unset($snapshotData[BannerInterface::BANNER_IMAGE], $promoBanner[BannerInterface::BANNER_IMAGE]);

        return $snapshotData != $promoBanner;
    }

    private function saveBannerRule(Rule $entity, array $attributes): void
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);

        try {
            $bannerRule = $this->bannerRuleRepository->getBySalesruleId($ruleLinkId);
        } catch (NoSuchEntityException $exception) {
            $bannerRule = $this->bannerRuleFactory->create();
        }

        if ($bannerRule->getId()) {
            $this->snapshot->registerSnapshot($bannerRule);
        }

        $this->convertCategoryIds($attributes);

        $bannerRule->addData($attributes);

        if (!isset($attributes[BannerRuleInterface::BANNER_PRODUCT_SKU]) && !$bannerRule->getBannerProductSku()) {
            $bannerRule->setBannerProductSku("");
        }

        if ((int)$bannerRule->getSalesruleId() !== $ruleLinkId) {
            $bannerRule->setEntityId(null);
            $bannerRule->setSalesruleId($ruleLinkId);
        }

        if ($isRuleModified = $this->snapshot->isModified($bannerRule)) {
            $this->bannerRuleRepository->save($bannerRule);
        }

        if ($this->isBannerModified || $isRuleModified) {
            $this->cache->cleanProductCache($bannerRule->getData());
        }
    }

    private function convertCategoryIds(array &$attributes): void
    {
        if (isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
            && is_array($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]
                = implode(',', $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]);
        } elseif (isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])
            && $attributes[BannerRuleInterface::SHOW_BANNER_FOR] == '2'
            && !isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES] = '';
        } elseif (!isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])) {
            $attributes[BannerRuleInterface::SHOW_BANNER_FOR] = BannerRuleInterface::ALL_PRODUCTS;
        }
    }
}
