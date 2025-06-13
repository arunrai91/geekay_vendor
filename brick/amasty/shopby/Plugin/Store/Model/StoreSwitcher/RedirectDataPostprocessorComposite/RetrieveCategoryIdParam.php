<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Store\Model\StoreSwitcher\RedirectDataPostprocessorComposite;

use Amasty\Shopby\Model\StoreSwitcher\CategoryRegistry;
use Amasty\Shopby\Plugin\Store\ViewModel\SwitcherUrlProvider\ModifyUrlData;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorComposite;

class RetrieveCategoryIdParam
{
    /**
     * @var CategoryRegistry
     */
    private $categoryRegistry;

    public function __construct(CategoryRegistry $categoryRegistry)
    {
        $this->categoryRegistry = $categoryRegistry;
    }

    /**
     * @param RedirectDataPostprocessorComposite $subject
     * @param null $result
     * @param ContextInterface $context
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        RedirectDataPostprocessorComposite $subject,
        $result,
        ContextInterface $context,
        array $data
    ): void {
        if (isset($data[ModifyUrlData::CATEGORY_ID])) {
            $this->categoryRegistry->set((int) $data[ModifyUrlData::CATEGORY_ID]);
        }
    }
}
