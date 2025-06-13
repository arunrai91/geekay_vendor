<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel\Page\Fulltext;

use Zend_Db_Expr;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    /** @var string */
    private $queryText;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    private $fullTextSpecialChars = ['$', '@', '*', '<', '>', '(', ')', '-', '+', '~', '"'];

    /**
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }

        $this->storeId = (int)$storeId;

        return $this;
    }

    /**
     * @param  \Magento\Cms\Model\ResourceModel\Page\Collection $collection
     * @param $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns($collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }

        return [];
    }

    /**
     * @see \Amasty\Xsearch\Integration\Model\ResourceModel\Page\Fulltext\CollectionSortingTest
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $searchQuery = $this->getSearchQuery();
        if ($searchQuery) {
            $adapter = $this->getConnection();
            $matchCondition = $this->getMatchCondition();
            $this->getSelect()
                ->where($matchCondition, $searchQuery)
                /*
                 * Order title matching in first.
                 * Shouldn't impact on productivity because sorting working after WHERE.
                 *
                 * Can't use match for single column, because all fields in one fulltext index.
                 * Fulltext index can be used only for all columns in the same time.
                 */
                ->order(new \Zend_Db_Expr(
                    $adapter->quoteInto('title LIKE ? DESC', str_replace('*', '%', $searchQuery))
                ))
                ->order(new Zend_Db_Expr(
                    $adapter->quoteInto($matchCondition, $searchQuery)
                    . ' DESC'
                ));
        }

        parent::_renderFiltersBefore();
    }

    /**
     * @return string
     */
    private function getMatchCondition()
    {
        $query = trim(str_replace($this->fullTextSpecialChars, ' ', $this->queryText));
        $columns = $this->getFulltextIndexColumns($this, $this->getMainTable());
        $matchMode = (strlen($query) > 2) ? ' IN BOOLEAN MODE' : '';

        return 'MATCH(' . implode(',', $columns) . ") AGAINST(?$matchMode)";
    }

    /**
     * @return string
     */
    private function getSearchQuery()
    {
        $query = trim(str_replace($this->fullTextSpecialChars, ' ', $this->queryText));

        if (strlen($query) > 2) {
            $query .= '*';
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getIndexFulltextValues()
    {
        $fulltextValues = [];
        foreach ($this->getItems() as $id => $item) {
            $fulltextString = '';
            $indexColumns = $this->getFulltextIndexColumns($this, $this->getMainTable());
            foreach ($indexColumns as $indexColumn) {
                if ($item->getData($indexColumn)) {
                    $fulltextString .= ' ' . trim($item->getData($indexColumn));
                }
            }

            $fulltextValues[$id] = trim($fulltextString);
        }

        return $fulltextValues;
    }
}
