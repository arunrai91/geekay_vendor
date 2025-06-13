<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\DB\Select;

class AttributeWeight
{
    public const ATTRIBUTES_LIMIT = 500;
    private const ATTRIBUTE_CODE = 'attribute_code';
    private const SEARCH_WEIGHT = 'search_weight';

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Returns array of attribute codes and weights
     *
     * @param bool $isSearchableOnly
     * @return array{string, string} [attribute_code => search_weight]
     */
    public function getWeights(bool $isSearchableOnly = false): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $result = [];

        if ($isSearchableOnly) {
            $attributeCollection->addIsSearchableFilter();
            $attributeCollection->setPageSize(self::ATTRIBUTES_LIMIT);
            $attributeCollection->setCurPage(1);
        }

        $select = $attributeCollection->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns(['main_table.' . self::ATTRIBUTE_CODE, 'additional_table.' . self::SEARCH_WEIGHT]);

        foreach ($attributeCollection->getData() as $attributeData) {
            $result[$attributeData[self::ATTRIBUTE_CODE]] = $attributeData[self::SEARCH_WEIGHT];
        }

        return $result;
    }
}
