<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-01T13:02:05+00:00
 * File:          Model/Export/Condition/Product/Found.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Condition\Product;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Found
{
    /**
     * @var \Xtento\OrderExport\Model\Export\Condition\Product
     */
    protected $conditionProduct;

    /**
     * @var \Xtento\OrderExport\Model\Export\Condition\Custom
     */
    protected $conditionCustom;

    /**
     * Found constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param \Xtento\OrderExport\Model\Export\Condition\Product $conditionProduct
     * @param \Xtento\OrderExport\Model\Export\Condition\Custom $conditionCustom
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Xtento\OrderExport\Model\Export\Condition\Product $conditionProduct,
        \Xtento\OrderExport\Model\Export\Condition\Custom $conditionCustom,
        array $data = []
    ) {
        $this->conditionProduct = $conditionProduct;
        $this->conditionCustom = $conditionCustom;
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Xtento\OrderExport\Model\Export\Condition\Product\Found');
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If an item is %1 with %2 of these conditions true:",
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = $this->conditionProduct;
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        foreach ($productAttributes as $code => $label) {
            $pAttributes[] = [
                'value' => 'Xtento\OrderExport\Model\Export\Condition\Product|' . $code,
                'label' => $label
            ];
        }

        $itemAttributes = [];
        $customItemAttributes = $this->conditionCustom->getCustomNotMappedAttributes('_item');
        foreach ($customItemAttributes as $code => $label) {
            $itemAttributes[] = [
                'value' => 'Xtento\OrderExport\Model\Export\Condition\Item|' . $code,
                'label' => $label
            ];
        }

        $conditions = [
            ['value' => '', 'label' => __('Please choose a condition to add.')],
            [
                'value' => 'Xtento\OrderExport\Model\Export\Condition\Combine',
                'label' => __('Conditions combination')
            ],
            ['label' => __('Product Attribute'), 'value' => $pAttributes],
            ['label' => __('Item Attribute'), 'value' => $itemAttributes],
        ];
        return $conditions;
    }
}