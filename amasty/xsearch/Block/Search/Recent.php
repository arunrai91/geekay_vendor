<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Magento\Framework\DataObject;

class Recent extends AbstractSearch
{
    public const CATEGORY_BLOCK_RECENT = 'recent_searches';

    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_RECENT;
    }

    public function getItemData(DataObject $item): array
    {
        $data = parent::getItemData($item);
        $data['num_results'] = $item->getNumResults();
        $data['full_match'] = strcasecmp($item->getQueryText(), $this->getQuery()->getQueryText()) === 0;

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $collection = parent::generateCollection()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setRecentQueryFilter()
            ->setPageSize($this->getLimit());
        $collection
            ->getSelect()
            ->where('num_results > 0 AND display_in_terms = 1');
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getQueryText());
    }

    /**
     * @return bool
     */
    public function isNoFollow()
    {
        return true;
    }

    public function getCacheLifetime()
    {
        return null;
    }
}
