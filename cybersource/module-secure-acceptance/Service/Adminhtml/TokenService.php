<?php

namespace CyberSource\SecureAcceptance\Service\Adminhtml;

use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;
use Magento\Quote\Model\QuoteRepository;
use CyberSource\Core\Model\LoggerInterface;
use CyberSource\SecureAcceptance\Gateway\Config\Config;

class TokenService
{
    const COMMAND_CODE = 'generate_flex_key';

    private $commandManager;
    private $urlDecoder;
    private $backendQuoteSession;
    private $quoteRepository;
    private $logger;
    private $config;

    public function __construct(
        CommandManagerInterface $commandManager,
        DecoderInterface $urlDecoder,
        BackendQuoteSession $backendQuoteSession,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->commandManager = $commandManager;
        $this->urlDecoder = $urlDecoder;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function generateToken()
    {
        $quote =  $this->backendQuoteSession->getQuote();

        if (!$quote || !$quote->getId()) {
            $this->logger->warning('Cart is empty or unable to load cart data.');
            return;
        }
            if($this->config->isMicroform()){
            $commandResult = $this->commandManager->executeByCode(
                self::COMMAND_CODE,
                $quote->getPayment()
            );
            $commandResult = $commandResult->get();
            $this->quoteRepository->save($quote);

            $captureContextValue = $commandResult['response'];
            $decodedCaptureResponse = json_decode($this->urlDecoder->decode(explode('.', $captureContextValue)[1]));

            $ctxData = $decodedCaptureResponse->ctx[0]->data ?? null;
            if ($ctxData) {
                $quoteExtension = $quote->getExtensionAttributes();
                if (!$quoteExtension) {
                    $quoteExtension = $this->quoteRepository->create();
                }
                if (!$quoteExtension->getClientLibraryIntegrity()) {
                    $quoteExtension->setClientLibraryIntegrity($ctxData->clientLibraryIntegrity ?? null);
                }
                if (!$quoteExtension->getClientLibrary()) {
                    $quoteExtension->setClientLibrary($ctxData->clientLibrary ?? null);
                }
                if (!$quoteExtension->getClientLibraryIntegrity()) {
                    $quoteExtension->setClientLibraryIntegrity($ctxData->clientLibraryIntegrity ?? null);
                }
                $quote->setExtensionAttributes($quoteExtension);
                $this->quoteRepository->save($quote);
            }
        }else{
            return true;
        }
    }
}
