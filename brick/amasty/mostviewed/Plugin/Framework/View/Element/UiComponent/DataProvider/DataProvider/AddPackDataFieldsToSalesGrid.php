<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Framework\View\Element\UiComponent\DataProvider\DataProvider;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\OptionSource\Packs as PacksSource;
use Magento\Config\Model\Config\Source\Yesno as YesnoSource;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Listing\Columns\Column;

class AddPackDataFieldsToSalesGrid
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var YesnoSource
     */
    private $yesnoSource;

    /**
     * @var PacksSource
     */
    private $packsSource;

    public function __construct(ConfigProvider $configProvider, YesnoSource $yesnoSource, PacksSource $packsSource)
    {
        $this->configProvider = $configProvider;
        $this->yesnoSource = $yesnoSource;
        $this->packsSource = $packsSource;
    }

    /**
     * @param DataProvider $subject
     * @param array $meta
     * @return array
     */
    public function afterGetMeta(DataProvider $subject, $meta)
    {
        if (is_array($meta)
            && $subject->getName() === 'sales_order_grid_data_source'
            && $this->configProvider->isJoinPackDataToSalesGrid()
        ) {
            $meta['sales_order_columns']['children']['mostviewed_includes_bundles'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Column::NAME,
                            'component' => 'Magento_Ui/js/grid/columns/select',
                            'label' => __('Includes Bundle Pack'),
                            'filter' => Select::NAME,
                            'dataType' => Select::NAME,
                            'options' => $this->yesnoSource->toOptionArray()
                        ]
                    ]
                ]
            ];
            $meta['sales_order_columns']['children']['mostviewed_bundles'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Column::NAME,
                            'label' => __('Bundle Pack(s)'),
                            'filter' => Select::NAME,
                            'dataType' => Select::NAME,
                            'options' => $this->packsSource->toOptionArray()
                        ]
                    ]
                ]
            ];
        }

        return $meta;
    }
}
