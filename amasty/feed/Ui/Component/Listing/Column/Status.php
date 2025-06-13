<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    public const STATUS_MAP = [
        0 => 'Inactive',
        1 => 'Active'
    ];

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $status = $item['is_active'] ?? null;

                if (null === $status) {
                    continue;
                }

                $item['status_label'] = self::STATUS_MAP[$status] ?? self::STATUS_MAP[0];
            }
        }

        return $dataSource;
    }
}
