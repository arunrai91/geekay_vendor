<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api;

interface UrlModifierInterface
{
    /**
     * @param string $url
     * @param int|null $categoryId
     * @param bool $skipModuleCheck
     * @return string
     */
    public function modifyUrl($url, ?int $categoryId = null, bool $skipModuleCheck = false);
}
