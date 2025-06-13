<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config\Source\ParentPriority;
use Amasty\Feed\Model\FeedTemplate\ResourceModel\FeedTemplate as TemplateResource;
use Amasty\Feed\Model\OptionSource\Feed\StoreOption;
use Amasty\Feed\Ui\Component\Form\Wizard\TemplateOptions;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Feed\Model\Config\Source\Compress
     */
    private $compressSource;

    /**
     * @var StoreOption
     */
    private $storeOption;

    /**
     * @var ParentPriority
     */
    private $parentPriority;

    /**
     * @var TemplateResource
     */
    private $templateResource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\Config\Source\Compress $compressSource,
        StoreOption $storeOption,
        ParentPriority $parentPriority,
        TemplateResource $templateResource,
        array $data = []
    ) {
        $this->compressSource = $compressSource;
        $this->storeOption = $storeOption;
        $this->parentPriority = $parentPriority;
        $this->templateResource = $templateResource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField(FeedInterface::ENTITY_ID, 'hidden', ['name' => 'feed_entity_id']);
        } else {
            $model->setData(FeedInterface::IS_ACTIVE, 1);

            $model->setData(FeedInterface::CSV_COLUMN_NAME, 1);

            $model->setData(FeedInterface::FORMAT_PRICE_CURRENCY_SHOW, 1);
            $model->setData(FeedInterface::FORMAT_PRICE_DECIMALS, 'two');
            $model->setData(FeedInterface::FORMAT_PRICE_DECIMAL_POINT, 'dot');
            $model->setData(FeedInterface::FORMAT_PRICE_THOUSANDS_SEPARATOR, 'comma');

            $model->setData(FeedInterface::FORMAT_DATE, 'Y-m-d');
        }

        $fieldset->addField(
            'template_type',
            'select',
            $this->getTemplateTypeOptions($model)
        );

        $fieldset->addField(
            FeedInterface::NAME,
            'text',
            [
                'name' => FeedInterface::NAME,
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            FeedInterface::FILENAME,
            'text',
            [
                'name' => FeedInterface::FILENAME,
                'label' => __('File Name'),
                'title' => __('File Name'),
                'required' => true
            ]
        );

        $typeOptions = [
            'label' => __('Type'),
            'title' => __('Type'),
            'name' => FeedInterface::FEED_TYPE,
            'required' => true,

            'options' => [
                'csv' => __('CSV'),
                'xml' => __('XML'),
                'txt' => 'TXT'
            ]
        ];

        if ($model->getId()) {
            $typeOptions['readonly'] = true;
            $feedType = $model->getFeedType();
            $feedTypeText = $typeOptions['options'][$feedType];
            if ($feedType && $feedTypeText) {
                $typeOptions['options'] = [$feedType => $feedTypeText];
            }
        }

        $fieldset->addField(
            FeedInterface::FEED_TYPE,
            'select',
            $typeOptions
        );

        $fieldset->addField(
            FeedInterface::STORE_ID,
            'select',
            [
                'name' => FeedInterface::STORE_ID,
                'label' => __('Store'),
                'title' => __('Store'),
                'required' => true,
                'options' => $this->storeOption->toOptionArray()
            ]
        );

        $fieldset->addField(
            FeedInterface::IS_ACTIVE,
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => FeedInterface::IS_ACTIVE,
                'required' => true,
                'options' => [
                    '1' => __('Active'),
                    '0' => __('Inactive')
                ]
            ]
        );

        $fieldset->addField(
            FeedInterface::COMPRESS,
            'select',
            [
                'label' => __('Compress'),
                'name' => FeedInterface::COMPRESS,
                'options' => $this->compressSource->toArray()
            ]
        );

        $fieldset->addField(
            FeedInterface::PARENT_PRIORITY,
            'select',
            [
                'label' => __('Parent Data Priority'),
                'title' => __('Parent Data Priority'),
                'name' => FeedInterface::PARENT_PRIORITY,
                'options' => $this->parentPriority->toOptionArray(),
                'note' => __(' If the feed content setting to display the parent option is set to'
                        . ' "Yes" or "Yes if empty", then this setting will determine the priority of the uploaded'
                        . ' parent product, in case the simple product is part of both a Configurable product and a'
                        . ' Bundle product.'),
            ]
        );

        $fieldset->addField(
            FeedInterface::EXCLUDE_DISABLED,
            'select',
            [
                'label' => __('Exclude Disabled Products'),
                'title' => __('Exclude Disabled Products'),
                'name' => FeedInterface::EXCLUDE_DISABLED,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            FeedInterface::EXCLUDE_SUBDISABLED,
            'select',
            [
                'label' => __('Exclude Child Products if Parent Product Is Disabled'),
                'title' => __('Exclude Child Products if Parent Product Is Disabled'),
                'name' => FeedInterface::EXCLUDE_SUBDISABLED,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            FeedInterface::EXCLUDE_OUT_OF_STOCK,
            'select',
            [
                'label' => __('Exclude Out of Stock Products'),
                'title' => __('Exclude Out of Stock Products'),
                'name' => FeedInterface::EXCLUDE_OUT_OF_STOCK,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            FeedInterface::EXCLUDE_NOT_VISIBLE,
            'select',
            [
                'label' => __('Exclude Not Visible Products'),
                'title' => __('Exclude Not Visible Products'),
                'name' => FeedInterface::EXCLUDE_NOT_VISIBLE,
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $dependencies = $this->getLayout()->createBlock(Dependence::class)
            ->addFieldMap($htmlIdPrefix . FeedInterface::EXCLUDE_DISABLED, FeedInterface::EXCLUDE_DISABLED)
            ->addFieldMap($htmlIdPrefix . FeedInterface::EXCLUDE_SUBDISABLED, FeedInterface::EXCLUDE_SUBDISABLED)
            ->addFieldDependence(FeedInterface::EXCLUDE_SUBDISABLED, FeedInterface::EXCLUDE_DISABLED, 1);

        $form->setValues($model->getData());
        $this->setForm($form);
        $this->setChild('form_after', $dependencies);

        return parent::_prepareForm();
    }

    private function getTemplateTypeOptions(FeedInterface $model): array
    {
        if ($templateCode = $model->getTemplateCode()) {
            $templateName = $this->templateResource->getNameByCode($templateCode);
            $name = $templateName ?: 'Custom Feed';
            $code = $templateName ?: TemplateOptions::CUSTOM_FEED_CODE;
        } else {
            $name = 'Custom Feed';
            $code = TemplateOptions::CUSTOM_FEED_CODE;
        }

        return [
            'label' => __('Template'),
            'title' => __('Template'),
            'name' => 'template_type',
            'required' => true,
            'readonly' => true,
            'options' => [
                $code => $name
            ]
        ];
    }
}
