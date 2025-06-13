<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Setup\Patch\Data;

use Amasty\Base\Api\Data\InstanceHashInterfaceFactory;
use Amasty\Base\Api\InstanceHashRepositoryInterface;
use Amasty\Base\Model\FlagRepository;
use Amasty\Base\Model\SysInfo\Provider\CollectorPool;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class MoveInstanceHashFlagData implements DataPatchInterface
{
    private const OLD_PREFIX = 'amasty_base_';

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var FlagRepository
     */
    private $flagRepository;

    /**
     * @var CollectorPool
     */
    private $collectorPool;

    /**
     * @var InstanceHashRepositoryInterface
     */
    private $instanceHashRepository;

    /**
     * @var InstanceHashInterfaceFactory
     */
    private $instanceHashFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FlagResource $flagResource,
        FlagRepository $flagRepository,
        CollectorPool $collectorPool,
        InstanceHashRepositoryInterface $instanceHashRepository,
        InstanceHashInterfaceFactory $instanceHashFactory,
        LoggerInterface $logger
    ) {
        $this->flagResource = $flagResource;
        $this->flagRepository = $flagRepository;
        $this->collectorPool = $collectorPool;
        $this->instanceHashRepository = $instanceHashRepository;
        $this->instanceHashFactory = $instanceHashFactory;
        $this->logger = $logger;
    }

    public function apply(): self
    {
        foreach (array_keys($this->collectorPool->get(CollectorPool::LICENCE_SERVICE_GROUP)) as $code) {
            $flagData = $this->flagRepository->get(self::OLD_PREFIX . $code);
            if ($flagData) {
                $this->moveFlagData($code, $flagData);
            }
        }
        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function moveFlagData(string $code, string $value): void
    {
        $instanceData = $this->instanceHashFactory->create();
        $instanceData->setCode($code);
        $instanceData->setValue($value);
        try {
            $this->instanceHashRepository->save($instanceData);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return;
        }

        $this->flagResource->getConnection()->delete(
            $this->flagResource->getMainTable(),
            ['flag_code = ?' => self::OLD_PREFIX . $code]
        );
    }
}
