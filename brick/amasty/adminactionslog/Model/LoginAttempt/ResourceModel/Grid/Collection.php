<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems()
    {
        $this->_setIsLoaded(false);
        $this->_items = [];
        $searchCriteria = $this->getSearchCriteria();
        $this->setPageSize($searchCriteria->getPageSize());
        $this->setCurPage($searchCriteria->getCurrentPage());

        return parent::getItems();
    }
}
