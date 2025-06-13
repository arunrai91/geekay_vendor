<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api;

interface UrlBuilderInterface
{
    /**
     * @param null|string $routePath
     * @param null|array $routeParams
     * @param bool $skipUrlModify
     * @param null|int $categoryId
     * @return null|string
     */
    public function getUrl(
        ?string $routePath = null,
        ?array $routeParams = null,
        bool $skipUrlModify = false,
        ?int $categoryId = null
    ): ?string;

    /**
     * @param bool $modified = true
     * @return string
     */
    public function getCurrentUrl($modified = true);

    /**
     * @param array $params
     * @return string|null
     */
    public function getBaseUrl($params = []);
}
