<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Email\Items;

use Amasty\Acart\Model\ConfigProvider;
use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as LinkCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

abstract class Link extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LinkCollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var ProductLinkFactory
     */
    protected $productLinkFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(
        ConfigProvider $configProvider,
        LinkCollectionFactory $linkCollectionFactory,
        ProductLinkFactory $productLinkFactory,
        CartRepositoryInterface $cartRepository,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->productLinkFactory = $productLinkFactory;
        $this->cartRepository = $cartRepository;
        parent::__construct($context, $data);
    }

    public function getItems()
    {
        $quote = $this->getQuote();

        if (!$quote || !$quote->getAllVisibleItems()) {
            return [];
        }

        $productIds = array_map(function (Item $item) {
            return $item->getProductId();
        }, $quote->getAllVisibleItems());
        $collection = $this->linkCollectionFactory->create()
            ->addProductFilter($productIds)
            ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->setLinkModel($this->getLinkModel())
            ->setPositionOrder()
            ->setGroupBy();

        if ($qty = $this->configProvider->getProductsQty()) {
            $collection->setPageSize($qty);
        }

        return $collection->getItems();
    }

    abstract protected function getLinkModel();

    public function getQuote()
    {
        try {
            return $this->cartRepository->get((int)$this->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
