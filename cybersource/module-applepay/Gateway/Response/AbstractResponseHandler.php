<?php

namespace CyberSource\ApplePay\Gateway\Response;

use CyberSource\ApplePay\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractResponseHandler
{
    const REQUEST_ID = "requestID";
    const REASON_CODE = "reasonCode";
    const DECISION = "decision";
    const MERCHANT_REFERENCE_CODE = "merchantReferenceCode";
    const REQUEST_TOKEN = "requestToken";
    const RECONCILIATION_ID = "reconciliationID";
    const CALL_ID = "callID";
    const CURRENCY = "currency";
    const AMOUNT = "amount";
    const AUTHORIZATION_CODE = "authorizationCode";

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractResponseHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return \Magento\Payment\Model\InfoInterface
     */
    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }

    protected function handleAuthorizeResponse($payment, $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->unsAdditionalInformation();

        $payment->setTransactionId($response[self::REQUEST_ID]);
        $payment->setCcTransId($response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::REQUEST_ID, $response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::CURRENCY, $response['purchaseTotals']->{self::CURRENCY});
        $payment->setAdditionalInformation(self::AMOUNT, $response['ccAuthReply']->{self::AMOUNT});
        $payment->setAdditionalInformation(self::REQUEST_TOKEN, $response[self::REQUEST_TOKEN]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_CODE, $response[self::MERCHANT_REFERENCE_CODE]);
        $payment->setAdditionalInformation(self::AUTHORIZATION_CODE, $response['ccAuthReply']->{self::AUTHORIZATION_CODE});
        #Arunendra Code   
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/handleAuthorizeResponse.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($response, true));
        try{
            $payment->setAdditionalInformation('transaction_id', $response[self::REQUEST_ID]);
            $cardNumber = $response['token']->prefix.'xxxxxx'.$response['token']->suffix;
            $cc_last_4 = $response['token']->suffix;
            $cardType = $response['card']->cardType;
            $payment->setAdditionalInformation('cardNumber',$cardNumber);
            $payment->setData('cc_last_4',$cc_last_4);
            $payment->setAdditionalInformation('cardType',$cardType);
            $payment->setAdditionalInformation('auth_amount',$response['ccAuthReply']->amount); 
            if($response['ccAuthReply'] && !empty($response['ccAuthReply']->cavvResponseCode) ){
                $payment->setAdditionalInformation('cavvResponseCode',$response['ccAuthReply']->cavvResponseCode);   
            }
            #https://3dmerchant.com/blog/terminology/cavv-authentication-verification-value
            if($cardType=='001' && in_array($response['ccAuthReply']->cavvResponseCode,['2','3','8','A'])){
                $payment->setAdditionalInformation('payer_authentication_eci',05);
            }else if($cardType=='002'){
                $payment->setAdditionalInformation('payer_authentication_eci',02);
            }
        }catch(\Exception $e){
           $logger->info($e->getMessage());
        }

        return $payment;
    }
}
