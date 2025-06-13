<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\ResourceModel;

use Amasty\RulesPro\Model\Cache;
use Amasty\RulesPro\Model\Indexer\PurchaseHistory;
use Amasty\RulesPro\Model\Indexer\PurchaseHistory\IndexStructure;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class for Data precessing from DB
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const ALL = 'all';
    public const TABLE_NAME = 'sales_order';

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        CacheInterface $cache = null // TODO: move to not optional argument and remove OM
    ) {
        $this->cache = $cache ?? ObjectManager::getInstance()->get(CacheInterface::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param int $customerId
     * @param array $conditions e.g. array( 0=> array('date'=>'>2013-12-04'),  1=>array('state'=>'>2013-12-04'))
     * @param string $conditionType "all"  or "any"
     *
     * @return array
     */
    public function getTotals($customerId, $conditions, $conditionType)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['o' => $this->getTable(self::TABLE_NAME)], [])
            ->where('o.customer_id = ?', $customerId);

        $map = [
            'date' => 'o.created_at',
            'state' => 'o.state',
            'status' => 'o.status',
        ];

        foreach ($conditions as $element) {
            $value = current($element);
            $field = $map[key($element)];
            $whereCond = $field . ' ' . $value;

            if ($conditionType == static::ALL) {
                $select->where($whereCond);
            } else {
                $select->orWhere($whereCond);
            }
        }

        $select->from(
            null,
            ['count' => 'COUNT(*)', 'amount' => 'SUM(o.base_grand_total)']
        );
        $row = $connection->fetchRow($select);

        return [
            'average_order_value' => $row['count'] ? $row['amount'] / $row['count'] : 0,
            'total_orders_amount' => $row['amount'],
            'of_placed_orders' => $row['count'],
        ];
    }

    /**
     * @param int $customerId
     * @param string $attribute
     *
     * @return float
     */
    public function getValidationData($customerId, $attribute)
    {
        $cacheKey = hash('sha256', $customerId . '_' . $attribute);
        $cacheData = $this->cache->load($cacheKey);
        if ($cacheData === false) {
            $connection = $this->getConnection();
            $columns = [];

            if ($attribute === 'order_num') {
                $columns = [IndexStructure::ORDERS_COUNT];
            } elseif ($attribute === 'sales_amount') {
                $columns = [IndexStructure::SUM_AMOUNT];
            }

            $select = $connection->select()
                ->from(['i' => $this->getTable(PurchaseHistory::INDEXER_ID)], $columns)
                ->where('i.customer_id = ?', $customerId);

            $result = (float)$connection->fetchOne($select);
            $this->cache->save($result, $cacheKey, [Cache::CACHE_TAG . $customerId], Cache::CACHE_LIFETIME);

            return $result;
        }

        return (float)$cacheData;
    }
}
