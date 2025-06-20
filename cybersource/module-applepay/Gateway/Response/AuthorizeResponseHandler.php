<?php
/**
 * Copyright © 2018 CollinsHarper. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\ApplePay\Gateway\Response;

use CyberSource\ApplePay\Gateway\Helper\SubjectReader;
use CyberSource\ApplePay\Gateway\Http\Client\SOAPClient;
use CyberSource\ApplePay\Gateway\Http\TransferFactory;
use CyberSource\ApplePay\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class AuthorizeResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var SOAPClient
     */
    private $soapClient;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * AuthorizeResponseHandler constructor.
     * @param RequestDataBuilder $requestDataBuilder
     * @param SOAPClient $SOAPClient
     * @param TransferFactory $transferFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        RequestDataBuilder $requestDataBuilder,
        SOAPClient $SOAPClient,
        TransferFactory $transferFactory,
        SubjectReader $subjectReader
    ) {
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $SOAPClient;
        $this->transferFactory = $transferFactory;

        parent::__construct($subjectReader);
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $this->getValidPaymentInstance($handlingSubject);
        $payment = $this->handleAuthorizeResponse($payment, $response);

        $payment->setIsTransactionClosed(false);
		
		if ($response[self::REASON_CODE] === 480) {
            $payment->setIsTransactionPending(true);
        } 

        $payment->setAdditionalInformation(
            self::RECONCILIATION_ID,
            $response['ccAuthReply']->{self::RECONCILIATION_ID} ?? null
        );
    }
}
