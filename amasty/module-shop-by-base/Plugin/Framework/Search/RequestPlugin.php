<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Framework\Search;

use Magento\Framework\Search\Request;

class RequestPlugin
{
    public const DEFAULT_FIELD_NAME = 'entity_id';

    /**
     * @param Request $request
     * @param array $result
     * @return array
     */
    public function afterGetSort(Request $request, $result)
    {
        foreach ($result as $key => $sort) {
            if (array_key_exists('field', $sort) && $sort['field'] === null) {
                $result[$key]['field'] = self::DEFAULT_FIELD_NAME;
            }
        }

        return $result;
    }
}
