<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Dynamic\Algorithm\Repository as DynamicAlgorithmRepository;
use Magento\Framework\Search\Dynamic\Algorithm;

/**
 * For temporary use. Untill all buckets will be evaluated in 1 request @todo
 */
class Repository
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Repository constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Reinitialize shared instance. In order to get correct aggregations for a price when it has current value applied
     *
     * @param DynamicAlgorithmRepository $subject
     * @param mixed $result
     * @param $algorithmType
     * @param array $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterGet(DynamicAlgorithmRepository $subject, $result, $algorithmType, array $data = [])
    {
        if ($algorithmType == 'auto') {
            return $this->objectManager->create(Algorithm\Auto::class, $data);
        } elseif ($algorithmType == 'manual') {
            return $this->objectManager->create(Algorithm\Manual::class, $data);
        } elseif ($algorithmType == 'improved') {
            return $this->objectManager->create(Algorithm\Improved::class, $data);
        }

        return $result;
    }
}
