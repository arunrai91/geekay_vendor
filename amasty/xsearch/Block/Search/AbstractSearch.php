<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Amasty\Xsearch\Controller\RegistryConstants;
use Amasty\Xsearch\Helper\Data;
use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\Search\SearchAdapterResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\DataObject;

abstract class AbstractSearch extends Template
{
    public const DEFAULT_CACHE_TAG = 'amasty_xsearch_popup';
    public const DEFAULT_CACHE_LIFETIME = 86400;

    /**
     * @var \Zend\ServiceManager\FactoryInterface
     */
    private $searchCollection;

    /**
     * @var \Magento\Search\Model\Query
     */
    private $query;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Data
     */
    protected $xSearchHelper;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var StringUtils
     */
    protected $stringUtils;

    /**
     * @var Config
     */
    protected $configProvider;

    /**
     * @var SearchAdapterResolver
     */
    private $searchAdapterResolver;

    public function __construct(
        Context $context,
        Data $xSearchHelper,
        StringUtils $string,
        QueryFactory $queryFactory,
        Registry $coreRegistry,
        Config $configProvider,
        SearchAdapterResolver $searchAdapterResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->xSearchHelper = $xSearchHelper;
        $this->stringUtils = $string;
        $this->queryFactory = $queryFactory;
        $this->coreRegistry = $coreRegistry;
        $this->configProvider = $configProvider;
        $this->searchAdapterResolver = $searchAdapterResolver;
        $this->setData('cache_lifetime', self::DEFAULT_CACHE_LIFETIME);
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();

        return array_merge(
            [$this->getQuery()->getQueryText(), self::DEFAULT_CACHE_TAG . '_' . $this->getBlockType()],
            $cacheKey
        );
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::DEFAULT_CACHE_TAG, self::DEFAULT_CACHE_TAG . '_' . $this->getBlockType()];
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_template = 'Amasty_Xsearch::search/items.phtml';

        parent::_construct();
    }

    /**
     * @return string
     */
    abstract public function getBlockType();

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws LocalizedException
     */
    protected function generateCollection()
    {
        if (!is_object($this->getData('collectionFactory'))) {
            throw new LocalizedException(__('Undefined collection factory'));
        }

        $collection = $this->getData('collectionFactory')->create();

        return $collection;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection | array
     */
    public function getSearchCollection()
    {
        if ($this->searchCollection === null) {
            try {
                $this->searchCollection = $this->generateCollection();
            } catch (LocalizedException $exception) {
                $this->searchCollection = [];
            }
        }

        return $this->searchCollection;
    }

    /**
     * @return array[]
     */
    public function getResults()
    {
        $query = $this->getQuery();
        $result = $query ? $this->searchAdapterResolver->getResults($this->getBlockType(), $query) : null;

        if ($result && $result->getItems()) {
            $this->setNumResults($result->getResultsCount());
            $searchResult = $result->getItems();
        } else {
            $searchResult = $this->getCollectionData();
        }

        return $searchResult;
    }

    private function getCollectionData(): array
    {
        foreach ($this->getSearchCollection() as $index => $item) {
            $result[$index] = $this->getItemData($item);
        }

        return $result ?? [];
    }

    /**
     * @param DataObject $item
     * @return array
     */
    public function getItemData(DataObject $item): array
    {
        $data['name'] = $this->getName($item);
        $data['description'] = $this->getDescription($item);
        $data['url'] = $this->getRelativeLink($this->getSearchUrl($item));
        $data['title'] = $this->getItemTitle($item);

        return $data;
    }

    /**
     * @return array[]
     */
    public function getIndexFulltextValues()
    {
        return $this->getSearchCollection()->getIndexFulltextValues();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/title');
    }

    /**
     * @return string
     */
    public function getLimit()
    {
        if ($this->getData('limit') === null) {
            $limit = (int)$this->xSearchHelper->getModuleConfig($this->getBlockType() . '/limit');
            $this->setData('limit', max(1, $limit));
        }

        return $this->getData('limit');
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getName());
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getDescription(\Magento\Framework\DataObject $item)
    {
        return '';
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemTitle(\Magento\Framework\DataObject $item)
    {
        return $this->getName($item);
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateName($name)
    {
        $text = $this->stripTags($name, null, true);
        $text = $this->formatAccordingToConstraint($text);

        return $this->highlight($text);
    }

    /**
     * @param string $text
     * @return string
     */
    public function formatAccordingToConstraint(string $text): string
    {
        $nameLength = $this->getNameLength();

        if ($nameLength && $this->stringUtils->strlen($text) > $nameLength) {
            $text = $this->stringUtils->substr($text, 0, $nameLength) . '...';
        }

        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    public function highlight($text)
    {
        if (trim($this->getQuery()->getQueryText())) {
            $text = $this->xSearchHelper->highlight($text, $this->getQuery()->getQueryText());
        }

        return $text;
    }

    /**
     * @return \Magento\Search\Model\QueryInterface
     */
    public function getQuery()
    {
        if (null === $this->query) {
            $this->query = $this->coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY)
                ? $this->coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY)
                : $this->queryFactory->get();
            $engine = $this->xSearchHelper->getCurrentSearchEngineCode();
            $this->query = $this->xSearchHelper->setStrippedQueryText($this->query, $engine);
        }

        return $this->query;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        return $item->getUrl() ? : $this->xSearchHelper->getResultUrl($item->getQueryText());
    }

    /**
     * @param array $item
     * @return bool
     */
    public function showDescription(array $item)
    {
        return $this->stringUtils->strlen($item['description']) > 0 && $this->getDescLength() > 0;
    }

    /**
     * @return string
     */
    public function getNameLength()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/name_length');
    }

    /**
     * @return string
     */
    public function getDescLength()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/desc_length');
    }

    /**
     * @param $currentHtml
     */
    protected function replaceVariables(&$currentHtml)
    {
        $currentHtml = preg_replace('@\{{(.+?)\}}@', '', $currentHtml);
    }

    /**
     * @param $descStripped
     * @param bool $descLength
     * @return string
     */
    public function getHighlightText($descStripped)
    {
        $text = $this->stringUtils->strlen($descStripped) > $this->getDescLength()
            ? $this->stringUtils->substr($descStripped, 0, $this->getDescLength()) . '...'
            : $descStripped;

        return $this->highlight($text);
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getRelativeLink($url)
    {
        $store = $this->_storeManager->getStore();
        return str_replace(
            [
                $store->getBaseUrl(),
                $store->getBaseUrl('link', false),
                $store->getBaseUrl(UrlInterface::URL_TYPE_WEB),
                $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false)
            ],
            '',
            $url
        );
    }

    /**
     * @param string $url
     * @return string
     */
    public function getFullLink($url)
    {
        $url = $this->getRelativeLink($url);

        return $this->_storeManager->getStore()->getBaseUrl() . ltrim($url, '/');
    }

    /**
     * @return bool
     */
    public function isNoFollow()
    {
        return false;
    }
}
