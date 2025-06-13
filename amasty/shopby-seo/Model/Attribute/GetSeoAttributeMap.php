<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Attribute;

use Amasty\ShopbySeo\Helper\Data as SeoHelper;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\DB\Select;

class GetSeoAttributeMap
{
    /**
     * @var SeoHelper
     */
    private $seoHelper;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var array|null [attributeId => attributeCode, ...]
     */
    private $seoAttributeMap;

    public function __construct(SeoHelper $seoHelper, AttributeCollectionFactory $attributeCollectionFactory)
    {
        $this->seoHelper = $seoHelper;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return array [attributeId => attributeCode, ...]
     */
    public function execute(): array
    {
        if ($this->seoAttributeMap === null) {
            /** @var AttributeCollection $attributeCollection */
            $attributeCollection = $this->attributeCollectionFactory->create();

            $attributeCollection->addFieldToFilter('attribute_code', [
                'in' => $this->seoHelper->getSeoSignificantAttributeCodes()
            ]);

            $attributeCollection->getSelect()->reset(Select::COLUMNS);
            $attributeCollection->getSelect()->columns(['attribute_id', 'attribute_code']);

            $this->seoAttributeMap = $attributeCollection->getResource()->getConnection()->fetchPairs(
                $attributeCollection->getSelect()
            );
        }

        return $this->seoAttributeMap;
    }
}
