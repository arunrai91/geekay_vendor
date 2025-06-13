<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form;

class History extends AbstractModifier
{
    public const GROUP_HISTORY = 'history';
    public const GROUP_CONTENT = 'content';
    public const SORT_ORDER = 100;
    public const LISTING_NS = 'amaudit_product_history_listing';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_product_id'] = $productId;

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->locator->getProduct()->getId()) {
            return $meta;
        }

        $meta[static::GROUP_HISTORY] = [
            'children' => [
                self::LISTING_NS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'amaudit_product_history_listing',
                                'externalProvider' => self::LISTING_NS . '.amaudit_actionslog_listing_data_source',
                                'selectionsProvider' => self::LISTING_NS . '.' . self::LISTING_NS
                                    . '.product_columns.ids',
                                'ns' => 'amaudit_product_history_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => false,
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id',
                                    '__disableTmpl' => ['productId' => false]
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id',
                                    '__disableTmpl' => ['productId' => false]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('History of Changes'),
                        'collapsible' => true,
                        'opened' => false,
                        'componentType' => Form\Fieldset::NAME,
                        'sortOrder' =>
                            $this->getNextGroupSortOrder(
                                $meta,
                                static::GROUP_CONTENT,
                                static::SORT_ORDER
                            ),
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
