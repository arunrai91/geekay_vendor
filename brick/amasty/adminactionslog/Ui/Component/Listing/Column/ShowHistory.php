<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class ShowHistory extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = [
                    'history' => [
                        'callback' => [
                            'provider' => 'amaudit_visithistory_listing.amaudit_visithistory_listing.modal',
                            'target' => 'getHistory'
                        ],
                        'label' => __('Show History')
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
