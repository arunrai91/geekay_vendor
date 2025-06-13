<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractHandler
{
    /**
     * @var array
     */
    private $requiredKeys = [
        'entity_id',
        'store_id',
        'row_id',
        'created_in',
        'updated_in',
        'parent_id'
    ];

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct(
            $objectManager,
            $dataStorage,
            $storeManager
        );
        $this->categoryRepository = $categoryRepository;
    }

    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        try {
            $storeId = $logEntry->getStoreId() ?? Store::DEFAULT_STORE_ID;
            $this->setCurrentStore($storeId);
            $categoryId = $logEntry->getData(LogEntry::ELEMENT_ID);
            $category = $this->categoryRepository->get($categoryId, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Unable to restore changes. Error: %1', $e->getMessage()));
        }

        if ($storeId !== Store::DEFAULT_STORE_ID) {
            $this->processUseDefault($category);
        }

        foreach ($logDetails as $detail) {
            $oldValue = $detail->getOldValue();
            $elementKey = $detail->getName();
            $category->setData($elementKey, $oldValue);
        }

        if (!$category->hasData('store_id')) {
            $storeId = $logEntry->getStoreId() ?? Store::DEFAULT_STORE_ID;
            $category->setData('store_id', $storeId);
        }

        $this->setRestoreActionFlag($category);
        $this->categoryRepository->save($category);
    }

    // Process category data for correct checkbox behaviour.
    private function processUseDefault(CategoryInterface $category): void
    {
        try {
            $defCategory = $this->categoryRepository->get($category->getId(), Store::DEFAULT_STORE_ID);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Unable to restore changes. Error: %1', $e->getMessage()));
        }

        $useDefaultMap = [];
        foreach ($defCategory->getData() as $key => $value) {
            if ($category->getData($key) === $value) {
                if (!in_array($key, $this->requiredKeys)) {
                    $category->setData($key);
                }
                $useDefaultMap[$key] = 1;
            } else {
                $useDefaultMap[$key] = 0;
            }
        }

        $category->setData('use_default', $useDefaultMap);
    }
}
