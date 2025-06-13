<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store;

class StoreView extends Store
{
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['store_id'])) {
                $storeId = (int)$item['store_id'];
                $item['store_id'] = [];
                $item['store_id'][] = $storeId;
            }
        }

        $dataSource = parent::prepareDataSource($dataSource);

        return $dataSource;
    }
}
