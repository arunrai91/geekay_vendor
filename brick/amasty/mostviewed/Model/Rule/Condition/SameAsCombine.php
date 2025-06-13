<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

class SameAsCombine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var ProductFactory
     */
    private ProductFactory $productFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType(\Magento\CatalogRule\Model\Rule\Condition\Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->productFactory->create()->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Amasty\Mostviewed\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Custom Fields'),
                    'value' => [
                        [
                            'label' => __('Price'),
                            'value' => \Amasty\Mostviewed\Model\Rule\Condition\Price::class,
                        ],
                    ]
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return 'same_as_conditions';
    }

    /**
     * @return mixed
     */
    public function getSameAsConditions()
    {
        return $this->getData('conditions');
    }
}
