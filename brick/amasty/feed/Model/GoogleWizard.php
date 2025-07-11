<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GoogleWizard
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\Feed\Model\Category\Category
     */
    private $categoryMapper;

    /**
     * @var \Amasty\Feed\Model\Category\ResourceModel\MappingCollection
     */
    private $categoryMapperCollection;

    /**
     * @var \Amasty\Feed\Model\GoogleWizard\ElementFactory
     */
    private $googleWizardElementFactory;

    /**
     * @var \Amasty\Feed\Model\Feed
     */
    private $feed;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var \Amasty\Feed\Model\Schedule\Management
     */
    private $scheduleManagement;

    /**
     * @var Category\Repository
     */
    private $repository;

    /**
     * @var FeedRepository
     */
    private $feedRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session $session,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Feed\Model\Category\Category $categoryMapper,
        \Amasty\Feed\Model\Category\ResourceModel\Collection $categoryMapperCollection,
        \Amasty\Feed\Model\GoogleWizard\ElementFactory $googleWizardElementFactory,
        \Amasty\Feed\Model\Schedule\Management $scheduleManagement,
        \Amasty\Feed\Model\Category\Repository $repository,
        \Amasty\Feed\Model\FeedRepository $feedRepository
    ) {
        $this->serializer = $serializer;
        $this->session = $session;
        $this->categoryMapper = $categoryMapper;
        $this->categoryMapperCollection = $categoryMapperCollection;
        $this->storeManager = $storeManager;
        $this->googleWizardElementFactory = $googleWizardElementFactory;
        $this->scheduleManagement = $scheduleManagement;
        $this->repository = $repository;
        $this->feedRepository = $feedRepository;
    }

    /**
     * Get currency
     *
     * @retrun string
     */
    public function getCurrency()
    {
        $currency = null;
        $sessionData = $this->getSessionData();
        if (is_array($sessionData)
            && isset($sessionData['format_price_currency'])
        ) {
            $currency = $sessionData['format_price_currency'];
        }

        return $currency;
    }

    /**
     * Get store id
     *
     * @retrun string|int
     */
    public function getStoreId()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $sessionData = $this->getSessionData();
        if (is_array($sessionData) && isset($sessionData['store_id'])) {
            $storeId = $sessionData['store_id'];
        }

        return $storeId;
    }

    /**
     * Get attributes for basic tabs
     *
     * @retrun array
     */
    public function getBasicAttributes()
    {
        $attributes = [];

        foreach ($this->getAttributes() as $idx => $attribute) {
            if ($attribute->getRequired()) {
                $attributes[$idx] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get attributes for optional attributes
     *
     * @retrun array
     */
    public function getOptionalAttributes()
    {
        $attributes = [];

        foreach ($this->getAttributes() as $idx => $attribute) {
            if (!$attribute->getRequired()) {
                $attributes[$idx] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Create finally feed and category mapping
     *
     * @param array $requestData
     *
     * @return array
     */
    public function setup($requestData)
    {
        $config = [];

        if (!$this->getSessionData()) {
            $this->setSessionData($requestData);
        } else {
            $requestData = $this->updSessionData($requestData);
        }

        $this->setupCategories($requestData);
        $this->setupFeed($requestData);

        if ($this->categoryMapper && $this->categoryMapper->getId()) {
            $categoryMappingId = $this->categoryMapper->getId();
            $config[RegistryContainer::VAR_CATEGORY_MAPPER] = $categoryMappingId;
        }

        if ($this->feed && $this->feed->getId()) {
            $feedId = $this->feed->getId();
            $config[RegistryContainer::VAR_FEED] = $feedId;
        }

        return $config;
    }

    /**
     * Session Data is cleared
     *
     * @return $this
     */
    public function clearSessionData()
    {
        $this->setSessionData();

        return $this;
    }

    /**
     * Create category with category mapper data
     *
     * @param array $requestData
     *
     * @return void
     */
    protected function setupCategories($requestData)
    {
        if (isset($requestData['feed_category_id'])) {
            $feedCategoryId = (int)$requestData['feed_category_id'];
            try {
                $this->categoryMapper = $this->repository->getById($feedCategoryId);
            } catch (LocalizedException $e) {
                null;
            }
        }

        if (isset($requestData['mapping'])) {
            if (!isset($requestData['feed_category_id'])) {
                $this->categoryMapper->setTaxonomySource($requestData['taxonomy_source']);
                $this->createCategoryMapper();
            }

            $this->categoryMapper->setMapping($requestData['mapping']);
            //TODO
            $this->repository->save($this->categoryMapper);
        }
    }

    /**
     * Create feed with data
     *
     * @param array $requestData
     *
     * @return void
     */
    protected function setupFeed($requestData)
    {
        $this->feed = $this->feedRepository->getEmptyModel();
        if (isset($requestData['feed_id'])) {
            $feedId = (int)$requestData['feed_id'];
            try {
                $this->feed = $this->feedRepository->getById($feedId);
            } catch (NoSuchEntityException $exception) {
                $this->feed = $this->feedRepository->getEmptyModel();
            }
        }

        if (isset($requestData['filename'])) {
            if (!isset($requestData['feed_id'])) {
                $this->createFeed();
            }

            $this->modifyFeed($requestData);
        }
    }

    /**
     * Create category mapper without data
     *
     * @return void
     */
    protected function createCategoryMapper()
    {
        $codePrefix = 'google_category_';
        $idx = $this->categoryMapperCollection->addGoogleSetupFilter()->count()
            + 1;

        $this->categoryMapper->addData(
            [
                'code' => $codePrefix . $idx,
                'name' => 'Google Setup #' . $idx,
                'use_taxonomy' => 1,
            ]
        );
        $this->repository->save($this->categoryMapper);
    }

    /**
     * Create xml document
     *
     * @param array $requestData
     *
     * @return string
     */
    protected function createXml($requestData)
    {
        $xmlData = $this->prepareXml($requestData);

        return implode('', $xmlData);
    }

    /**
     * Create feed without data
     *
     * @return void
     */
    protected function createFeed()
    {
        $this->feed->setData(
            [
                'feed_type' => 'xml',
                'name' => 'Google Feed',
                'xml_header' => '<?xml version="1.0"?> <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'
                    . '<channel> <created_at>{{DATE}}</created_at>',
                'xml_footer' => '</channel> </rss>',
                'xml_item' => 'item',
                'format_date' => 'Y-m-d',
                'is_active' => 1,
                'is_template' => 0
            ]
        );
    }

    /**
     * Modify feed
     *
     * @param array $requestData
     *
     * @return void
     */
    protected function modifyFeed($requestData)
    {
        $this->feed->addData($requestData);

        if ($this->feed->getDeliveryType()) {
            $this->feed->setDeliveryEnabled(true);
        }

        if (isset($requestData['delivery_path'])) {
            $this->feed->setDeliveryPath($requestData['delivery_path']);
        }

        $this->feed->addData(
            [
                'xml_content' => $this->createXml($requestData)
            ]
        );

        $this->feedRepository->save($this->feed, true);

        $this->scheduleManagement->saveScheduleData($this->feed->getId(), $requestData);
    }

    /**
     * Set data in session
     *
     * @param array $requestData
     *
     * @return void
     */
    protected function setSessionData($requestData = [])
    {
        $serializedRequestData = $this->serializer->serialize($requestData);
        $this->session->setAmastyFeedGoogleRequestData($serializedRequestData);
    }

    /**
     * Get data from session
     *
     * @return array
     */
    protected function getSessionData()
    {
        $serializedData = $this->session->getAmastyFeedGoogleRequestData();

        return $this->serializer->unserialize($serializedData);
    }

    /**
     * Update data in session
     *
     * @param array $requestData
     *
     * @return array
     */
    protected function updSessionData($requestData = [])
    {
        $serializedData = $this->session->getAmastyFeedGoogleRequestData();
        $savedRequestData = $this->serializer->unserialize($serializedData);
        if ($savedRequestData) {
            if (isset($savedRequestData['mapping']) && !isset($requestData['mapping'])) {
                unset($savedRequestData['mapping']);
            }
            $requestData = array_merge($savedRequestData, $requestData);
            $serializedData = $this->serializer->serialize($requestData);
            $this->session->setAmastyFeedGoogleRequestData($serializedData);
        }

        return $requestData;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $config = [
            'id',
            'title',
            'description',
            'type',
            'link',
            'image',
            'condition',
            'price',
            'price_sale',
            'price_effectivedate',
            'brand',
            'color',
            'size',
            'gender',
            'tax',
            'gtin',
            'mpn',
        ];

        if (!$this->feed) {
            $this->feed = $this->feedRepository->getEmptyModel();
        }

        if (!$this->attributes) {
            $this->attributes = [];

            foreach ($config as $element) {
                $this->attributes[$element] = $this->loadAttribute($element);
            }

            $this->setAttributeData();
        }

        return $this->attributes;
    }

    /**
     * Load attribute of feed
     *
     * @return object
     */
    protected function loadAttribute($element)
    {
        $elementModel = $this->googleWizardElementFactory->create(
            [
                'elementType' => $element,
                'feed' => $this->feed
            ]
        );

        return $elementModel->init($element);
    }

    /**
     * Set attribute data
     *
     * @return void
     */
    protected function setAttributeData()
    {
        $sessionData = $this->getSessionData();
        if (is_array($sessionData)) {
            $attributesData = [];

            if (isset($sessionData['basic'])) {
                $attributesData = array_merge(
                    $attributesData,
                    $sessionData['basic']
                );
            }

            if (isset($sessionData['optional'])) {
                $attributesData = array_merge(
                    $attributesData,
                    $sessionData['optional']
                );
            }

            foreach ($attributesData as $code => $element) {
                if (isset($this->attributes[$code])) {
                    $this->attributes[$code]->reloadData($element);
                }
            }
        }
    }

    /**
     * Check using identifier exists in xml document
     *
     * @return boolean
     */
    protected function canUseIdentifierExists(array $requestData = [])
    {
        $canUseIdentifierExists = false;
        if (isset($requestData['optional'])) {
            foreach ($requestData['optional'] as $code => $attribute) {
                $value = (!empty($attribute) && isset($attribute['attribute'])) ? $attribute['attribute'] : false;
                if (($code == 'mpn' || $code == 'gtin') && $value) {
                    $canUseIdentifierExists = true;
                }
            }
        }

        return $canUseIdentifierExists;
    }

    /**
     * @param $requestData
     *
     * @return array
     */
    private function prepareXml($requestData)
    {
        $xmlData = [];

        if (isset($requestData['basic'])) {
            foreach ($requestData['basic'] as $code => $config) {
                $attribute = $this->loadAttribute($code);
                $xmlData[] = $attribute->evaluate($config);
            }
        }

        $xmlData[] = $this->loadAttribute('shipping')->evaluate();
        $xmlData[] = $this->loadAttribute('availability')->evaluate();

        if ($this->categoryMapper->getId()) {
            $categoryAttr = $this->loadAttribute('category');
            $categoryAttr->setValue($this->categoryMapper->getCode());
            $xmlData[] = $categoryAttr->evaluate();
        }

        if ($this->canUseIdentifierExists($requestData)) {
            $identifierexistsAttr = $this->loadAttribute('identifierexists');
            $xmlData[] = $identifierexistsAttr->evaluate();
        } else {
            $xmlData[] = $this->loadAttribute('noidentifierexists')->evaluate();
        }

        if (isset($requestData['optional'])) {
            foreach ($requestData['optional'] as $code => $config) {
                $attribute = $this->loadAttribute($code);
                $row = $attribute->evaluate($config);
                if ($row) {
                    $xmlData[] = $row;
                }
            }
        }

        $attribute = $this->loadAttribute('image_additional');
        for ($idx = 1; $idx <= RegistryContainer::MAX_ADDITIONAL_IMAGES; $idx++) {
            $attribute->setImageIdx($idx);
            $xmlData[] = $attribute->evaluate();
        }

        return $xmlData;
    }
}
