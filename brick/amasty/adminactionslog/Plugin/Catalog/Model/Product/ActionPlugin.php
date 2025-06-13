<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\Catalog\Model\Product;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class ActionPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Logging\ActionFactory
     */
    private $actionFactory;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var array
     */
    private $products = [];

    public function __construct(
        RequestInterface $request,
        Logging\ActionFactory $actionFactory,
        MetadataInterfaceFactory $metadataFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->request = $request;
        $this->actionFactory = $actionFactory;
        $this->metadataFactory = $metadataFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function beforeUpdateAttributes(Action $subject, $productIds, $attrData, $storeId)
    {
        $this->products = $this->getProducts((array)$productIds, (int)$storeId);
        foreach ($this->products as $product) {
            // Workaround for \Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog\Product::getLogMetadata()
            $product->setStoreId($storeId);
            $product->setOrigData(Product::STORE_ID, $storeId);
            $this->executeLoggingAction($product);
        }

        return null;
    }

    public function afterUpdateAttributes(Action $subject, $result, $productIds, $attrData, $storeId)
    {
        foreach ($this->products as $product) {
            $product->addData($attrData);
            $this->executeLoggingAction($product, false);
        }

        return $result;
    }

    private function getProducts(array $productIds, int $storeId): array
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->addFilter(Product::STORE_ID, $storeId)
            ->create();

        return $this->productRepository->getList($criteria)->getItems();
    }

    private function executeLoggingAction($loggingObject, bool $isBefore = true): void
    {
        $eventName = $isBefore
            ? MetadataInterface::EVENT_SAVE_BEFORE
            : MetadataInterface::EVENT_SAVE_AFTER;
        $metadata = $this->metadataFactory->create([
            'request' => $this->request,
            'eventName' => $eventName,
            'loggingObject' => $loggingObject
        ]);
        $actionHandler = $this->actionFactory->create($metadata);
        $actionHandler->execute();
    }
}
