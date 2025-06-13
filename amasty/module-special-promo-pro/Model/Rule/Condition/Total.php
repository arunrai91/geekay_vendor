<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Rule\Condition;

use Amasty\Base\Model\Serializer;
use Amasty\RulesPro\Model\Cache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Total Condition(s).
 */
class Total extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var array
     */
    private $passedItems = [];

    /**
     * @var \Amasty\RulesPro\Helper\Calculator
     */
    private $calculator;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Amasty\RulesPro\Helper\Calculator $calculator,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = [],
        ?CacheInterface $cache = null, // TODO: move to not optional argument and remove OM
        ?Serializer $serializer = null // TODO: move to not optional argument and remove OM
    ) {
        $this->calculator = $calculator;
        $this->cache = $cache ?? ObjectManager::getInstance()->get(CacheInterface::class);
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Serializer::class);

        parent::__construct($context, $data); //DO NOT TOUCH POSITION OF PARENT CALL !!!

        $this->setType(Total::class)->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     *
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);

        return $this;
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     *
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );

        return $xml;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'average_order_value' => __('Average Order Value'),
                'total_orders_amount' => __('Total Sales Amount'),
                'of_placed_orders' => __('Number of Placed Orders'),
            ]
        );

        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );

        return $this;
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [
            [
                'label' => __('Please choose condition'),
                'value' => ''
            ],
            [
                'label' => __('Order State'),
                'value' => \Amasty\RulesPro\Model\Rule\Condition\Total\Status::class
            ],
            [
                'label' => __('Period after order was placed'),
                'value' => \Amasty\RulesPro\Model\Rule\Condition\Total\Period::class
            ],
        ];

        return $conditions;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $quote = $model->getQuote();
        }

        // order history conditions are valid for customers only, not for visitors.
        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $condArray = [];
        $dataForHash = [];

        foreach ($this->getConditions() as $condObj) {
            if (!in_array($condObj->getId(), $this->passedItems)) {
                $this->passedItems[] = $condObj->getId();
                $condArray[] = $condObj->validate($model);
            }
            $dataForHash[] = ['attribute' => $condObj->getAttribute(), 'value' => $condObj->getValue()];
        }

        if (empty($condArray)) {
            return $this->validateAttribute($model->getData($this->getAttribute()));
        }
        $fieldName = $this->getAttributeElement()->getValue();
        $dataForHash['customer'] = $customerId;
        $dataForHash['filedName'] = $fieldName;
        $dataForHash['operator'] = $this->getOperator();
        $dataForHash['value'] = $this->getValue();

        $cacheKey = hash('sha256', $this->serializer->serialize($dataForHash));
        $cacheData = $this->cache->load($cacheKey);
        if ($cacheData === false) {
            $attribute
                = $this->calculator->getSingleTotalField($customerId, $fieldName, $condArray, $this->getAggregator());

            $result = $this->validateAttribute($attribute);

            $this->cache->save((int)$result, $cacheKey, [Cache::CACHE_TAG . $customerId], Cache::CACHE_LIFETIME);

            return $result;
        }

        return (bool)$cacheData;
    }
}
