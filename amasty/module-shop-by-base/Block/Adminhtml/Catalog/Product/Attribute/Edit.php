<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute;

use Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var AttributeConfig
     */
    private $attributeSettingsConfig;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AttributeConfig $attributeConfig,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->attributeSettingsConfig = $attributeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function canConfigureAttributeOptions()
    {
        return $this->attributeSettingsConfig->canBeConfigured($this->getAttributeCode());
    }

    /**
     * @return string
     */
    public function getAttributeCode(): string
    {
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->coreRegistry->registry('entity_attribute');

        return $attribute && $attribute->getAttributeCode() ? $attribute->getAttributeCode() : '';
    }
}
