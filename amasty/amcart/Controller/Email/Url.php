<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Email;

use Amasty\Acart\Api\BlacklistRepositoryInterface;
use Amasty\Acart\Api\Data\BlacklistInterfaceFactory;
use Amasty\Acart\Api\QuoteManagementInterface;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Amasty\Acart\Controller\Email;
use Amasty\Acart\Model\App\Response\Redirect;
use Amasty\Acart\Model\ConfigProvider;
use Amasty\Acart\Model\History;
use Amasty\Acart\Model\ResourceModel\History\CollectionFactory;
use Amasty\Acart\Model\ResourceModel\RuleQuote as RuleQuoteResource;
use Amasty\Acart\Model\RuleQuote;
use Amasty\Acart\Model\SectionsData\FlushSectionsInterface;
use Amasty\Acart\Model\UrlManager;
use Magento\Checkout\Model\SessionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteFactory;

class Url extends Email
{
    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var FlushSectionsInterface
     */
    private $flushSections;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var BlacklistRepositoryInterface
     */
    protected $blacklistRepository;

    /**
     * @var BlacklistInterfaceFactory
     */
    protected $blacklistFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    public function __construct(
        Context $context,
        UrlManager $urlManager,
        RuleQuote $ruleQuote,
        RuleQuoteResource $ruleQuoteResource,
        CollectionFactory $historyCollectionFactory,
        CustomerSession $customerSession,
        SessionFactory $checkoutSessionFactory,
        CustomerFactory $customerFactory,
        QuoteFactory $quoteFactory,
        Redirect $redirect,
        FlushSectionsInterface $flushSections,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        BlacklistRepositoryInterface $blacklistRepository,
        BlacklistInterfaceFactory $blacklistFactory,
        ConfigProvider $configProvider,
        QuoteManagementInterface $quoteManagement
    ) {
        parent::__construct(
            $context,
            $urlManager,
            $ruleQuote,
            $ruleQuoteResource,
            $historyCollectionFactory,
            $customerSession,
            $checkoutSessionFactory,
            $customerFactory,
            $quoteFactory
        );

        $this->redirect = $redirect;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->blacklistFactory = $blacklistFactory;
        $this->configProvider = $configProvider;
        $this->quoteManagement = $quoteManagement;
        $this->flushSections = $flushSections;
    }

    protected function getHistory(): ?History
    {
        $key = urlencode($this->getRequest()->getParam('key'));

        $historyResource = $this->historyCollectionFactory->create();
        $historyResource->addRuleQuoteData()
            ->addFieldToFilter('main_table.public_key', $key)
            ->setCurPage(1)
            ->setPageSize(1);

        $history = $historyResource->getFirstItem();

        if (!$history->getHistoryId() || $history->getPublicKey() != $key) {
            return null;
        }

        return $history;
    }

    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        $mageUrl = $this->getRequest()->getParam('mageUrl');

        $history = $this->getHistory();

        if (!$history || (!$url && !$mageUrl)) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

            return $result->forward('edit');
        }
        $this->urlManager->init($history->getRule(), $history);
        $target = null;

        if ($url) {
            // @codingStandardsIgnoreLine
            $target = $this->urlManager->getCleanUrl(base64_decode(urldecode($url)));
        } elseif ($mageUrl) {
            $target = $this->_url->getUrl(
                // @codingStandardsIgnoreLine
                $this->urlManager->getCleanUrl(base64_decode(urldecode($mageUrl))),
                $this->urlManager->getUtmParams()
            );
        }

        $this->loginCustomer($history);
        $ruleQuote = $this->ruleQuoteRepository->getById((int)$history->getRuleQuoteId());

        $ruleQuote->clickByLink($history);

        $ruleQuote->setAbandonedStatus(RuleQuote::ABANDONED_RESTORED_STATUS);

        $this->ruleQuoteRepository->save($ruleQuote);

        return $this->resultRedirectFactory->create()->setUrl($this->redirect->validateRedirectUrl($target));
    }

    protected function loginCustomer(History $history): void
    {
        $checkoutSession = $this->checkoutSessionFactory->create();
        $sectionsToReload[] = 'cart';

        if ($this->customerSession->isLoggedIn()) {
            if ($history->getCustomerId() != $this->customerSession->getCustomerId()) {
                $this->customerSession->logout();
            }
        }

        // customer. login
        if ($history->getCustomerId()) {
            $customer = $this->customerFactory->create()->load($history->getCustomerId());

            if ($customer->getId()) {
                if ($this->isAutoLoginAllowed($history->getFinishedAt())) {
                    $this->customerSession->setCustomerAsLoggedIn($customer);
                    $checkoutSession->setCustomerData($customer->getDataModel());
                    $sectionsToReload[] = 'customer';
                } elseif (!$this->customerSession->isLoggedIn() && $history->getQuoteId()) {
                    try {
                        $quote = $this->quoteManagement->cloneCustomerQuoteToGuest(
                            (int)$customer->getId(),
                            (int)$history->getQuoteId(),
                            $checkoutSession->getQuoteId() ? (int)$checkoutSession->getQuoteId() : null
                        );
                    } catch (\Exception $e) {
                        $quote = null;
                    }

                    if ($quote) {
                        $checkoutSession->replaceQuote($quote);
                    }
                }
            }
        } elseif ($history->getQuoteId()) {
            /**
             * visitor. restore quote in the session
             */
            $quote = $this->quoteFactory->create()->load($history->getQuoteId());

            if ($quote) {
                $checkoutSession->replaceQuote($quote);
                $quote->getBillingAddress()->setEmail($history->getCustomerEmail());
            }
        }

        $this->flushSections->execute($checkoutSession, $sectionsToReload);

        if ($history->getSalesRuleCoupon()) {
            $code = $history->getSalesRuleCoupon();
            $quote = $checkoutSession->getQuote();

            if ($code && $quote) {
                $quote->setCouponCode($code)
                    ->collectTotals()
                    ->save();
            }
        }
    }

    private function isAutoLoginAllowed(?string $finishTime): bool
    {
        $isAllowed = false;
        if ($this->configProvider->isAutoLoginEnabled() && $finishTime) {
            $lifetimePeriod = $this->configProvider->getAutoLoginLifetime();
            $lifetime = strtotime('-' . $lifetimePeriod . ' days');
            $finishTime = strtotime($finishTime);
            $isAllowed = $lifetime < $finishTime;
        }

        return $isAllowed;
    }
}
