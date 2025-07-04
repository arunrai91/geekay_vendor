<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Api;

interface PageRepositoryInterface
{
    /**
     * @param \Amasty\ShopbyPage\Api\Data\PageInterface $page
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface
     */
    public function save(\Amasty\ShopbyPage\Api\Data\PageInterface $page);

    /**
     * @param int $id
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface
     */
    public function get($id);

    /**
     * @param int $categoryId
     * @param int $storeId
     *
     * @return \Amasty\ShopbyPage\Api\Data\PageSearchResultsInterface
     */
    public function getList($categoryId, $storeId);

    /**
     * @param \Amasty\ShopbyPage\Api\Data\PageInterface $pageData
     * @return bool
     */
    public function delete(\Amasty\ShopbyPage\Api\Data\PageInterface $pageData);

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}
