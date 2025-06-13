<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Plugin\XmlSitemap\ShopbyBase\Model;

class Sitemap
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $helperUrl;

    public function __construct(
        \Amasty\ShopbySeo\Helper\Url $helperUrl
    ) {
        $this->helperUrl = $helperUrl;
    }

    /**
     * @param $subject
     * @param $url
     * @return string
     */
    public function afterApplySeoUrl($subject, $url)
    {
        if ($this->helperUrl->isSeoUrlEnabled()) {
            $url = $this->helperUrl->seofyUrl($url);
        }

        return $url;
    }
}
