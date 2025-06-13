<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Amasty\Acart\Api\Data\HistoryExtensionInterface;
use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class History extends AbstractExtensibleModel implements HistoryInterface
{
    public const HISTORY_ID = 'history_id';
    public const RULE_QUOTE_ID = 'rule_quote_id';
    public const SCHEDULE_ID = 'schedule_id';
    public const STATUS = 'status';
    public const EMAIL_SUBJECT = 'email_subject';
    public const EMAIL_BODY = 'email_body';
    public const PUBLIC_KEY = 'public_key';
    public const SALES_RULE_ID = 'sales_rule_id';
    public const SALES_RULE_COUPON_ID = 'sales_rule_coupon_id';
    public const SALES_RULE_COUPON = 'sales_rule_coupon';
    public const SCHEDULED_AT = 'scheduled_at';
    public const EXECUTED_AT = 'executed_at';
    public const FINISHED_AT = 'finished_at';
    public const OPENED_COUNT = 'opened';
    public const SALES_RULE_COUPON_EXPIRATION_DATE = 'sales_rule_coupon_expiration_date';
    public const HISTORY_DETAILS = 'history_details';

    public const STATUS_PROCESSING = 'processing';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCEL_EVENT = 'cancel_event';
    public const STATUS_BLACKLIST = 'blacklist';
    public const STATUS_ADMIN = 'admin';
    public const STATUS_NOT_NEWSLETTER_SUBSCRIBER = 'not_newsletter_subscriber';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_acart_history';

    /**
     * @var string
     */
    protected $_eventObject = 'history';

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->storeManager = $storeManager;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\History::class);
        $this->setIdFieldName(self::HISTORY_ID);
    }

    public function getHistoryId(): ?int
    {
        return $this->_getData(self::HISTORY_ID);
    }

    public function setHistoryId(?int $historyId): HistoryInterface
    {
        $this->setData(self::HISTORY_ID, $historyId);

        return $this;
    }

    public function getRuleQuoteId(): ?int
    {
        return $this->hasData(self::RULE_QUOTE_ID) ? (int)$this->_getData(self::RULE_QUOTE_ID) : null;
    }

    public function setRuleQuoteId($ruleQuoteId): HistoryInterface
    {
        $this->setData(self::RULE_QUOTE_ID, $ruleQuoteId);

        return $this;
    }

    public function getScheduleId(): ?int
    {
        return $this->hasData(self::SCHEDULE_ID) ? (int)$this->_getData(self::SCHEDULE_ID) : null;
    }

    public function setScheduleId($scheduleId): HistoryInterface
    {
        $this->setData(self::SCHEDULE_ID, $scheduleId);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->_getData(self::STATUS);
    }

    public function setStatus(?string $status): HistoryInterface
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->_getData(self::PUBLIC_KEY);
    }

    public function setPublicKey(?string $publicKey): HistoryInterface
    {
        $this->setData(self::PUBLIC_KEY, $publicKey);

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->_getData(self::EMAIL_SUBJECT);
    }

    public function setEmailSubject(?string $emailSubject): HistoryInterface
    {
        $this->setData(self::EMAIL_SUBJECT, $emailSubject);

        return $this;
    }

    public function getEmailBody(): string
    {
        return $this->_getData(self::EMAIL_BODY) ?? '';
    }

    public function setEmailBody(?string $emailBody): HistoryInterface
    {
        $this->setData(self::EMAIL_BODY, $emailBody);

        return $this;
    }

    public function getSalesRuleId(): ?string
    {
        return $this->_getData(self::SALES_RULE_ID);
    }

    public function setSalesRuleId($salesRuleId): HistoryInterface
    {
        $this->setData(self::SALES_RULE_ID, $salesRuleId);

        return $this;
    }

    public function getSalesRuleCouponId(): ?int
    {
        return $this->_getData(self::SALES_RULE_COUPON_ID);
    }

    public function setSalesRuleCouponId(?int $salesRuleCouponId): HistoryInterface
    {
        $this->setData(self::SALES_RULE_COUPON_ID, $salesRuleCouponId);

        return $this;
    }

    public function getSalesRuleCoupon(): ?string
    {
        return $this->_getData(self::SALES_RULE_COUPON);
    }

    public function setSalesRuleCoupon(?string $salesRuleCoupon): HistoryInterface
    {
        $this->setData(self::SALES_RULE_COUPON, $salesRuleCoupon);

        return $this;
    }

    public function getScheduledAt(): ?string
    {
        return $this->_getData(self::SCHEDULED_AT);
    }

    public function setScheduledAt(?string $scheduledAt): HistoryInterface
    {
        $this->setData(self::SCHEDULED_AT, $scheduledAt);

        return $this;
    }

    public function getExecutedAt(): ?string
    {
        return $this->_getData(self::EXECUTED_AT);
    }

    public function setExecutedAt(?string $executedAt): HistoryInterface
    {
        $this->setData(self::EXECUTED_AT, $executedAt);

        return $this;
    }

    public function getFinishedAt(): ?string
    {
        return $this->_getData(self::FINISHED_AT);
    }

    public function setFinishedAt(?string $finishedAt): HistoryInterface
    {
        $this->setData(self::FINISHED_AT, $finishedAt);

        return $this;
    }

    public function getOpenedCount(): int
    {
        return (int)$this->_getData(self::OPENED_COUNT);
    }

    public function setOpenedCount(int $count): HistoryInterface
    {
        $this->setData(self::OPENED_COUNT, $count);

        return $this;
    }

    public function getSalesRuleCouponExpirationDate(): ?string
    {
        return $this->_getData(self::SALES_RULE_COUPON_EXPIRATION_DATE);
    }

    public function setSalesRuleCouponExpirationDate(?string $salesRuleCouponExpirationDate): HistoryInterface
    {
        $this->setData(self::SALES_RULE_COUPON_EXPIRATION_DATE, $salesRuleCouponExpirationDate);

        return $this;
    }

    public function setHistoryDetails(array $historyDetails): HistoryInterface
    {
        $this->setData(self::HISTORY_DETAILS, $historyDetails);

        return $this;
    }

    public function getHistoryDetails(): array
    {
        return (array)$this->_getData(self::HISTORY_DETAILS);
    }

    public function getExtensionAttributes(): ?HistoryExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(HistoryExtensionInterface $extensionAttributes): HistoryInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return StoreInterface
     */
    public function getStore($storeId = null): StoreInterface
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }

        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->ruleQuoteRepository->getById((int)$this->getRuleQuoteId())->getRule();
    }
}
