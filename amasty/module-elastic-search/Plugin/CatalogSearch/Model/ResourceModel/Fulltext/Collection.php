<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\CatalogSearch\Model\ResourceModel\Fulltext;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextCollection;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Psr\Log\LoggerInterface;

class Collection
{
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MessageManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return array|mixed
     */
    public function aroundGetFacetedData($subject, \Closure $proceed, $argument)
    {
        try {
            $result = $proceed($argument);
        } catch (StateException $exception) {
            $this->messageManager->addErrorMessage(__('Sorry, search engine is currently unavailable'));
            $this->logger->error($exception->getMessage());
            $result = [];
        }

        return $result;
    }
}
