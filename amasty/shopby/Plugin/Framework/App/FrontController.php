<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\App;

use Amasty\Shopby\Helper\Category as CategoryHelper;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Magento\Framework\App\FrontController as DefaultFronController;
use Magento\Framework\App\RequestInterface;

class FrontController
{
    public const SHOPBY_EXTRA_PARAM = 'amshopby';

    /**
     * @var bool|null
     */
    private $isCategorySingleSelect = null;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    public function __construct(
        CategoryHelper $categoryHelper,
        IsMultiselect $isMultiselect
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->isMultiselect = $isMultiselect;
    }

    /**
     * @param DefaultFronController $subject
     * @param RequestInterface $request
     * @return array
     */
    public function beforeDispatch(DefaultFronController $subject, RequestInterface $request)
    {
        $this->parseAmshopbyParams($request);
        return [$request];
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    private function parseAmshopbyParams(RequestInterface $request)
    {
        if ($amShopbyParams = $request->getParam(self::SHOPBY_EXTRA_PARAM, [])) {
            $amShopbyParams = is_array($amShopbyParams) ? $amShopbyParams : [];
            foreach ($amShopbyParams as $key => $value) {
                if ($key == CategoryHelper::CATEGORY_FILTER_PARAM
                    && $this->isCategorySingleSelect()
                ) {
                    continue;
                }

                $value = is_array($value) ? implode(",", $value) : $value;
                $request->setQueryValue($key, $value);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isCategorySingleSelect()
    {
        if ($this->isCategorySingleSelect === null) {
            $this->isCategorySingleSelect = !$this->isMultiSelectAllowed();
        }

        return $this->isCategorySingleSelect;
    }

    private function isMultiSelectAllowed(): bool
    {
        $filterSetting = $this->categoryHelper->getSetting();

        return $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
