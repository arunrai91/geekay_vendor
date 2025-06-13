<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Api\ProductAttributeRepositoryInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SetListExtensionAttributes
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        LoggerInterface $logger
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->logger = $logger;
    }

    public function afterGetList(
        ProductAttributeRepositoryInterface $subject,
        SearchResultsInterface $searchCriteria
    ): SearchResultsInterface {
        $productAttributes = [];
        foreach ($searchCriteria->getItems() as $entity) {
            try {
                $filterSetting = $this->filterSettingRepository->getByAttributeCode($entity->getAttributeCode());
                $extensionAttributes = $entity->getExtensionAttributes();
                $extensionAttributes->setFilterSetting($filterSetting);
                $entity->setExtensionAttributes($extensionAttributes);
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
            }

            $productAttributes[] = $entity;
        }
        $searchCriteria->setItems($productAttributes);

        return $searchCriteria;
    }
}
