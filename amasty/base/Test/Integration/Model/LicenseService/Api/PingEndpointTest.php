<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Integration\Model\LicenseService\Api;

use Amasty\Base\Model\SysInfo\Command\LicenceService\GetCurrentLicenseValidation;
use Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo;
use Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo\ChangedData\Persistor;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea crontab
 */
class PingEndpointTest extends AbstractEndpointTest
{
    /**
     * @var GetCurrentLicenseValidation
     */
    private $getCurrentLicense;

    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getCurrentLicense = $this->objectManager->get(GetCurrentLicenseValidation::class);
        $this->registeredInstanceRepository = $this->objectManager->get(RegisteredInstanceRepository::class);
    }

    /**
     * @magentoConfigFixture default_store amasty_base/instance_registration/is_production 0
     * @magentoConfigFixture default_store amasty_base/instance_registration/keys {"item1":{"license_key":"test_key"}}
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     */
    public function testValidResponse(): void
    {
        $response = json_encode($this->readResponseFile('valid_ping_response.json'));
        $this->mockResponse($response);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertTrue($licenseValidation->isNeedCheckLicense());
        $this->assertEquals('success', $licenseValidation->getMessages()[0]->getType());
        $this->assertNotEmpty($licenseValidation->getModules());
        $this->assertNotEmpty($licenseValidation->getInstanceKeys());
    }

    /**
     * @magentoConfigFixture default_store amasty_base/instance_registration/is_production 0
     * @magentoConfigFixture default_store amasty_base/instance_registration/keys {"item1":{"license_key":"test_key"}}
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     */
    public function testErrorResponse(): void
    {
        $response = json_encode($this->readResponseFile('invalid_ping_response.json'));
        $this->mockResponse($response, 400);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertTrue($licenseValidation->isNeedCheckLicense());
        $this->assertEquals('error', $licenseValidation->getMessages()[0]->getType());
        $this->assertEmpty($licenseValidation->getModules());
        $this->assertEmpty($licenseValidation->getInstanceKeys());
    }

    /**
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     */
    public function testNoNeedCheckLicense(): void
    {
        $response = json_encode(["isNeedCheckLicense" => false]);
        $this->mockResponse($response);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertFalse($licenseValidation->isNeedCheckLicense());
    }

    /**
     * @magentoConfigFixture default_store amasty_base/instance_registration/is_production 0
     * @magentoConfigFixture default_store amasty_base/instance_registration/keys {"item1":{"license_key":"test_key"}}
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/valid_response_exists.php
     */
    public function testUnavailableError(): void
    {
        $response = json_encode([]);
        $this->mockResponse($response, 504);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertTrue($licenseValidation->isNeedCheckLicense());
        $this->assertEquals('error', $licenseValidation->getMessages()[0]->getType());
    }

    /**
     * @magentoConfigFixture default_store amasty_base/instance_registration/is_production 0
     * @magentoConfigFixture default_store amasty_base/instance_registration/keys {"item1":{"license_key":"test_key"}}
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     */
    public function testUnavailableFirstRequest(): void
    {
        $response = json_encode([]);
        $this->mockResponse($response, 504);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertFalse($licenseValidation->isNeedCheckLicense());
    }

    /**
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_domain_changed.php
     */
    public function testPingDomainChanged(): void
    {
        $response = json_encode([]);
        $this->mockResponse($response);

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertEmpty($licenseValidation->getData());
    }

    /**
     * @magentoConfigFixture default_store amasty_base/instance_registration/is_production 0
     * @magentoConfigFixture default_store amasty_base/instance_registration/keys {"item1":{"license_key":"test_key"}}
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/system_instance_key_exists.php
     * @magentoDataFixture Amasty_Base::Test/Integration/_files/valid_response_exists.php
     */
    public function testPingNeedToReRegister(): void
    {
        $response = json_encode($this->readResponseFile('valid_ping_response_re_registration.json'));
        $responseRegistration = json_encode(['systemInstanceKey' => 'test-key2']);
        $this->mockResponse([$response, $responseRegistration]);
        $this->disableCollect(); //to send ping request

        $sendSysInfo = $this->objectManager->get(SendSysInfo::class);
        $sendSysInfo->execute();

        $licenseValidation = $this->getCurrentLicense->get();
        $this->assertCount(1, $licenseValidation->getData());

        $currentInstance = $this->registeredInstanceRepository->get()->getCurrentInstance();
        $this->assertEquals('test-key2', $currentInstance->getSystemInstanceKey());
    }

    private function disableCollect(): void
    {
        $dataPersistorMock = $this->createMock(Persistor::class);
        $this->objectManager->configure(
            [
                SendSysInfo::class => [
                    'arguments' => [
                        'changedDataPersistor' => [
                            'instance' => get_class($dataPersistorMock),
                            'shared' => true
                        ],
                    ]
                ]
            ]
        );
    }
}
