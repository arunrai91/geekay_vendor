<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Filter;

use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog\Product;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Inventory\Model\SourceItem;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Adminhtml\Stock\Item as StockItem;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Framework\DB\Select;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;

class FilterProductListing implements CollectionModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    public function apply(AbstractDb $collection)
    {
        $productId = (int)$this->request->getParam('current_product_id');
        if (!$productId) {
            return;
        }

        $collection->getSelect()->where($this->getSqlCondition($productId));
    }

    private function getSqlCondition(int $productId): string
    {
        $sqlCondition = '';

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return 'null';
        }

        $elements = [
            [
                Product::CATEGORY,
                LogEntry::ELEMENT_ID,
                (string)$productId
            ], [
                SourceItem::CATEGORY,
                LogEntry::ITEM,
                str_replace("'", "\'", $product->getSku())
            ]
        ];

        if ($product->getExtensionAttributes()
            && $stockItem = $product->getExtensionAttributes()->getStockItem()
        ) {
            $elements[] = [
                StockItem::CATEGORY,
                LogEntry::ELEMENT_ID,
                (string)$stockItem->getItemId()
            ];
        }

        $lastIndex = count($elements) - 1;

        foreach ($elements as $index => $element) {
            $sqlCondition .= sprintf('(%s)', $this->prepareSql(...$element));

            if ($index !== $lastIndex) {
                $sqlCondition .= sprintf(' %s ', Select::SQL_OR);
            }
        }

        return $sqlCondition;
    }

    private function prepareSql(
        string $category,
        string $identifierFieldName,
        string $identifier
    ): string {
        return sprintf(
            'main_table.%s = \'%s\' AND main_table.%s = \'%s\'',
            LogEntry::CATEGORY,
            $category,
            $identifierFieldName,
            $identifier
        );
    }
}
