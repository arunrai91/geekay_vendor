<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * System config provider.
 */
class ConfigModel
{
    /**
     * module name
     */
    public const MODULE = 'amrules';

    /**#@+
     * Constants defined for section names
     */
    public const SECTION_GENERAL = 'general';
    public const SECTION_SKIP_PRICE = 'skip_price';
    public const SECTION_DISCOUNT_BREAKDOWN = 'discount_breakdown';
    public const SECTION_DEBUG = 'discount_debug';
    public const SECTION_DISCOUNT_DROPDOWN = 'discount_dropdown';
    /**#@-*/

    /**#@+
     * Constants defined for field names
     */
    public const FIELD_SKIP_SPECIAL_PRICE = 'skip_special_price';
    public const FIELD_SKIP_SPECIAL_PRICE_CONFIGURABLE = 'skip_special_price_configurable';
    public const FIELD_SKIP_TIER_PRICE = 'skip_tier_price';
    public const FIELD_OPTIONS_VALUE = 'options_values';
    public const FIELD_DISCOUNT_BREAKDOWN_ENABLE = 'enable';
    public const FIELD_DEBUG_ENABLE = 'enable_debug';
    public const FIELD_ALLOWED_IP = 'allowed_ip';
    public const SHOW_EMPTY_DISCOUNT = 'show_empty_discount';
    public const FIELD_DISCOUNT_DROPDOWN_ENABLE = 'enabled';
    public const SHOW_RULE_NAME_DISCOUNT_DROPDOWN = 'add_rule_name';
    public const SHOW_RULE_DESCRIPTION_DISCOUNT_DROPDOWN = 'add_rule_description';
    public const SHOW_RULE_DATE_DISCOUNT_DROPDOWN = 'add_rule_date';
    /**#@-*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getOptionsValue()
    {
        return $this->getConfigValue(self::SECTION_GENERAL, self::FIELD_OPTIONS_VALUE);
    }

    public function showEmptyDiscount(?int $storeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE . '/' . self::SECTION_GENERAL . '/' . self::SHOW_EMPTY_DISCOUNT,
            $scope,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function getSkipSpecialPrice()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_SPECIAL_PRICE);
    }

    /**
     * @return mixed
     */
    public function getSkipTierPrice()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_TIER_PRICE);
    }

    /**
     * @return mixed
     */
    public function getSkipSpecialPriceConfigurable()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_SPECIAL_PRICE_CONFIGURABLE);
    }

    /**
     * @return mixed
     */
    public function getShowDiscountBreakdown()
    {
        return $this->getConfigValue(self::SECTION_DISCOUNT_BREAKDOWN, self::FIELD_DISCOUNT_BREAKDOWN_ENABLE);
    }

    /**
     * @return bool
     */
    public function isDebugEnable()
    {
        return (bool) $this->getConfigValue(self::SECTION_DEBUG, self::FIELD_DEBUG_ENABLE);
    }

    /**
     * @return string|null
     */
    public function getAllowedIps()
    {
        return $this->getConfigValue(self::SECTION_DEBUG, self::FIELD_ALLOWED_IP);
    }

    public function isDiscountDropdownEnable(): bool
    {
        return (bool)$this->getConfigValue(self::SECTION_DISCOUNT_DROPDOWN, self::FIELD_DISCOUNT_DROPDOWN_ENABLE);
    }

    public function isShowRuleNameInDiscountDropdown(): bool
    {
        return (bool)$this->getConfigValue(self::SECTION_DISCOUNT_DROPDOWN, self::SHOW_RULE_NAME_DISCOUNT_DROPDOWN);
    }

    public function isShowRuleDescriptionInDiscountDropdown(): bool
    {
        return (bool)$this->getConfigValue(
            self::SECTION_DISCOUNT_DROPDOWN,
            self::SHOW_RULE_DESCRIPTION_DISCOUNT_DROPDOWN
        );
    }

    public function isShowDateInDiscountDropdown(): bool
    {
        return (bool)$this->getConfigValue(self::SECTION_DISCOUNT_DROPDOWN, self::SHOW_RULE_DATE_DISCOUNT_DROPDOWN);
    }

    /**
     * @param string $section
     * @param string $field
     * @param string $module
     * @return mixed
     */
    private function getConfigValue($section, $field, $module = self::MODULE)
    {
        return $this->scopeConfig->getValue(
            $module . '/' . $section . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
