<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Magento\Store\Model\Store;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DataObject;

class Faq extends AbstractSearch
{
    public const FAQ_BLOCK_PAGE = 'faq';

    /**
     * @var AbstractCollection
     */
    private $categoriesSearchCollection;

    /**
     * @var AbstractCollection
     */
    private $questionsSearchCollection;

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::FAQ_BLOCK_PAGE;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateCollection()
    {
        /** @var \Magento\Framework\Data\Collection $collection */
        $collection = parent::generateCollection();

        foreach ($this->getCategoriesCollection() as $item) {
            if ($collection->count() >= $this->getLimit()) {
                break;
            }
            $item->setUrl($item->getRelativeUrl());
            $this->addToFaqCollection($item, $collection);
        }

        if ($collection->count() < $this->getLimit()) {
            foreach ($this->getQuestionsCollection() as $item) {
                if ($collection->count() >= $this->getLimit()) {
                    break;
                }
                $item->setUrl($item->getRelativeUrl());
                $this->addToFaqCollection($item, $collection);
            }
        }

        return $collection;
    }

    public function getItemData(DataObject $item): array
    {
        $data = parent::getItemData($item);
        $data['answer'] = $item->getAnswer();
        $data['short_answer'] = $item->getShortAnswer();

        return $data;
    }

    /**
     * @param $item
     * @param \Magento\Framework\Data\Collection $collection
     */
    private function addToFaqCollection($item, $collection)
    {
        if ($collection->count() >= $this->getLimit()) {
            return;
        }
        $dataObject = $this->getData('dataObjectFactory')->create();
        $dataObject->setData($item->getData());
        $collection->addItem($dataObject);
    }

    /**
     * @return AbstractCollection
     */
    private function getCategoriesCollection()
    {
        if ($this->categoriesSearchCollection === null) {
            $this->categoriesSearchCollection = $this->getData('categoriesCollectionFactory')->create()
                ->addSearchFilter($this->getQuery()->getQueryText())
                ->addFieldToFilter('status', 1)
                ->addStoreFilter([Store::DEFAULT_STORE_ID, $this->_storeManager->getStore()->getId()])
                ->setPageSize($this->getLimit());
        }

        return $this->categoriesSearchCollection;
    }

    /**
     * @return AbstractCollection
     */
    private function getQuestionsCollection()
    {
        if ($this->questionsSearchCollection === null) {
            $this->questionsSearchCollection = $this->getData('questionsCollectionFactory')->create()
                ->addSearchFilter($this->getQuery()->getQueryText())
                ->addFieldToFilter('visibility', 1)
                ->addFieldToFilter('status', 1)
                ->addStoreFilter([Store::DEFAULT_STORE_ID, $this->_storeManager->getStore()->getId()])
                ->setPageSize($this->getLimit());
        }

        return $this->questionsSearchCollection;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        return $item->getUrl();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getTitle());
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $item)
    {
        return '';
    }

    /**
     * @return array[]
     */
    public function getIndexFulltextValues()
    {
        $faqValues = $this->questionsSearchCollection->getIndexFulltextValues();
        $categoryValues = $this->categoriesSearchCollection->getIndexFulltextValues();

        return array_merge($categoryValues, $faqValues);
    }
}
