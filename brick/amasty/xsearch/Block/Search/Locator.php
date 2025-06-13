<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Amasty\StoreLocatorAdvancedSearch\Model\ResourceModel\Location\Collection as LocationCollection;

class Locator extends AbstractSearch
{
    public const LOCATOR_BLOCK_PAGE = 'locator';

    /**
     * @var LocationCollection
     */
    private $locationSearchCollection;

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::LOCATOR_BLOCK_PAGE;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $collection = parent::generateCollection();

        foreach ($this->getLocationCollection() as $item) {
            $item->setUrl($item->getUrlKey());
            $this->addToLocationCollection($item, $collection);
        }

        return $collection;
    }

    /**
     * @param $item
     * @param $collection
     */
    private function addToLocationCollection($item, &$collection)
    {
        $dataObject = $this->getData('dataObjectFactory')->create();
        $dataObject->setData($item->getData());
        $collection->addItem($dataObject);
    }

    private function getLocationCollection()
    {
        if ($this->locationSearchCollection === null) {
            $this->locationSearchCollection = $this->getData('locationsCollectionFactory')->create();
            $this->locationSearchCollection->applyDefaultFilters();
            $this->locationSearchCollection->addSearchFilter($this->getQuery()->getQueryText());
            $this->locationSearchCollection->setPageSize($this->getLimit());
        }

        return $this->locationSearchCollection;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        $urlPrefix = $this->getData('locatorConfigProvider')->getUrl();

        return $urlPrefix . '/' . $item->getUrlKey();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getName());
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $item)
    {
        $descStripped = $this->stripTags($item->getShortDescription(), null, true);
        $this->replaceVariables($descStripped);

        return $this->getHighlightText($descStripped);
    }

    /**
     * @return array[]
     */
    public function getIndexFulltextValues()
    {
        return $this->locationSearchCollection->getIndexFulltextValues();
    }
}
