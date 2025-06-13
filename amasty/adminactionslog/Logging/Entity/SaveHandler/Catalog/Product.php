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
        'quantity_and_stock_status'
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
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        $this->logImages->processGalleryBeforeSave($object);

        return parent::processBeforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        $this->logImages->processGalleryAfterSave($object);

        return parent::processAfterSave($object);
    }
}
