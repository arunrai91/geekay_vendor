<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Element;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;

class ElementCreator
{
    /**
     * @var array
     */
    private array $modifiers;

    public function __construct(array $modifiers = [])
    {
        $this->modifiers = $modifiers;
    }

    public function create(Fieldset $fieldset, Attribute $attribute): AbstractElement
    {
        $config = $this->getElementConfig($attribute);

        if (!empty($config['rendererClass'])) {
            $fieldType = $config['inputType'] . '_' . $attribute->getAttributeCode();
            $fieldset->addType($fieldType, $config['rendererClass']);
        }

        return $fieldset
            ->addField($config['attribute_code'], $config['inputType'], $config)
            ->setEntityAttribute($attribute);
    }

    private function getElementConfig(Attribute $attribute): array
    {
        $defaultConfig = $this->createDefaultConfig($attribute);
        $config = $this->modifyConfig($defaultConfig);

        $config['label'] = __($config['label']);

        return $config;
    }

    private function createDefaultConfig(Attribute $attribute): array
    {
        return [
            'inputType' => $attribute->getFrontend()->getInputType(),
            'rendererClass' => $attribute->getFrontend()->getInputRendererClass(),
            'attribute_code' => $attribute->getAttributeCode(),
            'name' => $attribute->getAttributeCode(),
            'label' => $attribute->getFrontend()->getLabel(),
            'class' => $attribute->getFrontend()->getClass(),
            'required' => $attribute->getIsRequired(),
            'note' => $attribute->getNote(),
        ];
    }

    /**
     *  Modify config
     *
     * @param array $config
     * @return array
     */
    private function modifyConfig(array $config): array
    {
        if ($this->isModified($config['attribute_code'])) {
            return $this->applyModifier($config);
        }
        return $config;
    }

    private function isModified(string $attributeCode): bool
    {
        return isset($this->modifiers[$attributeCode]);
    }

    /**
     * Apply modifier to config
     *
     * @param array $config
     * @return array
     */
    private function applyModifier(array $config): array
    {
        $modifiedConfig = $this->modifiers[$config['attribute_code']];
        foreach (array_keys($config) as $key) {
            if (isset($modifiedConfig[$key])) {
                $config[$key] = $modifiedConfig[$key];
            }
        }
        return $config;
    }
}
