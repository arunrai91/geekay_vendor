<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\Catalog\Model\Product\Gallery\LogImages;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;

class Product extends Common
{
    public const CATEGORY = 'catalog/product/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'current_product_id',
        'affect_product_custom_options',
        'current_store_id',
        'product_has_weight',
        'is_new',
        '_edit_mode',
        'amrolepermissions_owner',
        'use_config_gift_message_available',
        'use_config_gift_wrapping_available',
        'url_key_create_redirect',
        'use_config_is_returnable',
        'can_save_custom_options',
        'save_rewrites_history',
        'is_custom_option_changed',
        'special_from_date_is_formated',
        'special_to_date_is_formated',
        'custom_design_from_is_formated',
        'news_from_date_is_formated',
        'news_to_date_is_formated',
        'force_reindex_eav_required',
        'updated_at',
        'has_options',
        'required_options',
        'quantity_and_stock_status',
        'is_changed_categories'
    ];

    /**
     * @var LogImages
     */
    private $logImages;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        ?LogImages $logImages = null
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);
        $this->logImages = $logImages ?? ObjectManager::getInstance()->get(LogImages::class);
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $metadata->getObject();

        if (!$product->getName()) {
            $product->load($product->getId()); // Force reload product in cases of mass delete, etc.
        }

        return [
            LogEntry::ITEM => $product->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Catalog Product'),
            LogEntry::ELEMENT_ID => (int)$product->getId(),
            LogEntry::STORE_ID => (int)$product->getStoreId()
        ];
    }

    /**
     * @param AbstractModel $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        $this->logImages->processGalleryBeforeSave($object);
        $preparedData = parent::processBeforeSave($object);
        $this->prepareCurrentCategories((array)$object->getData(), (array)$object->getOrigData(), $preparedData);

        return $preparedData;
    }

    /**
     * @param AbstractModel $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        $this->logImages->processGalleryAfterSave($object);
        $preparedData = parent::processAfterSave($object);
        $newData = (array)$object->getData();

        if (isset($newData['category_ids'])
            && is_array($newData['category_ids'])
        ) {
            $preparedData['category_ids'] = implode(',', $newData['category_ids']);
        }

        return $preparedData;
    }

    private function prepareCurrentCategories(array $data, array $orig, array &$preparedData): void
    {
        if (!isset($data['category_ids']) || !is_array($data['category_ids'])) {
            return;
        }

        if (!isset($orig['category_ids']) || !is_array($orig['category_ids'])) {
            $preparedData['category_ids'] = implode(',', $data['category_ids']);
            return;
        }

        // looking for difference between two category arrays
        if (count($orig['category_ids']) !== count($data['category_ids'])
            || array_diff($orig['category_ids'], $data['category_ids']) !== []) {
            $preparedData['category_ids'] = implode(',', $orig['category_ids']);
        } else {
            $preparedData['category_ids'] = implode(',', $data['category_ids']);
        }
    }
}
