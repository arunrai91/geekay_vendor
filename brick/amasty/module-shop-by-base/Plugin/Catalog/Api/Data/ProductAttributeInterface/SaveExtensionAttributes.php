<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Api\Data\ProductAttributeInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class SaveExtensionAttributes
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(FilterSettingRepositoryInterface $filterSettingRepository)
    {
        $this->filterSettingRepository = $filterSettingRepository;
    }

    public function afterSave(
        ProductAttributeInterface $entity,
        ProductAttributeInterface $result
    ): ProductAttributeInterface {
        $extensionAttributes = $entity->getExtensionAttributes();
        /** @var FilterSettingInterface|null $filterSetting */
        $filterSetting = $extensionAttributes->getFilterSetting();
        if ($filterSetting) {
            $filterSetting->setAttributeId((int)$result->getAttributeId());
            $this->filterSettingRepository->save($filterSetting);

            $resultExtentionAttributes = $result->getExtensionAttributes();
            $resultExtentionAttributes->setFilterSetting($filterSetting);
            $result->setExtensionAttributes($resultExtentionAttributes);
        }

        return $result;
    }
}
