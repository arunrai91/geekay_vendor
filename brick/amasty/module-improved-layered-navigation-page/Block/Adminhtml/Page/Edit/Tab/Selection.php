<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form as WidgetForm;
use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Element\ElementCreator;
use Amasty\ShopbyPage\Model\Request\Page\Registry as PageRegistry;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Amasty\ShopbyPage\Model\Config\Source\Attribute as SourceAttribute;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;

/**
 * @api
 */
class Selection extends WidgetForm implements TabInterface
{
    /**
     * @var  SourceAttribute
     */
    private $sourceAttribute;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var PageRegistry
     */
    private PageRegistry $pageRegistry;

    public function __construct(
        SourceAttribute $sourceAttribute,
        CatalogConfig $catalogConfig,
        FormFactory $formFactory,
        ElementCreator $creator,
        PageRegistry $pageRegistry,
        Context $context,
        array $data = []
    ) {
        $this->sourceAttribute = $sourceAttribute;
        $this->catalogConfig = $catalogConfig;
        $this->pageRegistry = $pageRegistry;
        parent::__construct($formFactory, $creator, $context, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Filter Selections');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function prepareForm(): Selection
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getDataFormFactory()->create();

        /** @var \Amasty\ShopbyPage\Api\Data\PageInterface $model */
        $model = $this->pageRegistry->get();

        $conditions = $model->getConditions();

        $attributes = $this->sourceAttribute->toArray();
        $defaultAttributeId = count($attributes) > 0 ? array_keys($attributes)[0] : null;

        if (!$defaultAttributeId) {
            return $this;
        }

        $attributeIdx = 1;
        if (is_array($conditions)) {
            foreach ($conditions as $condition) {
                $this->addSelectionControls($attributeIdx, $condition, $form, $attributes);
                $attributeIdx++;
            }
        }

        $fieldset = $form->addFieldset(
            'add_selection_fieldset',
            ['legend' => __('Add Selection'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'new_selection_filter',
            'select',
            [
                'values'   => ['0' => __('Select attribute')] + $attributes,
                'label'    => __('Filter'),
                'title'    => __('Filter'),
                'onchange' => 'window.amastyShopbyPageSelection.add(\'add_selection_fieldset\', this.value);
                                this.value=0; return;'
            ]
        );

        $this->setForm($form);
        parent::prepareForm();

        return $this;
    }

    /**
     * @param $attributeIdx
     * @param $condition
     * @param \Magento\Framework\Data\Form $form
     * @param $attributes
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addSelectionControls(
        $attributeIdx,
        $condition,
        \Magento\Framework\Data\Form $form,
        $attributes
    ) {
        $filter = array_key_exists('filter', $condition) ? $condition['filter'] : null;
        $value = array_key_exists('value', $condition) ? $condition['value'] : null;

        $attribute = $this->catalogConfig->getAttribute(Product::ENTITY, $filter);

        $attributeValueId = 'attribute_value_' . $attributeIdx;
        $attributeDeleteId = 'attribute_delete_' . $attributeIdx;
        $fieldset = $form->addFieldset(
            $attributeIdx . '_selection_fieldset',
            ['legend' => __('Selection #%1', $attributeIdx), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            $attributeIdx,
            'select',
            [
                'name'     => 'conditions[' . $attributeIdx . '][filter]',
                'value'    => $filter,
                'values'   => $attributes,
                'class'    => 'required-entry',
                'required' => true,
                'label'    => __('Filter'),
                'title'    => __('Filter'),
                'onchange' => 'window.amastyShopbyPageSelection.load(\'' .
                    $attributeValueId . '_data\', ' . $attributeIdx . ', this.value)'
            ]
        );

        $fieldset->addField(
            $attributeValueId,
            'text',
            [
                'name'  => $attributeValueId,
                'label' => __('Value'),
                'title' => __('Value')
            ]
        );

        $form->getElement(
            $attributeValueId
        )->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\Selection\Option::class)
                ->setEavAttribute($attribute)
                ->setEavAttributeValue($value)
                ->setEavAttributeIdx($attributeIdx)
        );

        $fieldset->addField(
            $attributeDeleteId,
            'button',
            [
                'value'   => __('Remove'),
                'onclick' => 'window.amastyShopbyPageSelection.remove(\'' . $attributeIdx . '_selection_fieldset\')'
            ]
        );

        return $fieldset;
    }
}
