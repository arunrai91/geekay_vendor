<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Integration;

use Amasty\ShopbySeo\Model\ResourceModel\Eav\Model\Entity\Attribute\Option\Collection as OptionsCollection;
use Magento\TestFramework\Helper\Bootstrap;

class OptionProcessor
{
    public const OPTION_1 = '{OPTION_1_ID}';
    public const OPTION_2 = '{OPTION_2_ID}';
    public const OPTION_3 = '{OPTION_3_ID}';
    public const OPTION_AS_ATTR = '{OPTION_AS_ATTR}';

    /**
     * option = "someattr"
     */
    public const OPTION_AS_DIF_ATTR = '{OPTION_AS_DIF_ATTR}';
    public const OPTION_AS_DIF_OPT_A1 = '{OPTION_AS_DIF_OPT_A1}';
    public const OPTION_21_ID = '{OPTION_21_ID}';
    public const OPTION_AS_DIF_OPT_A2 = '{OPTION_AS_DIF_OPT_A2}';
    public const OPTION_UNIQ_RUINER_A2 = '{OPTION_UNIQ_RUINER_A2}';

    /**
     * option = "third_attr"
     * attribute_code = "third_attr"
     * alias = "attribute_alias"
     */
    public const OPTION_ALIAS_ATTR_1 = '{OPTION_ALIAS_ATTR_1}';

    public const TEMPLATE_ARRAY = [
        self::OPTION_1,
        self::OPTION_2,
        self::OPTION_3,
        self::OPTION_AS_ATTR,
        self::OPTION_AS_DIF_ATTR,
        self::OPTION_AS_DIF_OPT_A1,
        self::OPTION_21_ID,
        self::OPTION_AS_DIF_OPT_A2,
        self::OPTION_UNIQ_RUINER_A2,
        self::OPTION_ALIAS_ATTR_1,
    ];

    public static function processStringTemplate(string $template): string
    {
        $params = self::processOptionIdTemplateParams([$template]);

        return reset($params);
    }

    public static function processOptionIdTemplateParams(array $expectedOptions): array
    {
        /** @var OptionsCollection $collection */
        $collection = Bootstrap::getObjectManager()->create(OptionsCollection::class);

        $collection->addAttributeFilter(['dropdown_attribute', 'someattr', 'third_attr']);
        $optionIds = $collection->getColumnValues('option_id');

        foreach ($expectedOptions as &$option) {
            $option = str_replace(self::TEMPLATE_ARRAY, $optionIds, $option);
        }

        return $expectedOptions;
    }
}
