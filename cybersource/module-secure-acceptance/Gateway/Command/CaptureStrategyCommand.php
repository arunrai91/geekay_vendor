<?php

namespace CyberSource\SecureAcceptance\Gateway\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Class CaptureStrategyCommand
 * @SuppressWarnings(PHPMD)
 */
class CaptureStrategyCommand implements CommandInterface
{

    const SALE = 'sale';
    const CAPTURE = 'settlement';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;
	
	/**
     * @var boolean
     */
	private $isZeroAmountCapture = false;

    /**
     * CaptureStrategyCommand constructor.
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
		
		/* throw localize exception in case of zero */
		$amount = $this->subjectReader->readAmount($commandSubject);
		if ($amount <= 0) {
			$this->isZeroAmountCapture = true;
		}
		
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     * @param OrderPaymentInterface $payment
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment)
    {
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        $exitsAuthorization = $payment->getAuthorizationTransaction();
        if (!$exitsAuthorization && !$existsCapture) {
            return self::SALE;
        }
		
		if($this->isZeroAmountCapture){
			throw new \Magento\Framework\Exception\LocalizedException(__('Amount should be greater than zero!'));
		}

        return self::CAPTURE;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean) $count;
    }
}
