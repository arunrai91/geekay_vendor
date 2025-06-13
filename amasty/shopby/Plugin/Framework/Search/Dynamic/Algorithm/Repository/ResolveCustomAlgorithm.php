<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Search\Dynamic\Algorithm\Repository;

use Amasty\Shopby\Model\Search\Dynamic\Custom;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository as AlgorithmRepository;

class ResolveCustomAlgorithm
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param AlgorithmRepository $subject
     * @param callable $proceed
     * @param string $algorithmType
     * @param array $data
     * @return AlgorithmInterface
     *
     * @see AlgorithmRepository::get
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGet(AlgorithmRepository $subject, callable $proceed, $algorithmType, array $data = [])
    {
        if ($algorithmType === Custom::ALGORITHM_CODE) {
            return $this->objectManager->create(Custom::class, $data);
        }

        return $proceed($algorithmType, $data);
    }
}
