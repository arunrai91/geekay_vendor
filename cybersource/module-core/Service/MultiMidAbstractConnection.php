<?php
/**
 * Copyright © 2021 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\Core\Service;

use CyberSource\Core\Model\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use CyberSource\Core\Model\DoRequest;

abstract class MultiMidAbstractConnection
{
    const XML_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const IS_TEST_MODE_CONFIG_PATH = 'payment/chcybersource/use_test_wsdl';
    const TEST_WSDL_PATH           = 'payment/chcybersource/path_to_test_wsdl';
    const LIVE_WSDL_PATH           = 'payment/chcybersource/path_to_wsdl';

    const MERCHANT_ID_PATH         = 'payment/chcybersource/merchant_id';
    const P12_CERTIFICATE = 'payment/chcybersource/general_p12_certificate';
    const P12_ACCESSKEY     = 'payment/chcybersource/p12_accesskey';


    /**
     * @var string
     */
    private $wsdl = null;

    /**
     * @var string
     */
    private $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * @var string
     */
    public $p12_certificate = null;

    /**
     * @var string
     */
    public $p12_accesskey = null;

    /**
     * @var ScopeConfigInterface
     */
    public $config;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var \SoapClient $client
     */
    public $client;
      /**
     * @var string
     */
    public $merchantId = null;



    /**
     * AbstractConnection constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @throws \Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        $merchantId,
        $p12_certificate,
        $p12_accesskey
    ) {
        $this->config = $scopeConfig;
        $this->logger = $logger;
        $this->merchantId = $merchantId;
        $this->p12_certificate = $p12_certificate;
        $this->p12_accesskey = $p12_accesskey;
        $this->handleWsdlEnvironment();
        $this->setUpCredentials();
        $this->initSoapClient();
    }

    /**
     * Initialize SOAP Client
     *
     * @return \SoapClient
     * @throws \Exception
     */
    public function initSoapClient()
    {

        try {
            if ($this->wsdl !== null) {
                // Get the full path to the certificate file
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
                // Get the upload directory
                $uploadDir = $filesystem->getDirectoryWrite($directory::ROOT)->getAbsolutePath('certificate/');
                $filename = $this->p12_certificate; 
                $p12_accesskey = $this->p12_accesskey; 
                $certificateDir = $directory->getRoot() . "/certificate";
                $sslOptions = [
                    'SSL' => [
                        'KEY_ALIAS' => 'cybersource',
                        'KEY_FILE' => $filename,
                        'KEY_PASS' => $p12_accesskey,
                        'KEY_DIRECTORY' => $certificateDir,
                        'CONNECTION_TIMEOUT' => '6000'
                    ]
                ];
                $soapClient = new DoRequest($this->logger, $this->wsdl, $sslOptions);
                $this->client = $soapClient;

                return $this->client;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
        return null;
    }

    public function setSoapClient(\SoapClient $client)
    {
        $this->client = $client;
    }

    public function getSoapClient()
    {
        return $this->client;
    }

    /**
     * Handle WSDL Environment to use correct webservice based on environment config
     */
    private function handleWsdlEnvironment()
    {
        $isTestMode = $this->config->getValue(
            self::IS_TEST_MODE_CONFIG_PATH,
            $this->storeScope
        );

        if ($isTestMode) {
            $this->wsdl = $this->config->getValue(
                self::TEST_WSDL_PATH,
                $this->storeScope
            );
        } else {
            $this->wsdl = $this->config->getValue(
                self::LIVE_WSDL_PATH,
                $this->storeScope
            );
        }
    }

    /**
     * Setup Credentials for webservice
     */
    private function setUpCredentials()
    {
        if(empty($this->merchantId)){
            $this->p12_certificate = $this->config->getValue(
                self::P12_CERTIFICATE,
                $this->storeScope,
                $this->getCurrentStoreId()
            );
 
            $this->p12_accesskey = $this->config->getValue(
                self::P12_ACCESSKEY,
                $this->storeScope,
                $this->getCurrentStoreId()
            );
            $this->merchantId = $this->config->getValue(
                self::MERCHANT_ID_PATH,
                $this->storeScope,
                $this->getCurrentStoreId()
            );
        }
    }


    /**
     * get merchant id
     *
     * @return $merchantId
     */
	public function getMid()
    {
		return $this->merchantId;
	}

	/**
	* get the current storeId
	* \Magento\Framework\App\ObjectManager $objectManager
	* \Magento\Store\Model\StoreManagerInterface $storeManager
	* @return $storeId
	*/
	public function getCurrentStoreId()
    {
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeId = $storeManager->getStore()->getStoreId();
		return $storeId;
	}


}