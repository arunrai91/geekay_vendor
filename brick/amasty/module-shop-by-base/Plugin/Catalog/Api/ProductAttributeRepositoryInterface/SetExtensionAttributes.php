<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Api\ProductAttributeRepositoryInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SetExtensionAttributes
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        LoggerInterface $logger,
        Config $eavConfig
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
    }

    public function afterGet(
        ProductAttributeRepositoryInterface $subject,
        ProductAttributeInterface $entity,
        string $attributeCode
    ): ProductAttributeInterface {
        if (!$entity->getExtensionAttributes()->getFilterSetting()) {
            try {
                if (is_numeric($attributeCode)) { // if code is numeric, try to map attribute id to code
                    $attributeCode = $this->getAttributeCode((int)$attributeCode);
                }

                $filterSetting = $this->filterSettingRepository->getByAttributeCode($attributeCode);
                $extensionAttributes = $entity->getExtensionAttributes();
                $extensionAttributes->setFilterSetting($filterSetting);
                $entity->setExtensionAttributes($extensionAttributes);
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
            }
        }

        return $entity;
    }

    /**
     * @throws LocalizedException
     */
    private function getAttributeCode(int $attributeId): string
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeId);

        return $attribute->getAttributeCode();
    }
}
