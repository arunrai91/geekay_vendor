<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Ogrid\Helper\Data;

use Amasty\Ogrid\Helper\Data as OgridHelper;
use Amasty\Rolepermissions\Helper\Data as Helper;

class RestrictOrderItemFields
{
    public const ORDER_ITEM_FIELD_PREFIX = 'amasty_ogrid_product_';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var bool
     */
    private $processed = false;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function afterGetOrderItemFields(
        OgridHelper $subject,
        array $result
    ): array {
        if (!$this->processed) {
            if ($restrictedAttributeCodes = $this->helper->getRestrictedAttributeCodes()) {
                foreach ($result as $field => $value) {
                    $fieldAttribute = str_replace(self::ORDER_ITEM_FIELD_PREFIX, '', $field);

                    if (in_array($fieldAttribute, $restrictedAttributeCodes)) {
                        unset($result[$field]);
                    }
                }
            }

            $this->processed = true;
        }

        return $result;
    }
}
