<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\Category\DataProvider;

use Amasty\Base\Ui\Component\Form\PromotionSelectOption;
use Magento\Catalog\Model\Category\DataProvider;

class AddPromoLayoutOptions
{
    /**
     * @var PromotionSelectOption[]
     */
    private $promoLayoutOptions;

    public function __construct(
        array $promoLayoutOptions = []
    ) {
        $this->promoLayoutOptions = $promoLayoutOptions;
    }

    public function afterGetMeta(
        DataProvider $subject,
        array $result
    ): array {
        $options = $result["design"]["children"]["page_layout"]["arguments"]["data"]["config"]["options"] ?? [];

        if (empty($options)) {
            return $result;
        }

        $promoOptions = [];

        foreach ($this->promoLayoutOptions as $promoOption) {
            if ($this->isAlreadyHaveOption($options, $promoOption)) {
                continue;
            }

            $promoOptions[] = $promoOption->toArray();
        }

        $result["design"]["children"]["page_layout"]["arguments"]["data"]["config"]["options"]
            = array_merge($options, $promoOptions);

        return $result;
    }

    private function isAlreadyHaveOption(array $options, PromotionSelectOption $option): bool
    {
        return !empty(array_filter($options, function ($item) use ($option) {
            return $item['value'] === $option->getValue();
        }));
    }
}
