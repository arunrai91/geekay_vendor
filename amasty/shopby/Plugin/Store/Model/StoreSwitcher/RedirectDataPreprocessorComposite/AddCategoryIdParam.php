<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Store\Model\StoreSwitcher\RedirectDataPreprocessorComposite;

use Amasty\Shopby\Plugin\Store\ViewModel\SwitcherUrlProvider\ModifyUrlData;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorComposite;

class AddCategoryIdParam
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        RedirectDataPreprocessorComposite $subject,
        array $result
    ): array {
        if ($categoryId = $this->request->getParam(ModifyUrlData::CATEGORY_ID)) {
            $result[ModifyUrlData::CATEGORY_ID] = $categoryId;
        }

        return $result;
    }
}
