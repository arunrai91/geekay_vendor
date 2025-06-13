<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceData;

use Amasty\Base\Api\Data\InstanceDataInterface;
use Amasty\Base\Api\Data\InstanceDataInterfaceFactory;
use Amasty\Base\Api\InstanceDataRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements InstanceDataRepositoryInterface
{
    /**
     * @var ResourceModel\InstanceData
     */
    private $instanceDataResource;

    /**
     * @var InstanceDataInterfaceFactory
     */
    private $instanceDataFactory;

    public function __construct(
        ResourceModel\InstanceData $instanceDataResource,
        InstanceDataInterfaceFactory $instanceDataFactory
    ) {
        $this->instanceDataResource = $instanceDataResource;
        $this->instanceDataFactory = $instanceDataFactory;
    }

    public function get(string $code): InstanceDataInterface
    {
        $instanceData = $this->instanceDataFactory->create();
        $this->instanceDataResource->load($instanceData, $code, InstanceDataInterface::CODE);
        if (!$instanceData->getId()) {
            throw new NoSuchEntityException(__('Instance Data with code "%1" not found.', $code));
        }

        return $instanceData;
    }

    public function save(InstanceDataInterface $instanceData): void
    {
        try {
            try {
                /** @var InstanceData $instanceDataSaved */
                $instanceDataSaved = $this->get($instanceData->getCode());
                $instanceDataSaved->addData($instanceData->getData());
                $instanceData = $instanceDataSaved;
                //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (NoSuchEntityException $e) {
            }
            $this->instanceDataResource->save($instanceData);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Instance Data. Error: %1', $e->getMessage()));
        }
    }

    public function delete(string $code): void
    {
        try {
            $instanceData = $this->get($code);
            $this->instanceDataResource->delete($instanceData);
            //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete Instance Data with code %1. Error: "%2"', $code, $e->getMessage())
            );
        }
    }
}
