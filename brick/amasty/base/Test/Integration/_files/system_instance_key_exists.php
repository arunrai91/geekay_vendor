<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

use Amasty\Base\Model\InstanceData\ResourceModel\InstanceData;
use Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey\Converter;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$instanceDataResource = $objectManager->get(InstanceData::class);
$instanceDataResource->getConnection()->delete($instanceDataResource->getMainTable());

/** @var RegisteredInstanceRepository $registeredInstancesRepo */
$registeredInstancesRepo = $objectManager->get(RegisteredInstanceRepository::class);
/** @var Converter $converter */
$converter = $objectManager->get(Converter::class);

$instance = $converter->convertArrayToInstance([
    'domain' => parse_url(
        $objectManager->get(UrlInterface::class)->getBaseUrl(['_scope' => Store::DEFAULT_STORE_ID]),
        PHP_URL_HOST
    ),
    'system_instance_key' => 'test-key'
]);

$registeredInstance = $registeredInstancesRepo->get();
$registeredInstance->setCurrentInstance($instance);
$registeredInstance->setInstances([$instance]);

$registeredInstancesRepo->save($registeredInstance);
