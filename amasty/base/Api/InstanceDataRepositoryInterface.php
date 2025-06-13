<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface InstanceDataRepositoryInterface
{
    /**
     * @param string $code
     * @return \Amasty\Base\Api\Data\InstanceDataInterface
     * @throws NoSuchEntityException
     */
    public function get(string $code): Data\InstanceDataInterface;

    /**
     * @param \Amasty\Base\Api\Data\InstanceDataInterface $instanceData
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(Data\InstanceDataInterface $instanceData): void;

    /**
     * @param string $code
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(string $code): void;
}
