<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceHash;

use Amasty\Base\Api\Data\InstanceHashInterface;
use Amasty\Base\Api\Data\InstanceHashInterfaceFactory;
use Amasty\Base\Api\InstanceHashRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements InstanceHashRepositoryInterface
{
    /**
     * @var ResourceModel\InstanceHash
     */
    private $instanceHashResource;

    /**
     * @var InstanceDataInterfaceFactory
     */
    private $instanceHashFactory;

    public function __construct(
        ResourceModel\InstanceHash $instanceHashResource,
        InstanceHashInterfaceFactory $instanceHashFactory
    ) {
        $this->instanceHashResource = $instanceHashResource;
        $this->instanceHashFactory = $instanceHashFactory;
    }

    public function get(string $code): InstanceHashInterface
    {
        $instanceData = $this->instanceHashFactory->create();
        $this->instanceHashResource->load($instanceData, $code, InstanceHashInterface::CODE);
        if (!$instanceData->getId()) {
            throw new NoSuchEntityException(__('Instance Hash Data with code "%1" not found.', $code));
        }

        return $instanceData;
    }

    public function save(InstanceHashInterface $instanceHash): void
    {
        try {
            try {
                /** @var InstanceHash $instanceHashSaved */
                $instanceHashSaved = $this->get($instanceHash->getCode());
                $instanceHashSaved->addData($instanceHash->getData());
                $instanceHash = $instanceHashSaved;
                //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (NoSuchEntityException $e) {
            }
            $this->instanceHashResource->save($instanceHash);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Instance Data. Error: %1', $e->getMessage()));
        }
    }

    public function delete(string $code): void
    {
        try {
            $instanceHash = $this->get($code);
            $this->instanceHashResource->delete($instanceHash);
            //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete Instance Hash with code %1. Error: "%2"', $code, $e->getMessage())
            );
        }
    }
}
