<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry as CoreRegistry;
use Amasty\ShopbyPage\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;
use Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\SelectionFactory as TabSelectionFactory;
use Amasty\ShopbyPage\Model\Config\Source\Attribute as SourceAttribute;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Framework\Data\FormFactory;

class AddSelection extends Action
{
    /**
     * @var CatalogConfig
     */
    private CatalogConfig $catalogConfig;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var FormFactory
     */
    private FormFactory $formFactory;

    /**
     * @var TabSelectionFactory
     */
    private TabSelectionFactory $tabSelectionFactory;

    /**
     * @var SourceAttribute
     */
    private SourceAttribute $sourceAttribute;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CatalogConfig $catalogConfig,
        FormFactory $formFactory,
        TabSelectionFactory $tabSelectionFactory,
        SourceAttribute $sourceAttribute,
        LayoutInterface $layout
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->catalogConfig = $catalogConfig;
        $this->formFactory = $formFactory;
        $this->tabSelectionFactory = $tabSelectionFactory;
        $this->sourceAttribute = $sourceAttribute;
        $this->request = $context->getRequest();
        $this->layout = $layout;

        parent::__construct($context);
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyPage::page');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $counter = $this->request->getParam('counter');
            $attribute = $this->loadAttribute();

            /** @var \Magento\Framework\Data\FormFactory $form */
            $form = $this->formFactory->create();

            /** @var \Amasty\ShopbyBase\Block\Adminhtml\Widget\Form $widgetForm */
            $widgetForm = $this->layout->createBlock(\Amasty\ShopbyBase\Block\Adminhtml\Widget\Form::class)
                ->setForm($this->formFactory->create());

            $attributes = $this->sourceAttribute->toArray();

            $tab = $this->tabSelectionFactory->create();

            $fieldset = $tab->addSelectionControls(
                $counter + 1,
                ['filter' => $attribute->getId(), 'value' => ''],
                $form,
                $attributes
            );

            $widgetForm->getForm()->addElement($fieldset);

            $response = ['error' => false, 'html' => $widgetForm->toHtml()];
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage() . __('We can\'t fetch attribute options.')];
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws LocalizedException
     */
    private function loadAttribute()
    {
        $attributeId = $this->request->getParam('id');
        $attribute = $this->catalogConfig->getAttribute(Product::ENTITY, $attributeId);

        if (!$attribute->getId()) {
            throw new LocalizedException(__('Attribute does n\'t exists'));
        }

        return $attribute;
    }
}
