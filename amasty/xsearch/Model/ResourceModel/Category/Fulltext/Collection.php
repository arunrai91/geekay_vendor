<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel\Category\Fulltext;

use Magento\Framework\Search\Response\QueryResponse;

class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    /**
     * @var int[]
     */
    private $attributeIdByCode = [];

    /**
     * @var string
     */
    private $queryText;

    /**
     * @var array
     */
    private $fullTextSpecialChars = ['$', '@', '*', '<', '>', '(', ')', '-', '+', '~', '"', '.'];

    /**
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $query = str_replace($this->fullTextSpecialChars, ' ', $query);
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    protected function _renderFiltersBefore()
    {
        if ($this->queryText) {
            $select = $this->getSelect();
            $select->joinInner(
                ['index_table' => $this->getSearchSelect()],
                'index_table.entity_id = e.entity_id',
                []
            );

            $select->group('e.entity_id');
            $select->order('SUM(index_table.score) DESC');
        }

        parent::_renderFiltersBefore();
    }

    /**
     * Get search query with score expression.
     *
     * Results with full word match have bigger score than whildcart result.
     * Attributes may have different search weight.
     */
    public function getSearchSelect(): \Magento\Framework\DB\Select
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['index_table' => $this->resolveIndexTable()], ['index_table.entity_id']);

        $whildcartQuery = sprintf(
            'MATCH(index_table.data_index) AGAINST (%s IN BOOLEAN MODE)',
            $connection->quote($this->queryText . '*')
        );
        $matchQuery = sprintf(
            'MATCH(index_table.data_index) AGAINST (%s)',
            $connection->quote($this->queryText)
        );

        $scoreExpression = new \Zend_Db_Expr(sprintf(
            '(%s + %s) * (%s)',
            $matchQuery,
            $whildcartQuery,
            $this->getAttributeWightExpression()
        ));
        $select->columns(['score' => $scoreExpression]);
        $select->where($whildcartQuery);

        return $select;
    }

    /**
     * Case rules for attribute weight.
     *
     * Category name have slightly bigger weight, because it is only who is visible on frontend.
     */
    public function getAttributeWightExpression(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $cases = [
            $connection->quote($this->getAttributeIdByCode('name')) => 8,
            $connection->quote($this->getAttributeIdByCode('meta_title')) => 2,
        ];
        
        return $connection->getCaseSql('attribute_id', $cases, 1);
    }

    private function getAttributeIdByCode(string $code): int
    {
        if (!isset($this->attributeIdByCode[$code])) {
            $this->attributeIdByCode[$code] = (int) $this->getEntity()->getAttribute($code)->getAttributeId();
        }

        return $this->attributeIdByCode[$code];
    }

    /**
     * @return array[]
     */
    public function getIndexFulltextValues()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['posts_tags' => $this->getTable('amasty_xsearch_category_fulltext_scope') . $this->getStoreId()],
                ['entity_id', 'data_index']
            );
        $items = $this->getConnection()->fetchAll($select);
        $result = [];
        foreach ($items as $item) {
            $value = trim($item['data_index']);
            $id = $item['entity_id'];
            if (!isset($result[$id])) {
                $result[$id] = $value;
            } else {
                $result[$id] .= ' ' . $value;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function resolveIndexTable()
    {
        return $this->getTable('amasty_xsearch_category_fulltext_scope') . $this->getStoreId();
    }
}
