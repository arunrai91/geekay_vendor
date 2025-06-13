<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Category;

use Magento\Framework\Event\ObserverInterface;

class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Rolepermissions\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Amasty\Rolepermissions\Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var \Amasty\Rolepermissions\Model\RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Rolepermissions\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Amasty\Rolepermissions\Model\ResourceModel\Rule $ruleResource,
        \Amasty\Rolepermissions\Model\RuleFactory $ruleFactory
    ) {
        $this->request = $request;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $categoryId = (int) $this->request->getParam('id');
        if (!$categoryId) { // New category
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $observer->getCategory();

            $this->updateSubcategoryPermissions($category);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    private function updateSubcategoryPermissions($category)
    {
        /** @var \Amasty\Rolepermissions\Model\ResourceModel\Rule\Collection $ruleCollection */
        $ruleCollection = $this->ruleCollectionFactory->create();
        $ruleCollection->addCategoriesFilter($category->getParentId());

        foreach ($ruleCollection->getAllIds() as $ruleId) {
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $ruleId);
            $categories = $rule->getAllAllowedCategories();
            // joined value is in string
            $categories[] = $category->getId();

            $rule->setCategories(array_unique($categories))
                ->save();
        }
    }
}
