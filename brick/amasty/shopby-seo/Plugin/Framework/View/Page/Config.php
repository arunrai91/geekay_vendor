<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Plugin\Framework\View\Page;

use \Magento\Framework\View\Page\Config as NativeConfig;

class Config
{
    public const NOINDEX = 'NOINDEX';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\ShopbySeo\Helper\Meta
     */
    private $metaHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\ShopbySeo\Helper\Meta $metaHelper
    ) {
        $this->request = $request;
        $this->metaHelper = $metaHelper;
    }

    /**
     * @param NativeConfig $subject
     * @param $result
     * @return string
     */
    public function afterGetRobots(
        NativeConfig $subject,
        $result
    ) {
        if ($this->request->getModuleName() === 'catalogsearch') {
            $robots = explode(',', $result);
            $robots[0] = self::NOINDEX;
            $result = implode(',', $robots);
        }

        return $result;
    }

    /**
     * @param NativeConfig $subject
     * @param callable $proceed
     * @param $url
     * @param $contentType
     * @param array $properties
     * @param null $name
     * @return NativeConfig
     */
    public function aroundAddRemotePageAsset(
        NativeConfig $subject,
        callable $proceed,
        $url,
        $contentType,
        array $properties = [],
        $name = null
    ) {
        if ($this->request->getFullActionName() !== 'catalog_product_view'
            && $contentType == 'canonical'
            && !$this->isNeedAddCanonical()
        ) {
            return $subject;
        }

        return $proceed($url, $contentType, $properties, $name);
    }

    public function isNeedAddCanonical(): bool
    {
        return $this->metaHelper->getIndexTagValue();
    }
}
