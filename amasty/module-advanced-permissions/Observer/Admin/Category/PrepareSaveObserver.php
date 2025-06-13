<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Category;

use Magento\Framework\Event\ObserverInterface;

class PrepareSaveObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $this->helper->currentRule();

        if ($rule->getProducts() || $rule->getScopeStoreviews()) {
            if (false === $rule->getAllowedProductIds()) {
                return;
            }

            /** @var \Magento\Catalog\Model\Category $category */
            $category = $observer->getCategory();
            $postedProducts = $category->getPostedProducts();

            if ($postedProducts !== null) {
                $productsCollection = [];

                foreach ($category->getProductCollection() as $id => $product) {
                    $productsCollection[$id] = $product->getCatIndexPosition();
                }

                $allowedProductIds = $this->helper->combine(
                    array_keys($productsCollection),
                    array_keys($postedProducts),
                    $rule->getAllowedProductIds()
                );
                $priorities = $postedProducts + $productsCollection;

                foreach ($priorities as $id => $position) {
                    if (!in_array($id, $allowedProductIds)) {
                        unset($priorities[$id]);
                    }
                }

                $category->setPostedProducts($priorities);
            }
        }
    }
}
