<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Amasty\ShopbyPage\Model\Request\Page\SelectionAttributeRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Selection extends Action
{
    /**
     * @var CatalogConfig
     */
    private CatalogConfig $catalogConfig;

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var SelectionAttributeRegistry
     */
    private SelectionAttributeRegistry $selectionAttributeRegistry;

    public function __construct(
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        CatalogConfig $catalogConfig,
        SelectionAttributeRegistry $selectionAttributeRegistry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->catalogConfig = $catalogConfig;
        $this->selectionAttributeRegistry = $selectionAttributeRegistry;
        $this->request = $context->getRequest();
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
            $attribute = $this->loadAttribute();
            $attributeIdx = $this->request->getParam('idx');
            if (isset($attributeIdx)) {
                $attributeIdx = (int) $attributeIdx;
            }

            $this->selectionAttributeRegistry->setAttribute($attribute);
            $this->selectionAttributeRegistry->setAttributeIdx($attributeIdx);

            return $this->resultPageFactory->create();
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
        $attributeId = (int) $this->request->getParam('id');
        $attribute = $this->catalogConfig->getAttribute(Product::ENTITY, $attributeId);

        if (!$attribute->getId()) {
            throw new LocalizedException(__('Attribute does n\'t exists'));
        }

        return $attribute;
    }
}
