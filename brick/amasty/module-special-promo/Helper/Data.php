<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Helper;

/**
 * Keeper of action types.
 * @deplacated
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const TYPE_XY_ANY_PRODUCTS = 'buyxgety_anyproducts';
    public const TYPE_CHEAPEST = 'thecheapest';//+
    public const TYPE_CHEAPEST_FIXED = 'thecheapest_fixprice';
    public const TYPE_EXPENCIVE = 'themostexpencive';//+
    public const TYPE_AMOUNT = 'moneyamount';//+

    public const TYPE_EACH_N = 'eachn_perc';//+
    public const TYPE_EACH_N_FIXDISC = 'eachn_fixdisc';//+
    public const TYPE_EACH_N_FIXED = 'eachn_fixprice';//+

    public const TYPE_EACH_M_AFT_N_PERC = 'eachmaftn_perc';//+
    public const TYPE_EACH_M_AFT_N_DISC = 'eachmaftn_fixdisc';//+
    public const TYPE_EACH_M_AFT_N_FIX = 'eachmaftn_fixprice';//+

    public const TYPE_GROUP_N = 'groupn';
    public const TYPE_GROUP_N_DISC = 'groupn_disc';

    public const TYPE_XY_PERCENT = 'buyxgety_perc';
    public const TYPE_XY_FIXED = 'buyxgety_fixprice';
    public const TYPE_XY_FIXDISC = 'buyxgety_fixdisc';

    public const TYPE_XN_PERCENT = 'buyxgetn_perc';
    public const TYPE_XN_FIXED = 'buyxgetn_fixprice';
    public const TYPE_XN_FIXDISC = 'buyxgetn_fixdisc';

    public const TYPE_TIERED_WHOLE_CART = 'tiered_wholecheaper';
    public const TYPE_TIERED_XN = 'tiered_buyxgetcheapern';

    public const TYPE_AFTER_N_FIXED = 'aftern_fixprice';
    public const TYPE_AFTER_N_DISC = 'aftern_disc';
    public const TYPE_AFTER_N_FIXDISC = 'aftern_fixdisc';

    public const TYPE_SETOF_PERCENT = 'setof_percent';
    public const TYPE_SETOF_FIXED = 'setof_fixed';

    public const BUY_X_GET_Y = [
        self::TYPE_XN_PERCENT,
        self::TYPE_XN_FIXED,
        self::TYPE_XN_FIXDISC
    ];

    public const TIERED = [
        self::TYPE_TIERED_WHOLE_CART,
        self::TYPE_TIERED_XN
    ];

    public const TYPE_EACH_M_AFT_N = [
        self::TYPE_EACH_M_AFT_N_PERC,
        self::TYPE_EACH_M_AFT_N_DISC,
        self::TYPE_EACH_M_AFT_N_FIX
    ];

    public const GROUP_EACH_N = [
        self::TYPE_EACH_N,
        self::TYPE_EACH_N_FIXED,
        self::TYPE_EACH_N_FIXDISC,
    ];

    /**
     * @var array
     */
    protected $passedItems = [];

    /**
     * @param int $itemId
     */
    public function addPassedItem($itemId)
    {
        $this->passedItems[] = $itemId;
    }

    /**
     * @return array
     */
    public function getPassedItems()
    {
        return $this->passedItems;
    }

    /**
     * @param bool $asOptions
     *
     * @return array
     */
    public function getDiscountTypes($asOptions = false)
    {
        $types = [
            self::TYPE_XY_ANY_PRODUCTS => __('Buy X get Y free (any products)'),
            self::TYPE_CHEAPEST => __('Percent Discount: The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_CHEAPEST_FIXED => __('Fixed Price: The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_EXPENCIVE => __('The Most Expensive'),
            self::TYPE_AMOUNT => __('Get $Y for each $X spent'),

            self::TYPE_EACH_N => __('Percent Discount: each 2nd, 4th, 6th with N% Off'),
            self::TYPE_EACH_N_FIXDISC => __('Fixed Discount: each 3rd, 6th, 9th with $X Off'),
            self::TYPE_EACH_N_FIXED => __('Fixed Price: each 5th 10th, 15th for $X'),

            self::TYPE_EACH_M_AFT_N_PERC =>
                __('Percent Discount: each 1st, 3rd, 5th with N% Off after X items added to the cart'),
            self::TYPE_EACH_M_AFT_N_DISC =>
                __('Fixed Discount: each 3rd, 7th, 11th with $X Off after Y items added to the cart'),
            self::TYPE_EACH_M_AFT_N_FIX =>
                __('Fixed Price: each 5th, 7th, 9th for $X after Y items added to the cart'),

            self::TYPE_GROUP_N => __('Fixed Price: Each X items for $50'),
            self::TYPE_GROUP_N_DISC => __('Percent Discount: Each X items with N% off'),

            self::TYPE_XN_PERCENT => __('Percent Discount: Buy X get Y Free'),
            self::TYPE_XN_FIXDISC => __('Fixed Discount:  Buy X get Y with $10 Off'),
            self::TYPE_XN_FIXED => __('Fixed Price: Buy X get Y for $9.99'),

            self::TYPE_TIERED_WHOLE_CART => __('For Each $X Spent, get Whole Cart N% Cheaper'),
            self::TYPE_TIERED_XN => __('For Each $10 Spent on X, get item Y 5% Cheaper'),

            self::TYPE_AFTER_N_DISC => __('Percent Discount'),
            self::TYPE_AFTER_N_FIXDISC => __('Fixed Discount'),
            self::TYPE_AFTER_N_FIXED => __('Fixed Price'),

            self::TYPE_SETOF_PERCENT => __('Percent discount for product set'),
            self::TYPE_SETOF_FIXED => __('Fixed price for product set'),

        ];

        if (!$asOptions) {
            $groups = [
                'Popular' => [
                    self::TYPE_XY_ANY_PRODUCTS,
                    self::TYPE_CHEAPEST,
                    self::TYPE_CHEAPEST_FIXED,
                    self::TYPE_EXPENCIVE,
                    self::TYPE_AMOUNT
                ],
                'Buy X Get Y (X and Y are different products)' => [
                    self::TYPE_XN_PERCENT,
                    self::TYPE_XN_FIXDISC,
                    self::TYPE_XN_FIXED
                ],
                'Multiple Tiered Discount' => [
                    self:: TYPE_TIERED_WHOLE_CART,
                    self:: TYPE_TIERED_XN
                ],
                'Each N-th' => [
                    self::TYPE_EACH_N,
                    self::TYPE_EACH_N_FIXDISC,
                    self::TYPE_EACH_N_FIXED
                ],
                'Each Product After N' => [
                    self::TYPE_EACH_M_AFT_N_PERC,
                    self::TYPE_EACH_M_AFT_N_DISC,
                    self::TYPE_EACH_M_AFT_N_FIX
                ],
                'Each Group of N' => [
                    self::TYPE_GROUP_N,
                    self::TYPE_GROUP_N_DISC
                ],
                'Product Set' => [
                    self::TYPE_SETOF_PERCENT,
                    self::TYPE_SETOF_FIXED
                ],
            ];

            $result = [];

            foreach ($groups as $groupName => $groupActions) {
                $values = [];
                foreach ($groupActions as $k) {
                    $values[] = [
                        'value' => $k,
                        'label' => $types[$k],
                    ];
                }
                $result[] = [
                    'label' => __($groupName),
                    'value' => $values,
                ];
            }
            $types = $result;
        }

        return $types;
    }

    /**
     * @param string $rule
     *
     * @return mixed|string
     */
    public function getFilePath($rule)
    {
        $rule = implode('_', array_map('ucfirst', explode('_', $rule)));
        $rule = str_replace('_', '', $rule);
        $rule = 'Amasty\Rules\Model\Rule\Action\Discount\\' . $rule;

        return $rule;
    }

    /**
     * @param string $created
     *
     * @return float
     */
    public function getMembership($created)
    {
        $time = round((time() - strtotime($created)) / 60 / 60 / 24);

        return $time;
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public static function comparePrices($a, $b)
    {
        $res = ($a['price'] < $b['price']) ? -1 : 1;
        if ($a['price'] == $b['price']) {
            $res = ($a['id'] < $b['id']) ? -1 : 1;
            if ($a['id'] == $b['id']) {
                $res = 0;
            }
        }

        return $res;
    }
    //@codingStandardsIgnoreEnd

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return array
     */
    public function getRuleCats($rule)
    {
        $promoCats = explode(',', (string)$rule->getAmrulesRule()->getPromoCats());
        $promoCats = array_map('trim', $promoCats);
        $promoCats = array_filter($promoCats);

        return $promoCats;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return array
     */
    public function getRuleSkus($rule)
    {
        $promoSku = explode(',', (string)$rule->getAmrulesRule()->getPromoSkus());
        $promoSku = array_map('trim', $promoSku);
        $promoSku = array_filter($promoSku);

        return $promoSku;
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @return array
     */
    public static function staticGetDiscountTypes()
    {
        $types = [
            self::TYPE_XY_ANY_PRODUCTS => __('Buy X get Y free (any products)'),
            self::TYPE_CHEAPEST => __('Percent Discount: The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_CHEAPEST_FIXED => __('Fixed Price: The Cheapest, also for Buy 1 get 1 free'),
            self::TYPE_EXPENCIVE => __('The Most Expensive'),
            self::TYPE_AMOUNT => __('Get $Y for each $X spent'),

            self::TYPE_EACH_N => __('Percent Discount: each 2nd, 4th, 6th with N% Off'),
            self::TYPE_EACH_N_FIXDISC => __('Fixed Discount: each 3rd, 6th, 9th with $X Off'),
            self::TYPE_EACH_N_FIXED => __('Fixed Price: each 5th 10th, 15th for $X'),

            self::TYPE_EACH_M_AFT_N_PERC =>
                __('Percent Discount: each 1st, 3rd, 5th with N% Off after X items added to the cart'),
            self::TYPE_EACH_M_AFT_N_DISC =>
                __('Fixed Discount: each 3rd, 7th, 11th with $X Off after Y items added to the cart'),
            self::TYPE_EACH_M_AFT_N_FIX =>
                __('Fixed Price: each 5th, 7th, 9th for $X after Y items added to the cart'),

            self::TYPE_GROUP_N => __('Fixed Price: Each X items for $50'),
            self::TYPE_GROUP_N_DISC => __('Percent Discount: Each X items with N% off'),

            self::TYPE_XY_PERCENT => __('Percent Discount: Buy X get Y Free'),
            self::TYPE_XY_FIXDISC => __('Fixed Discount:  Buy X get Y with $10 Off'),
            self::TYPE_XY_FIXED => __('Fixed Price: Buy X get Y for $9.99'),

            self::TYPE_XN_PERCENT => __('Percent Discount: Buy X get Y Free'),
            self::TYPE_XN_FIXDISC => __('Fixed Discount:  Buy X get N with $10 Off'),
            self::TYPE_XN_FIXED => __('Fixed Price: Buy X get N for $9.99'),

            self::TYPE_TIERED_WHOLE_CART => __('For Each $X Spent, get Whole Cart N% Cheaper'),
            self::TYPE_TIERED_XN => __('For Each $10 Spent on X, get item Y 5% Cheaper'),

            self::TYPE_AFTER_N_DISC => __('Percent Discount'),
            self::TYPE_AFTER_N_FIXDISC => __('Fixed Discount'),
            self::TYPE_AFTER_N_FIXED => __('Fixed Price'),

            self::TYPE_SETOF_PERCENT => __('Percent discount for product set'),
            self::TYPE_SETOF_FIXED => __('Fixed price for product set'),

        ];

        return $types;
    }
    //@codingStandardsIgnoreEnd

    public function _resetState(): void
    {
        $this->passedItems = [];
    }
}
