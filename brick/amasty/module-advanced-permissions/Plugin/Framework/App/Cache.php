<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Framework\App;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Framework\App\CacheInterface;

class Cache
{
    public const ATTRIBUTE_METADATA_CACHE_PREFIX = 'ATTRIBUTE_METADATA_INSTANCES_CACHE';

    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $ruleHelper;

    /**
     * Cache constructor.
     * @param \Amasty\Rolepermissions\Helper\Data\Proxy $ruleHelper
     */
    public function __construct(\Amasty\Rolepermissions\Helper\Data $ruleHelper)
    {
        $this->ruleHelper = $ruleHelper;
    }

    /**
     * @param CacheInterface $subject
     * @param string $identifier
     * @return string
     */
    public function beforeLoad(CacheInterface $subject, $identifier)
    {
        if (defined('Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories::CATEGORY_TREE_ID')) {
            if (false !== strpos($identifier, Categories::CATEGORY_TREE_ID)) {
                $rule = $this->ruleHelper->currentRule();
                if ($rule) {
                    $identifier = $identifier . '_' . $rule->getRoleId();
                }
            }
        }

        if ($identifier == self::ATTRIBUTE_METADATA_CACHE_PREFIX . 'customerall') {
            $rule = $this->ruleHelper->currentRule();
            if ($rule) {
                $identifier = $identifier . '_' . $rule->getRoleId();
            }
        }

        return [$identifier];
    }

    /**
     * @param CacheInterface $subject
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param null $lifeTime
     * @return array
     */
    public function beforeSave(CacheInterface $subject, $data, $identifier, $tags = [], $lifeTime = null)
    {
        if (defined('Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories::CATEGORY_TREE_ID')) {
            if (false !== strpos($identifier, Categories::CATEGORY_TREE_ID)) {
                $rule = $this->ruleHelper->currentRule();
                if ($rule) {
                    $identifier = $identifier . '_' . $rule->getRoleId();
                }
            }
        }

        if ($identifier == self::ATTRIBUTE_METADATA_CACHE_PREFIX . 'customerall') {
            $rule = $this->ruleHelper->currentRule();
            if ($rule) {
                $identifier = $identifier . '_' . $rule->getRoleId();
            }
        }

        return [$data, $identifier, $tags, $lifeTime];
    }
}
