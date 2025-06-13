<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\ElasticSearch\Model\Indexer\Data\External\RelevanceBoostDataMapper;
use Amasty\ElasticSearch\Utils\AttributeUtil;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\StoreManager;

class SortingProvider
{
    public const DEFAULT_SORTING = 'relevance';
    public const DEFAULT_DIRECTION = 'desc';

    /**
     * List of fields that need to skipp by default.
     * @var array
     */
    private $skippedFields = ['entity_id'];

    /**
     * Default mapping for special fields.
     * @var array
     */
    private $map = ['relevance' => '_score'];

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var AttributeUtil
     */
    private $attributeUtil;

    public function __construct(
        Config $eavConfig,
        Session $customerSession,
        Registry $registry,
        StoreManager $storeManager,
        array $skippedFields = [],
        array $map = [],
        ?AttributeUtil $attributeUtil = null // TODO: move to not optional argument and remove OM
    ) {
        $this->eavConfig = $eavConfig;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;
        $this->skippedFields = array_merge($this->skippedFields, $skippedFields);
        $this->map = array_merge($this->map, $map);
        $this->attributeUtil = $attributeUtil ?? ObjectManager::getInstance()->get(AttributeUtil::class);
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(RequestInterface $request)
    {
        $sortings = [];

        foreach ($this->getRequestedSorting($request) as $item) {
            if (!$item['field'] || in_array($item['field'], $this->skippedFields)) {
                continue;
            }
            $attributeCode = $item['field'];

            $fieldName = $this->getFieldName($attributeCode);

            $sortings[] = [
                $fieldName => [
                    'order' => strtolower($item['direction'])
                ]
            ];
        }

        return $sortings;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function getRequestedSorting(RequestInterface $request)
    {
        $result = [];

        if (method_exists($request, 'getSort') && $request->getSort()) {
            $result = $request->getSort();
        }

        if (empty($result)) {
            $result[] = ['field' => self::DEFAULT_SORTING, 'direction' => self::DEFAULT_DIRECTION];
        }

        return $result;
    }

    private function getFieldName(string $attributeCode): string
    {
        if ($attributeCode === 'price') {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            return 'price_' . $customerGroupId;
        }

        if ($attributeCode === 'position') {
            $categoryId = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
            return 'category_position_' . $categoryId;
        }

        if (isset($this->map[$attributeCode])) {
            return $this->map[$attributeCode];
        }

        $attribute = $this->eavConfig->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );

        $fieldName = $attributeCode;
        if ($this->attributeUtil->isComplexAttribute($attribute)) {
            $fieldName .= '_value';
        }

        if ($this->isUsedSortField($attribute)) {
            return $fieldName . '.sort_' . $attributeCode;
        }

        return $fieldName;
    }

    private function isUsedSortField(Attribute $attribute): bool
    {
        return $attribute->getUsedForSortBy()
            && (!in_array($attribute->getBackendType(), ['int', 'smallint', 'decimal'], true)
                || $this->attributeUtil->isComplexAttribute($attribute));
    }
}
