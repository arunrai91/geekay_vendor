<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model\ResourceModel;

use Amasty\Base\Model\Serializer as BaseSerializer;
use Amasty\ShopbyPage\Api\Data\PageInterface;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\Data\PageSearchResultsInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Amasty\ShopbyPage\Model\Page as PageModel;
use Amasty\ShopbyPage\Model\ResourceModel\Page as PageResource;
use Amasty\ShopbyPage\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class PageRepository implements PageRepositoryInterface
{
    /**
     * @var PageResource
     */
    private PageResource $pageResourceModel;

    /**
     * @var PageInterfaceFactory
     */
    private PageInterfaceFactory $pageFactory;

    /**
     * @var PageSearchResultsInterfaceFactory
     */
    private PageSearchResultsInterfaceFactory $pageSearchResultsFactory;

    /**
     * @var BaseSerializer
     */
    private BaseSerializer $serializer;

    /**
     * @var PageCollectionFactory
     */
    private PageCollectionFactory $pageCollectionFactory;

    public function __construct(
        PageResource $pageResourceModel,
        PageSearchResultsInterfaceFactory $pageSearchResultsFactory,
        PageInterfaceFactory $pageFactory,
        BaseSerializer $serializer,
        PageCollectionFactory $pageCollectionFactory
    ) {
        $this->pageResourceModel = $pageResourceModel;
        $this->pageSearchResultsFactory = $pageSearchResultsFactory;
        $this->pageFactory = $pageFactory;
        $this->serializer = $serializer;
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * @param PageInterface $page
     *
     * @return PageInterface
     */
    public function save(PageInterface $page)
    {
        $outputData = $page->getData();

        $this->normalizeOutputData($outputData);

        $page->setData($outputData);

        $this->pageResourceModel
            ->save($page)
            ->saveStores($page);

        return $this->get($page->getId());
    }

    /**
     * @param $data
     * @param $key
     * @param string $delimiter
     */
    private function implodeMultipleData(&$data, $key, $delimiter = ',')
    {
        if (array_key_exists($key, $data) && is_array($data[$key])) {
            $data[$key] = implode($delimiter, $data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     * @param $key
     */
    private function serializeMultipleData(&$data, $key)
    {
        if (array_key_exists($key, $data)) {
            $data[$key] = $this->serializer->serialize($data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     */
    private function normalizeOutputData(&$data)
    {
        if (array_key_exists('top_block_id', $data) && $data['top_block_id'] === '') {
            $data['top_block_id'] = null;
        }

        if (array_key_exists('bottom_block_id', $data) && $data['bottom_block_id'] === '') {
            $data['bottom_block_id'] = null;
        }

        $this->implodeMultipleData($data, 'categories');
        $this->serializeMultipleData($data, 'conditions');
    }

    private function normalizeInputData(array &$data): void
    {
        if (array_key_exists('categories', $data)) {
            $this->processCategoryField($data['categories']);
        }

        if (array_key_exists('store_id', $data)) {
            $data['stores'] = $data['store_id'];
        }

        if (array_key_exists('conditions', $data)) {
            $this->processConditionsField($data['conditions']);
        }
    }

    /**
     * @param string $categories
     */
    private function processCategoryField(&$categories)
    {
        if ($categories) {
            $categories = explode(',', $categories);
        } else {
            $categories = [\Amasty\Base\Model\Source\Category::EMPTY_OPTION_ID];
        }
    }

    /**
     * @param string $conditions
     */
    private function processConditionsField(&$conditions)
    {
        if ($conditions !== ''
            && ($conditionsArr = $this->serializer->unserialize($conditions))
            && is_array($conditionsArr)
        ) {
            array_walk(
                $conditionsArr,
                function (&$condition) {
                    if (is_string($condition)) {
                        try {
                            $condition = $this->serializer->unserialize($condition);
                        } catch (\Exception $e) {
                            $condition = [];
                        }
                    }
                }
            );
            $conditions = $conditionsArr;
        } else {
            $conditions = [];
        }
    }

    /**
     * @param int $id
     *
     * @return PageInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var PageModel $page */
        $page = $this->pageFactory->create();
        $this->pageResourceModel->load($page, $id);

        if (!$page->getId()) {
            throw new NoSuchEntityException(__('Page with id "%1" does not exist.', $id));
        }

        return $this->getPageData($page);
    }

    private function getPageData(PageModel $page): PageModel
    {
        $inputData = $page->getData();

        $this->normalizeInputData($inputData);

        $page->setData($inputData);

        return $page;
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     *
     * @return \Amasty\ShopbyPage\Api\Data\PageSearchResultsInterface
     */
    public function getList($categoryId, $storeId)
    {
        $searchResults = $this->pageSearchResultsFactory->create();

        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter(
            'categories',
            [
                ['finset' => $categoryId],
                ['eq' => 0],
                ['null' => true]
            ]
        );
        $collection->addStoreFilter($storeId);

        $pagesData = [];

        /** @var PageModel $page */
        foreach ($collection as $page) {
            $pagesData[] = $this->getPageData($page);
        }

        usort(
            $pagesData,
            function (PageInterface $a, PageInterface $b) {
                return count($b->getConditions()) - count($a->getConditions());
            }
        );

        $searchResults->setTotalCount($collection->getSize());

        return $searchResults->setItems($pagesData);
    }

    /**
     * @param PageInterface $pageData
     *
     * @return bool true on success
     */
    public function delete(PageInterface $pageData)
    {
        return $this->deleteById($pageData->getPageId());
    }

    /**
     * @param int $id
     *
     * @return bool true on success
     */
    public function deleteById($id)
    {
        /** @var PageModel $page */
        $page = $this->pageFactory->create();
        $this->pageResourceModel->load($page, $id);
        $this->pageResourceModel->delete($page);

        return true;
    }
}
