<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\ResourceModel\Product\CatalogPriceRule;
use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Amasty\Rules\Model\Rule\QuoteStorage;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;

class SpecialPriceValidator implements SkipItemValidatorInterface
{
    /**
     * @var ConfigModel
     */
    private $configModel;

    /**
     * @var QuoteStorage
     */
    private $quoteStorage;

    public function __construct(
        ?CatalogPriceRule $catalogPriceRule, // @deprecated
        ?StoreManagerInterface $storeManager, // @deprecated
        ?Session $customerSession, // @deprecated
        ConfigModel $configModel,
        ?QuoteStorage $quoteStorage = null // TODO: move to not optional argument and remove OM
    ) {
        $this->configModel = $configModel;
        $this->quoteStorage = $quoteStorage ?? ObjectManager::getInstance()->get(QuoteStorage::class);
    }

    public function validate(AbstractItem $item, Rule $rule): bool
    {
        $product = $item->getProduct();
        $specialPrice = $product->getPriceInfo()->getPrice(SpecialPrice::PRICE_CODE);

        if ($specialPrice->getValue()) {
            return true;
        }

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $product = current($item->getChildren())->getProduct();
        }

        return $this->hasCatalogPriceRule((int)$product->getId(), $item->getQuote());
    }

    public function isNeedToValidate(Rule $rule): bool
    {
        $amrule = $rule->getData(AbstractRule::AMASTY_RULE);
        $useGeneralSkipSettings = $amrule->isEnableGeneralSkipSettings();
        $skipConditions = explode(',', (string)$amrule->getSkipRule());

        return ($useGeneralSkipSettings && $this->configModel->getSkipSpecialPrice())
            || (!$useGeneralSkipSettings
                && in_array(SkipItemValidatorInterface::SPECIAL_PRICE, $skipConditions, true));
    }

    private function hasCatalogPriceRule(int $productId, CartInterface $quote): bool
    {
        $productsWithDiscount = $this->quoteStorage->getProductsWithDiscount($quote);

        if (in_array($productId, $productsWithDiscount)) {
            return true;
        }

        return false;
    }
}
