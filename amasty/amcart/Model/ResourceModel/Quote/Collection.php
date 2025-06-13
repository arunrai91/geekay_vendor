<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\ResourceModel\Quote;

use Amasty\Acart\Model\ConfigProvider;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Quote\Model\ResourceModel\Quote\Collection
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        ConfigProvider $configProvider,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->configProvider = $configProvider;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
    }

    /**
     * @return $this
     */
    public function addAbandonedCartsFilter()
    {
        $this->addFieldToFilter('main_table.is_active', ['eq' => 1])
            ->getSelect()->joinLeft(
                ['ruleQuote' => $this->getTable('amasty_acart_rule_quote')],
                'main_table.entity_id = ruleQuote.quote_id and ruleQuote.test_mode <> 1',
                []
            )->where('main_table.items_count > 0');

        if ($this->configProvider->isSendOnetime()) {
            $this->getSelect()->where('ruleQuote.rule_quote_id IS NULL');
        } else {
            $this->getSelect()
                ->group('main_table.entity_id')
                ->having(
                    sprintf(
                        'MAX(%s) IS NULL OR MAX(%s) < MAX(%s)',
                        'ruleQuote.rule_quote_id',
                        'ruleQuote.created_at',
                        'main_table.updated_at'
                    )
                );
        }

        return $this;
    }

    /**
     * @param bool $debug
     * @param array $permittedDomains
     *
     * @return $this
     */
    public function joinQuoteEmail($debug = false, $permittedDomains = [])
    {
        $this->getSelect()->joinLeft(
            ['quoteEmail' => $this->getTable('amasty_acart_quote_email')],
            'main_table.entity_id = quoteEmail.quote_id',
            ['acart_quote_email' => 'customer_email']
        );

        $emailColumn = $this->getConnection()->getIfNullSql('main_table.customer_email', 'quoteEmail.customer_email');

        if ($debug && count($permittedDomains)) {
            $emailCondition = [];
            foreach ($permittedDomains as $domain) {
                $emailCondition[] = ['like' => '%@' . $domain . '%'];
            }

            $this->addFieldToFilter(
                $emailColumn,
                $emailCondition
            );
        }

        $this->addFieldToFilter($emailColumn, ['notnull' => true]);

        $this->getSelect()
            ->columns($emailColumn . ' as target_email');

        return $this;
    }

    /**
     * @param string $currentExecution
     * @param string $firstExecution
     * @param int $limit
     *
     * @return self
     */
    public function addTimeFilter(string $currentExecution, string $firstExecution, int $limit = 200): self
    {
        $timeDiff = $this->getDbTimeDiff();
        $currentExecution = $this->correctDate($currentExecution, $timeDiff);
        $firstExecution = $this->correctDate($firstExecution, $timeDiff);

        $dateColumn = $this->getConnection()->getIfNullSql('main_table.updated_at', 'main_table.created_at');
        $this->addFieldToFilter($dateColumn, ['gteq' => $firstExecution])
            ->addFieldToFilter($dateColumn, ['lt' => $currentExecution])
            ->setOrder($dateColumn, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->getSelect()->limit($limit);

        return $this;
    }

    /**
     * Correct UTC time if database has different timezone set
     */
    private function correctDate(string $date, string $timeDiff): string
    {
        if (!($interval = ltrim(strtok($timeDiff, ':'), '-0'))) {
            return $date;
        }

        $method = strpos($timeDiff, '-') === false ? 'add' : 'sub';
        $interval = new \DateInterval("PT{$interval}H");

        return (string)$this->getConnection()->formatDate((new \DateTime($date))->$method($interval));
    }

    private function getDbTimeDiff(): string
    {
        return $this->getConnection()->query(
            $this->getConnection()->quoteInto(
                'SELECT ? as diff',
                new \Zend_Db_Expr('TIMEDIFF(NOW(), UTC_TIMESTAMP)')
            )
        )->fetch(\Zend_Db::FETCH_COLUMN);
    }
}
