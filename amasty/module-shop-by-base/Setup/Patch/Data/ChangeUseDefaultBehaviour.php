<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * From now on, all option settings with "use default" will be null instead of copying from default
 */
class ChangeUseDefaultBehaviour implements DataPatchInterface
{
    /**
     * @var OptionSetting
     */
    private $optionResource;

    /**
     * @var ColumnValueExpressionFactory
     */
    private $expressionFactory;

    public function __construct(
        OptionSetting $optionResource,
        ColumnValueExpressionFactory $expressionFactory
    ) {
        $this->optionResource = $optionResource;
        $this->expressionFactory = $expressionFactory;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $this->updateStoreValues();
        $this->updateEavValues();

        return $this;
    }

    private function updateStoreValues(): void
    {
        $connection = $this->optionResource->getConnection();
        $tableName = $this->optionResource->getTable('amasty_amshopby_option_setting');
        $storeIds = $this->fetchStoreIds($connection, $tableName);

        if (!$storeIds) {
            return;
        }

        foreach ($storeIds as $storeId) {
            $select = $connection->select()->join(
                ['os_default' => $tableName],
                'os.value = os_default.value AND os_default.store_id = 0',
                [
                    'url_alias' => $this->ifColumnExpression('url_alias', 'os_default.url_alias'),
                    'is_featured' => $this->ifColumnExpression('is_featured', 'os_default.is_featured'),
                    'meta_title' => $this->ifColumnExpression('meta_title', 'os_default.meta_title'),
                    'meta_description' => $this->ifColumnExpression('meta_description', 'os_default.meta_description'),
                    'meta_keywords' => $this->ifColumnExpression('meta_keywords', 'os_default.meta_keywords'),
                    'title' => $this->ifColumnExpression('title', 'os_default.title'),
                    'description' => $this->ifColumnExpression('description', 'os_default.description'),
                    'image' => $this->ifColumnExpression('image', 'os_default.image'),
                    'top_cms_block_id' => $this->ifColumnExpression('top_cms_block_id', 'os_default.top_cms_block_id'),
                    'bottom_cms_block_id' => $this->ifColumnExpression(
                        'bottom_cms_block_id',
                        'os_default.bottom_cms_block_id'
                    ),
                    'slider_position' => $this->ifColumnExpression('slider_position', 'os_default.slider_position'),
                    'slider_image' => $this->ifColumnExpression('slider_image', 'os_default.slider_image'),
                    'short_description' => $this->ifColumnExpression(
                        'short_description',
                        'os_default.short_description'
                    ),
                    'image_alt' => $this->ifColumnExpression('image_alt', 'os_default.image_alt'),
                    'small_image_alt' => $this->ifColumnExpression('small_image_alt', 'os_default.small_image_alt'),
                    'is_show_in_widget' => $this->ifColumnExpression(
                        'is_show_in_widget',
                        'os_default.is_show_in_widget'
                    ),
                    'is_show_in_slider' => $this->ifColumnExpression(
                        'is_show_in_slider',
                        'os_default.is_show_in_slider'
                    ),
                ]
            )->where(
                'os.store_id = :store_id'
            );
            $query = $connection->updateFromSelect($select, ['os' => $tableName]);
            $connection->query($query, ['store_id' => $storeId]);
        }
    }

    private function fetchStoreIds(): array
    {
        $connection = $this->optionResource->getConnection();
        $tableName = $this->optionResource->getTable('amasty_amshopby_option_setting');
        $storeIdSelect = $connection->select()
            ->from($tableName, 'store_id')
            ->distinct(true)
            ->where('store_id != 0');

        return $connection->fetchCol($storeIdSelect) ?: [];
    }

    private function ifColumnExpression(string $column, string $compare): ColumnValueExpression
    {
        $expression = sprintf('IF(os.%1$s = %2$s, NULL, os.%1$s)', $column, $compare);

        return $this->expressionFactory->create(['expression' => $expression]);
    }

    private function updateEavValues(): void
    {
        $connection = $this->optionResource->getConnection();
        $eavTableName = $this->optionResource->getTable('eav_attribute_option_value');
        $tableName = $this->optionResource->getTable('amasty_amshopby_option_setting');
        $select = $connection->select()->join(
            ['eav' => $eavTableName],
            'os.value = eav.option_id AND eav.store_id = 0',
            [
                'meta_title' => $this->ifColumnExpression('meta_title', 'eav.value OR os.meta_title = ""'),
                'title' => $this->ifColumnExpression('title', 'eav.value OR os.title = ""')
            ]
        )->where(
            'os.store_id = 0'
        );
        $query = $connection->updateFromSelect($select, ['os' => $tableName]);
        $connection->query($query);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
