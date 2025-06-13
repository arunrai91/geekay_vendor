<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting as OptionSettingResource;
use Amasty\ShopbyBase\Model\StoreData\ScopedFieldsProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * OptionSetting Collection
 * @method OptionSettingResource getResource()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var Option\CollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var ScopedFieldsProvider
     */
    private $scopedFieldsProvider;

    /**
     * @var int|null
     */
    private $storeId;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Option\CollectionFactory $optionCollectionFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null,
        ?ScopedFieldsProvider $scopedFieldsProvider = null //TODO: move to not optional
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->scopedFieldsProvider = $scopedFieldsProvider
            ?? ObjectManager::getInstance()->get(ScopedFieldsProvider::class);
    }

    /**
     * Collection protected constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShopbyBase\Model\OptionSetting::class,
            OptionSettingResource::class
        );
        $this->_idFieldName = $this->getResource()->getIdFieldName();
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return $this
     * @deprecated use addLoadFilters with attribute code
     */
    public function addLoadParams($filterCode, $optionId, $storeId)
    {
        return $this->addLoadFilters(
            FilterSetting::convertToAttributeCode($filterCode),
            (int) $optionId,
            (int) $storeId
        );
    }

    /**
     * @return $this
     */
    public function addLoadFilters(string $attributeCode, int $optionId, int $storeId = Store::DEFAULT_STORE_ID)
    {
        $listStores = [Store::DEFAULT_STORE_ID];
        if ($storeId > Store::DEFAULT_STORE_ID) {
            $listStores[] = $storeId;
        }

        $this->addFieldToFilter(OptionSettingInterface::ATTRIBUTE_CODE, $attributeCode)
            ->addFieldToFilter('value', $optionId)
            ->addFieldToFilter('store_id', $listStores)
            ->addOrder('store_id', self::SORT_ORDER_DESC);

        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     * @deprecared moved to Resource model
     */
    public function getHardcodedAliases($storeId)
    {
        return $this->getResource()->getHardcodedAliases($storeId);
    }

    /**
     * @param $value
     * @param $storeId
     * @return mixed
     */
    public function getValueFromMagentoEav($value, $storeId)
    {
        $optionCollection = $this->optionCollectionFactory->create()
            ->addFieldToFilter('main_table.option_id', $value)
            ->addFieldToFilter('option.store_id', [0, $storeId])
            ->setOrder('option.store_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $optionCollection->getSelect()
            ->setPart('columns', [])
            ->join(
                ['option' => $optionCollection->getTable('eav_attribute_option_value')],
                'option.option_id = main_table.option_id',
                ['value']
            );

        return $this->getConnection()->fetchOne($optionCollection->getSelect());
    }

    /**
     * @return $this
     */
    public function addTitleToCollection()
    {
        $this->getSelect()->joinInner(
            ['amshopbybrand_option' => $this->getTable('eav_attribute_option')],
            'main_table.value = amshopbybrand_option.option_id',
            []
        );
        $this->join(
            ['option' => $this->getTable('eav_attribute_option_value')],
            'option.option_id = main_table.value'
        );
        $this->getSelect()->columns('IF(main_table.title IS NULL, option.value, main_table.title) as title');
        $this->getSelect()->columns(
            'IF(main_table.meta_title IS NULL, option.value, main_table.meta_title) as meta_title'
        );
        $this->getSelect()->group('option_setting_id');

        return $this;
    }

    public function addStoreData(int $storeId): void
    {
        if (!$this->getFlag('option_store_data_added')) {
            $this->storeId = $storeId;

            $this->addFieldToFilter(
                OptionSettingInterface::STORE_ID,
                Store::DEFAULT_STORE_ID
            );

            $this->getSelect()->joinLeft(
                ['store_table' => $this->getResource()->getMainTable()],
                sprintf(
                    'main_table.value = store_table.value AND store_table.store_id = %d',
                    $storeId
                ),
                []
            );

            foreach ($this->getGlobalColumns() as $field) {
                $this->addFieldToSelect($field, $field);
            }

            foreach ($this->getStoreColumns() as $field) {
                $this->addFieldToSelect($field, $field);
            }

            $this->setFlag('option_store_data_added', true);
        }
    }

    /**
     * @param string|array $field
     * @param string|null $alias
     * @return AbstractCollection
     */
    public function addFieldToSelect($field, $alias = null): AbstractCollection
    {
        if (is_string($field)
            && $this->storeId
            && in_array($field, $this->getStoreColumns())
        ) {
            $field = $this->getExpressionForStoreColumn($field);
        }

        return parent::addFieldToSelect($field, $alias);
    }

    /**
     * @param string|array $field
     * @param null|string|array $condition
     * @return AbstractCollection
     */
    public function addFieldToFilter($field, $condition = null): AbstractCollection
    {
        if (is_string($field)
            && $this->storeId
            && in_array($field, $this->getStoreColumns())
        ) {
            $field = $this->getExpressionForStoreColumn($field);
        } elseif (in_array($field, $this->getGlobalColumns())) {
            $field = 'main_table.' . $field;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    private function getExpressionForStoreColumn(string $field): Zend_Db_Expr
    {
        return $this->getConnection()->getIfNullSql('store_table.' . $field, 'main_table.' . $field);
    }

    /**
     * @return string[]
     */
    private function getGlobalColumns(): array
    {
        return $this->scopedFieldsProvider->getNotNullableFields($this->getMainTable());
    }

    /**
     * @return string[]
     */
    private function getStoreColumns(): array
    {
        return $this->scopedFieldsProvider->getNullableFields($this->getMainTable());
    }
}
