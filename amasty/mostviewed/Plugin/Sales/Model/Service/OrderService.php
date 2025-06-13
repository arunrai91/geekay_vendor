<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Sales\Model\Service;

use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Api\Data\ClickInterface;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\Collector\ClickCollector;
use Amasty\Mostviewed\Model\Product\Analytic\AppendProductSales as AppendProductAnalyticSales;
use Amasty\Mostviewed\Model\ResourceModel\Product\Analytic\Table;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class OrderService
{
    public const ORDERS_MADE = 'orders_made';

    public const REVENUE = 'revenue';

    /**
     * @var ClickRepositoryInterface
     */
    private $clickRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var array
     */
    private $analytics = [
        self::ORDERS_MADE => [],
        self::REVENUE     => []
    ];

    /**
     * @var ?array
     */
    private $salesProducts;

    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var array
     */
    private $visitorData;

    /**
     * @var ClickCollector
     */
    private $collector;

    /**
     * @var GroupRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var AppendProductAnalyticSales
     */
    private $appendProductAnalyticSales;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ClickRepositoryInterface $clickRepository,
        AnalyticRepositoryInterface $analyticRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Visitor $visitor,
        SessionManagerInterface $session,
        ClickCollector $collector,
        GroupRepositoryInterface $ruleRepository,
        AppendProductAnalyticSales $appendProductAnalyticSales,
        ?DateTime $dateTime = null
    ) {
        $this->clickRepository = $clickRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visitor = $visitor;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->analyticRepository = $analyticRepository;
        $this->visitorData = $session->getVisitorData();
        $this->collector = $collector;
        $this->ruleRepository = $ruleRepository;
        $this->appendProductAnalyticSales = $appendProductAnalyticSales;
        // OM for backward compatibility
        $this->dateTime = $dateTime ?? ObjectManager::getInstance()->get(DateTime::class);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order): OrderInterface
    {
        if (isset($this->visitorData['visitor_id'])) {
            $visitorId = $this->visitorData['visitor_id'];
            foreach ($order->getItems() as $orderItem) {
                $productIds = [$orderItem->getProductId()];
                $productOptions = $orderItem->getData('product_options');
                if ($productOptions && isset($productOptions['super_product_config']['product_id'])) {
                    $productIds[] = $productOptions['super_product_config']['product_id'];
                }

                if ($clickEvent = $this->getClickEvent($productIds, $visitorId)) {
                    $analytic = $this->loadAnalytic($clickEvent->getBlockId(), self::ORDERS_MADE);
                    $analytic->setCounter($analytic->getCounter() + 1);
                    $analytic = $this->loadAnalytic($clickEvent->getBlockId(), self::REVENUE);
                    $analytic->setCounter($analytic->getCounter() + $orderItem->getRowTotalInclTax());
                    $this->addProductToSalesAnalytic($orderItem, $clickEvent->getBlockId(), $order);
                }
            }
            if ($this->clickRepository->getCountLoaded() > 0) {
                $this->collector->execute();
                $this->saveAnalytics();
                $this->saveProductSales();
            }
        }

        return $order;
    }

    /**
     * @param array $productIds
     * @param $visitorId
     *
     * @return ClickInterface|null
     */
    private function getClickEvent($productIds, $visitorId)
    {
        $clickEvent = null;
        $this->searchCriteriaBuilder
            ->addFilter(ClickInterface::PRODUCT_ID, $productIds, 'in')
            ->addFilter(ClickInterface::VISITOR_ID, $visitorId);
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField(ClickInterface::ID)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        $clickEvents = $this->clickRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
        if (isset($clickEvents[0])) {
            $clickEvent = $clickEvents[0];
        }

        return $clickEvent;
    }

    /**
     * @param int $blockId
     * @param string $type
     *
     * @return \Amasty\Mostviewed\Model\Analytics\Analytic
     */
    private function loadAnalytic($blockId, $type, $date = null)
    {
        $date = $date ?? $this->dateTime->date('Y-m-d');

        if (!isset($this->analytics[$type][$blockId][$date])) {
            $view = $this->analyticRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter(AnalyticInterface::BLOCK_ID, $blockId)
                    ->addFilter(AnalyticInterface::TYPE, $type)
                    ->addFilter(AnalyticInterface::DATE, $date)
                    ->setPageSize(1)
                    ->create()
            )->getItems();
            if (isset($view[0])) {
                $view = $view[0];
            } else {
                $view = $this->analyticRepository->getNew();
            }
            $this->analytics[$type][$blockId][$date] = $view;
        }

        return $this->analytics[$type][$blockId][$date];
    }

    private function saveAnalytics(): void
    {
        foreach ($this->analytics as $type => $analytics) {
            foreach ($analytics as $blockId => $analyticsByDate) {
                /** @var \Amasty\Mostviewed\Model\Analytics\Analytic $analytic */
                foreach ($analyticsByDate as $date => $analytic) {
                    $analytic->setBlockId($blockId);
                    $analytic->setType($type);
                    $analytic->setDate($date);
                    $this->analyticRepository->save($analytic);
                }
            }
        }
    }

    private function addProductToSalesAnalytic($orderItem, $blockId, $order): void
    {
        $this->salesProducts[] = [
            Table::ORDER_ID_COLUMN => $order->getEntityId(),
            Table::ORDER_INCREMENT_COLUMN => $order->getIncrementId(),
            Table::RULE_ID_COLUMN => $blockId,
            Table::RULE_NAME_COLUMN => $this->ruleRepository->getById($blockId)->getName(),
            Table::PRODUCT_NAME_COLUMN => $orderItem->getName(),
            Table::PRODUCT_PRICE_COLUMN => $orderItem->getBasePrice(),
            Table::PRODUCT_QTY_COLUMN => $orderItem->getQtyOrdered(),
            Table::BASE_GRAND_TOTAL_COLUMN => $order->getBaseGrandTotal(),
            Table::GRAND_TOTAL_COLUMN => $order->getGrandTotal(),
            Table::BASE_CURRENCY_CODE_COLUMN => $order->getBaseCurrencyCode(),
            Table::ORDER_CURRENCY_CODE_COLUMN => $order->getOrderCurrencyCode(),
            Table::CREATED_AT_COLUMN => $order->getCreatedAt()
        ];
    }

    private function saveProductSales(): void
    {
        $this->appendProductAnalyticSales->execute($this->salesProducts);
        $this->salesProducts = [];
    }
}
