<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Ui\Listing\Columns\ProductActions;

use Magento\Catalog\Ui\Component\Listing\Columns\ProductActions;

class RestrictNameAttribute
{
    public function beforePrepareDataSource(ProductActions $subject, array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['name'])) {
                    $item['name'] = '';
                }
            }
        }

        return [$dataSource];
    }
}
