<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Controller\Adminhtml\Config\Checker;

use Amasty\ElasticSearch\Exception\Missing404Exception;
use Amasty\ElasticSearch\Model\Client\ClientFactory;
use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Amasty\ElasticSearch\Model\Config;
use Amasty\ElasticSearch\Model\GetAmastySearchEngines;
use Amasty\ElasticSearch\Model\Source\CustomAnalyzer;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\StripTags;

class Connection extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_ElasticSearch::config';

    public const EXCEPTION_PATTERN = '/unknown analyzer/i';

    public const IS_READ_ONLY_INDEX = 'amasty_is_read_only_flag';

    public const CUSTOM_ANALYZER_TEST_INDEX_NAME = 'amasty_custom_analyzer_test_index';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var GetAmastySearchEngines
     */
    private $getAmastySearchEngines;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function __construct(
        Context $context,
        Config $config,
        JsonFactory $resultJsonFactory,
        StripTags $tagFilter,
        GetAmastySearchEngines $getAmastySearchEngines,
        ClientFactory $clientFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
        $this->config = $config;
        $this->getAmastySearchEngines = $getAmastySearchEngines;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['engine'])
                || !in_array($options['engine'], $this->getAmastySearchEngines->execute(), true)
            ) {
                throw new LocalizedException(
                    __('Test connection can be applied only for Amasty Elastic Search engine.')
                );
            }

            $connectionData = $this->config->prepareConnectionData($options);
            $client = $this->clientFactory->create($options['engine'], $connectionData);
            $pingResult = $client->ping();

            if ($pingResult) {
                $indexPrefix = $connectionData['index'] ?? $this->config->getIndexPrefix();
                $this->checkIsReadOnly($client, $indexPrefix);

                if (isset($options['customAnalyzer'])
                    && $options['customAnalyzer'] != CustomAnalyzer::DISABLED
                ) {
                    $customAnalyzerIndexName = $indexPrefix . '_' . self::CUSTOM_ANALYZER_TEST_INDEX_NAME;
                    $client->createIndex(
                        $customAnalyzerIndexName,
                        [
                            'analysis' => [
                                'analyzer' => [
                                    'default' => ['type' => $options['customAnalyzer']]
                                ]
                            ]
                        ]
                    );

                    try {
                        $client->deleteIndex($customAnalyzerIndexName);
                    } catch (Missing404Exception $e) {
                        ;// do nothing
                    }
                }
                $result['success'] = $pingResult;
            } else {
                $result['errorMessage'] = __('It is impossible to perform the search.
                    Please make sure Elasticsearch Engine is installed on the server and configured properly.');
            }

            // @codingStandardsIgnoreLine
        } catch (LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            if (preg_match(self::EXCEPTION_PATTERN, $e->getMessage())) {
                $result['errorMessage'] = __('To use custom analyzer you have to install matching plugin');
            } else {
                $result['errorMessage'] = $this->tagFilter->filter(__($e->getMessage()));
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }

    private function checkIsReadOnly(ClientInterface $client, string $indexPrefix)
    {
        $testIndexName = $indexPrefix . '_' . self::IS_READ_ONLY_INDEX;

        try {
            $client->createIndex($testIndexName, []);
            $client->deleteIndex($testIndexName);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Please check the state of read_only_allow_delete setting in Elasticsearch server configuration.')
            );
        }
    }
}
