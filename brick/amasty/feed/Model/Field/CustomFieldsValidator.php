<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Field;

use Amasty\Feed\Api\CustomFieldsRepositoryInterface;
use Amasty\Feed\Model\Field\ResourceModel\CollectionFactory as FieldCollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomFieldsValidator
{
    /**
     * Storage for validated products
     * @var array [product_id => [code => [rule]]]
     */
    private $validatedProductRules = [];

    /**
     * @var CustomFieldsRepositoryInterface
     */
    private $customFieldsRepository;

    /**
     * @var array
     */
    private $rules;

    /**
     * @var FieldCollectionFactory
     */
    private $fieldCollectionFactory;

    /**
     * @var array
     */
    private $customFields;

    public function __construct(
        CustomFieldsRepositoryInterface $customFieldsRepository,
        FieldCollectionFactory $collectionFactory,
        array $customFields = []
    ) {
        $this->customFieldsRepository = $customFieldsRepository;
        $this->fieldCollectionFactory = $collectionFactory;
        $this->customFields = $customFields;
    }

    public function setCustomFields(array $customFields): void
    {
        $this->customFields = $customFields;
    }

    public function prepareRules(Collection $collection): array
    {
        $this->rules = [];
        foreach ($collection->getItems() as $product) {
            $this->getValidRules($product);
        }

        return $this->validatedProductRules;
    }

    /**
     * @param ProductInterface $product
     *
     * @return Condition[] [code => [rule]]
     */
    public function getValidRules(ProductInterface $product): array
    {
        if (!array_key_exists($product->getId(), $this->validatedProductRules)) {
            $this->validatedProductRules[$product->getId()] = [];
            foreach ($this->getRules() as $code => $rules) {
                // each rule is a pair of actions
                foreach ($rules as $rule) {
                    if ($rule->getConditions()->validate($product)) {
                        $this->validatedProductRules[$product->getId()][$code] = $rule;
                        break;
                    }
                }
            }
        }

        return $this->validatedProductRules[$product->getId()];
    }

    /**
     * @return Condition[] [[code => [rule]]]
     * @throws NoSuchEntityException
     */
    private function getRules(): array
    {
        if (!empty($this->rules)) {
            return $this->rules;
        }

        foreach ($this->getConditions() as $customField) {
            foreach ($customField as $condition) {
                $rule = $this->customFieldsRepository->getConditionModel($condition['id']);
                $this->rules[$condition['code']][] = $rule;
            }
        }

        return $this->rules;
    }

    /**
     * @return array [code => [entity_id, code]]
     * @throws NoSuchEntityException
     */
    private function getConditions(): array
    {
        $conditions = [];
        $data = $this->fieldCollectionFactory->create()->getCustomConditions($this->customFields);
        if ($data) {
            foreach ($data as $record) {
                $conditions[$record['code']][] = ['id' => $record['entity_id'], 'code' => $record['code']];
            }
        }

        $conformityArray = array_diff_key($this->customFields, $conditions);
        if (!empty($this->customFields) && $conformityArray) {
            throw new NoSuchEntityException(
                __(
                    'Error(s) occurred during feed generation, attribute code(s): "%1"',
                    implode(",", $conformityArray)
                )
            );
        }

        return $conditions;
    }
}
