<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab;

use Amasty\ShopbyPage\Api\Data\PageInterface;
use Amasty\ShopbyPage\Controller\RegistryConstants;
use Amasty\ShopbyPage\Model\Config\Source\Robots;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @api
 */
class Meta extends Generic implements TabInterface
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var Robots
     */
    private $robotsConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Robots $robotsConfig,
        array $data = []
    ) {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->robotsConfig = $robotsConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Meta Tags');
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

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();

        /** @var PageInterface $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::PAGE);

        $fieldset = $form->addFieldset(
            'meta_fieldset',
            ['legend' => __('Meta Tags'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'meta_title',
            'text',
            [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title')
            ]
        );

        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description')
            ]
        );

        $fieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords')
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Canonical Url'),
                'title' => __('Canonical Url'),
                'note' => __('Use relative URL here.<br/> Read ') .
                    '<a href="https://support.google.com/webmasters/answer/139394" target="_blank">'
                    . __('this article') . '</a>'
                . __(' to learn more about canonical URLs.')
            ]
        );

        $fieldset->addField(
            'tag_robots',
            'select',
            [
                'name' => 'tag_robots',
                'label' => __('Robots Tag Control'),
                'title' => __('Robots Tag Control'),
                'values' => $this->robotsConfig->toArray()
            ]
        );

        $form->setValues(
            $this->extensibleDataObjectConverter->toFlatArray(
                $model,
                [],
                PageInterface::class
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
