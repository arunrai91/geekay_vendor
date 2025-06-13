<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Backend;

use Amasty\Base\Model\Serializer;
use Amasty\Xsearch\Helper\Data;
use Amasty\Xsearch\Model\CustomSearchAttributes;
use Amasty\Xsearch\Model\System\Config\AttributeWeight;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Weight extends \Magento\Framework\App\Config\Value
{

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var Data
     */
    private $xSearchHelper;

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AttributeWeight
     */
    private $attributeWeight;

    /**
     * @var CustomSearchAttributes
     */
    private $customSearchAttributes;

    /**
     * Weight constructor.
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param Data $xSearchHelper
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Random $mathRandom
     * @param Attribute $attributeResource
     * @param Serializer $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param AttributeWeight|null $attributeWeight
     * @param CustomSearchAttributes|null $customSearchAttributes
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        Data $xSearchHelper,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        Attribute $attributeResource,
        Serializer $serializer,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?AttributeWeight $attributeWeight = null, // TODO move to not optional
        ?CustomSearchAttributes $customSearchAttributes = null
    ) {
        $this->mathRandom = $mathRandom;
        $this->attributeRepository = $attributeRepository;
        $this->xSearchHelper = $xSearchHelper;
        $this->attributeResource = $attributeResource;
        $this->serializer = $serializer;
        $this->attributeWeight = $attributeWeight
            ?? ObjectManager::getInstance()->get(AttributeWeight::class);
        $this->customSearchAttributes = $customSearchAttributes
            ?? ObjectManager::getInstance()->get(CustomSearchAttributes::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];

        if (is_array($value)) {
            foreach ($value as $data) {
                if (!$data
                    || !is_array($data)
                    || !(isset($data['weight']) && isset($data['attributes_weight']))
                ) {
                    continue;
                }

                $result[$data['attributes_weight']] = $data['weight'];
            }
        } else {
            $result = $this->serializer->unserialize($value);
        }

        if ($result && is_array($result)) {
            $this->setWeightAndSearchable($result);
            $this->deactivateSearchable($result);
            $this->setValue($this->serializer->serialize($result));
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        $attributeWeights = $this->attributeWeight->getWeights(true);
        if ($originValue = $this->getValue()) {
            $originValue = $this->serializer->unserialize($originValue);
            foreach ($this->customSearchAttributes->getAttributes() as $code => $attribute) {
                if (isset($originValue[$code])) {
                    $attributeWeights[$code] = $originValue[$code];
                }
            }
        }
        $value = $this->encodeArrayFieldValue($attributeWeights);

        $this->setValue($value);

        return $this;
    }

    /**
     * @param array $value
     * @return array
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $attributes => $weight) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['attributes' => $attributes, 'weight' => $weight];
        }

        return $result;
    }

    private function setWeightAndSearchable(array $result): void
    {
        foreach ($result as $attributeCode => $weight) {
            try {
                $attribute = $this->attributeRepository->get($attributeCode);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
            $attribute->setSearchWeight($weight);
            $attribute->setIsSearchable(true);

            /* saving with resource model, because magento repository on version less 2.1.8 break attribute options*/
            $this->attributeResource->save($attribute);
        }
    }

    /**
     * Set in the attribute is_searchable in false
     * @param array $values
     * @return void
     */
    private function deactivateSearchable(array $values): void
    {
        $productAttributes = $this->attributeWeight->getWeights(true);

        foreach ($values as $attributeCode => $weight) {
            unset($productAttributes[$attributeCode]);
        }

        foreach ($productAttributes as $attribute => $value) {
            try {
                $attribute = $this->attributeRepository->get($attribute);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
            $attribute->setIsSearchable(false);
            $this->attributeResource->save($attribute);
        }
    }
}
