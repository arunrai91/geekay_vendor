<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Ui\Component\Form;

use Amasty\Base\Ui\Component\Form\PromotionSelectOption;
use Amasty\Base\Ui\Component\Form\PromotionSelectOptionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Wrapper for dynamic OptionSource select with promo options
 */
class PromotionSelectOptionSource implements OptionSourceInterface
{
    /**
     * @var PromotionSelectOptionFactory
     */
    private $promotionSelectOptionFactory;

    /**
     * @var string
     */
    private $promoModuleName;

    /**
     * @var PromotionSelectOption[]|string[] ['label' => 'value']
     */
    private $promoOptions;

    /**
     * @var string
     */
    private $promoLink;

    /**
     * @var OptionSourceInterface
     */
    private $origOptionSource;

    public function __construct(
        PromotionSelectOptionFactory $promotionSelectOptionFactory,
        OptionSourceInterface $origOptionSource,
        string $promoModuleName = '',
        string $promoLink = '',
        array $promoOptions = []
    ) {
        $this->promotionSelectOptionFactory = $promotionSelectOptionFactory;
        $this->promoModuleName = $promoModuleName;
        $this->promoOptions = $promoOptions;
        $this->promoLink = $promoLink;
        $this->origOptionSource = $origOptionSource;
    }

    public function toOptionArray(): array
    {
        $result = $this->origOptionSource->toOptionArray();
        $existingOptionValues = array_column($result, 'value');
        foreach ($this->getPromoOptions() as $promoOption) {
            if (!in_array($promoOption->getValue(), $existingOptionValues, true)) {
                $result[] = $promoOption->toArray();
            }
        }

        return $result;
    }

    /**
     * @return PromotionSelectOption[]
     */
    private function getPromoOptions(): array
    {
        $promoOptions = [];
        foreach ($this->promoOptions as $promoOption) {
            if (is_array($promoOption)) {
                $promoOption = $this->createPromoOption($promoOption);
            }
            $promoOptions[$promoOption->getValue()] = $promoOption;
        }

        return $promoOptions;
    }

    private function createPromoOption(array $promoOption): PromotionSelectOption
    {
        $option = $this->promotionSelectOptionFactory->create();
        $option->setLabel($promoOption['label']);
        $option->setValue($promoOption['value']);
        $option->setModuleName($this->promoModuleName);
        $option->setLink($this->promoLink);

        return $option;
    }
}
