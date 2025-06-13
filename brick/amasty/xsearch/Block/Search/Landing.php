<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

class Landing extends AbstractSearch
{
    public const CATEGORY_BLOCK_LANDING = 'landing_page';

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_LANDING;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $collection = parent::generateCollection()
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->addStoreFilter($this->_storeManager->getStore())
            ->addFieldToFilter('is_active', 1)
            ->setPageSize($this->getLimit());
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getTitle());
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $page)
    {
        $descStripped = $this->stripTags($page->getContent(), null, true);

        return $this->getHighlightText($descStripped);
    }

    public function getSearchUrl(\Magento\Framework\DataObject $item): string
    {
        $store = $this->_storeManager->getStore();
        $this->_urlBuilder->setScope((int)$store->getId());

        return $this->_urlBuilder->getUrl(
            $item->getIdentifier(),
            [
                '_current' => false,
                '_nosid' => true
            ]
        );
    }
}
