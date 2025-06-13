<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab;

use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form as WidgetForm;
use Amasty\ShopbyBase\Block\Adminhtml\Widget\Form\Element\ElementCreator;
use Amasty\ShopbyPage\Model\Page;
use Amasty\ShopbyPage\Model\Request\Page\Registry as PageRegistry;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Amasty\ShopbyPage\Model\Config\Source\Category as SourceCategory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * @api
 */
class Category extends WidgetForm implements TabInterface
{
    /**
     * @var SystemStore
     */
    private SystemStore $systemStore;

    /**
     * @var SourceCategory
     */
    private SourceCategory $sourceCategory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var PageRegistry
     */
    private PageRegistry $pageRegistry;

    public function __construct(
        SystemStore $systemStore,
        SourceCategory $sourceCategory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        PageRegistry $pageRegistry,
        Context $context,
        FormFactory $formFactory,
        ElementCreator $creator,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->sourceCategory = $sourceCategory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
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
        return __('Categories & Store Views');
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

    public function prepareForm(): Category
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getDataFormFactory()->create();
        $form->setHtmlIdPrefix('amasty_shopbypage_');

        /** @var Page $model */
        $model = $this->pageRegistry->get();
        $fieldset = $form->addFieldset(
            'category_fieldset',
            ['legend' => __('Categories'), 'class' => 'fieldset-wide']
        );

        $this->addStoreField($fieldset, $model);

        $fieldset->addField('categories', 'multiselect', [
            'label' => __('Categories'),
            'title' => __('Categories'),
            'name' => 'categories',
            'style' => 'height: 500px; width: 300px;',
            'values' => $this->sourceCategory->toOptionArray()
        ]);

        $form->setValues(
            $this->extensibleDataObjectConverter->toNestedArray(
                $model,
                [],
                \Amasty\ShopbyPage\Api\Data\PageInterface::class
            )
        );

        $this->setForm($form);

        parent::prepareForm();

        return $this;
    }

    /**
     * @param Fieldset $fieldset
     * @param Page $model
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addStoreField(Fieldset $fieldset, Page $model)
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store Views'),
                    'title' => __('Store Views'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                ]
            );

            /** @var \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element $renderer */
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $storeId = $this->_storeManager->getStore(true)->getId();
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $storeId]
            );
            $model->setStores([$storeId]);
        }
    }
}
