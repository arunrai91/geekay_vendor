<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Attribute
{
    /**
     * @var array
     */
    private $brandOptions;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        AttributeRepository $attributeRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ProductAttributeInterface|IdentityInterface|null
     */
    public function getAttribute(): ?ProductAttributeInterface
    {
        $attributeCode = $this->configProvider->getBrandAttributeCode();

        if (!$attributeCode) {
            return null;
        }

        try {
            return $this->attributeRepository->get($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param int|null $storeId
     * @return AttributeOptionInterface[]|null
     */
    public function getOptions(?int $storeId = null): ?array
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->brandOptions[$storeId])) {
            $this->brandOptions[$storeId] = [];
            $attribute = $this->getAttribute();

            if ($attribute) {
                $attribute->setStoreId($storeId);
                $this->brandOptions[$storeId] = $attribute->getOptions();
                array_shift($this->brandOptions[$storeId]);
            }
        }

        return $this->brandOptions[$storeId];
    }
}
