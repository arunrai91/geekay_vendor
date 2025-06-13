<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Widget;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Element\ElementCreator;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory as DataFormFactory;

class Form extends Widget
{
    /**
     * @var DataFormFactory
     */
    private DataFormFactory $dataFormFactory;

    /**
     * @var ElementCreator
     */
    private ElementCreator $creator;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form.phtml';

    /**
     * @var DataForm|null
     */
    private ?DataForm $form = null;

    public function __construct(
        DataFormFactory $dataFormFactory,
        ElementCreator $creator,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataFormFactory = $dataFormFactory;
        $this->creator = $creator;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setDestElementId('edit_form');
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        DataForm::setElementRenderer(
            $this->getLayout()->createBlock(
                \Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Renderer\Element::class,
                $this->getNameInLayout() . '_element'
            )
        );
        DataForm::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class,
                $this->getNameInLayout() . '_fieldset'
            )
        );
        DataForm::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                \Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Renderer\Fieldset\Element::class,
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return parent::_prepareLayout();
    }

    public function getForm(): ?DataForm
    {
        return $this->form;
    }

    public function getFormHtml(): string
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }
        return '';
    }

    public function setForm(DataForm $form): Form
    {
        $this->form = $form;
        $this->form->setParent($this);
        $this->form->setBaseUrl($this->_urlBuilder->getBaseUrl());

        $customAttributes = $this->getData('custom_attributes');
        if (is_array($customAttributes)) {
            foreach ($customAttributes as $key => $value) {
                $this->form->addCustomAttribute($key, $value);
            }
        }
        return $this;
    }

    public function prepareForm(): Form
    {
        return $this;
    }

    /**
     * This method is called before rendering HTML
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * Initialize form fields values
     *
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return $this
     */
    public function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param array $exclude attributes that should be skipped
     * @return void
     */
    public function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if (!$this->_isAttributeVisible($attribute)) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType())
                && !in_array($attribute->getAttributeCode(), $exclude)
                && ('media_image' !== $inputType || $attribute->getAttributeCode() == 'image')
            ) {
                $element = $this->creator->create($fieldset, $attribute);
                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                $this->_applyTypeSpecificConfig($inputType, $element, $attribute);
            }
        }
    }

    /**
     * Check whether attribute is visible
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return bool
     */
    public function _isAttributeVisible(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        return !(!$attribute || $attribute->hasIsVisible() && !$attribute->getIsVisible());
    }

    /**
     * Apply configuration specific for different element type
     *
     * @param string $inputType
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return void
     */
    public function _applyTypeSpecificConfig($inputType, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        switch ($inputType) {
            case 'select':
                $element->setValues($attribute->getSource()->getAllOptions(true, true));
                break;
            case 'multiselect':
                $element->setValues($attribute->getSource()->getAllOptions(false, true));
                $element->setCanBeEmpty(true);
                break;
            case 'date':
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                break;
            case 'datetime':
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                $element->setTimeFormat($this->_localeDate->getTimeFormat());
                break;
            case 'multiline':
                $element->setLineCount($attribute->getMultilineCount());
                break;
            default:
                break;
        }
    }

    /**
     * Add new element type
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $baseElement
     * @return void
     */
    public function _addElementTypes(\Magento\Framework\Data\Form\AbstractForm $baseElement)
    {
        $types = array_merge(
            [
                'datetime' => 'date'
            ],
            $this->_getAdditionalElementTypes()
        );

        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

    /**
     * Retrieve predefined additional element types
     *
     * @return array
     */
    public function _getAdditionalElementTypes()
    {
        return [];
    }

    /**
     * Render additional element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _getAdditionalElementHtml($element)
    {
        return '';
    }

    public function getDataFormFactory(): DataFormFactory
    {
        return $this->dataFormFactory;
    }
}
