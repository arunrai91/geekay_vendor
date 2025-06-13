<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Unit\Model\SysInfo;

use Amasty\Base\Model\FlagRepository;
use Amasty\Base\Model\InstanceData\InstanceData;
use Amasty\Base\Model\InstanceData\InstanceDataFactory;
use Amasty\Base\Model\InstanceData\Repository;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstance;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstance\Instance;
use Amasty\Base\Model\SysInfo\Data\RegisteredInstanceFactory;
use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegisteredInstanceRepositoryTest extends TestCase
{
    /**
     * @var RegisteredInstanceRepository
     */
    private $model;

    /**
     * @var FlagRepository|MockObject
     */
    private $flagRepositoryMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var RegisteredInstanceFactory|MockObject
     */
    private $registeredInstanceFactoryMock;

    /**
     * @var Repository|MockObject
     */
    private $instanceDataRepositoryMock;

    /**
     * @var InstanceDataFactory|MockObject
     */
    private $instanceDataFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    protected function setUp(): void
    {
        $this->flagRepositoryMock = $this->createMock(FlagRepository::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->registeredInstanceFactoryMock = $this->createMock(RegisteredInstanceFactory::class);
        $this->instanceDataRepositoryMock = $this->createMock(Repository::class);
        $this->instanceDataFactoryMock = $this->createMock(InstanceDataFactory::class);
        $this->urlMock = $this->createMock(UrlInterface::class);

        $this->model = new RegisteredInstanceRepository(
            $this->flagRepositoryMock,
            $this->serializerMock,
            $this->dataObjectHelperMock,
            $this->registeredInstanceFactoryMock,
            $this->instanceDataRepositoryMock,
            $this->instanceDataFactoryMock,
            $this->urlMock
        );
    }

    /**
     * @param string $regInstSerialized
     * @param array $regInstArray
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGet(string $regInstSerialized, array $regInstArray): void
    {
        $this->urlMock->method('getBaseUrl')->willReturn('https://test.com');
        $currentInstance = $this->createConfiguredMock(Instance::class, ['getDomain' => 'test.com']);
        $registeredInstanceMock = $this->createMock(RegisteredInstance::class);
        $registeredInstanceMock->method('getCurrentInstance')->willReturn($currentInstance);
        $this->registeredInstanceFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($registeredInstanceMock);
        $this->prepareGetDataMock($registeredInstanceMock, $regInstSerialized, $regInstArray);

        $this->assertEquals($registeredInstanceMock, $this->model->get());
    }

    public function testGetDomainChanged(): void
    {
        $this->urlMock->method('getBaseUrl')->willReturn('https://test2.com');
        $currentInstance = $this->createConfiguredMock(Instance::class, ['getDomain' => 'test.com']);
        $registeredInstanceMock = $this->createMock(RegisteredInstance::class);
        $emptyInstanceMock = $this->createMock(RegisteredInstance::class);
        $registeredInstanceMock->method('getCurrentInstance')->willReturn($currentInstance);
        $this->registeredInstanceFactoryMock
            ->method('create')
            ->willReturn($registeredInstanceMock, $emptyInstanceMock);
        $this->prepareGetDataMock($registeredInstanceMock, 'val', ['val']);

        $this->assertEquals($emptyInstanceMock->getCurrentInstance(), $this->model->get()->getCurrentInstance());
    }

    public function getDataProvider(): array
    {
        return [
            ['', []],
            ['val', ['val']]
        ];
    }

    private function prepareGetDataMock(
        RegisteredInstance $registeredInstanceMock,
        string $regInstSerialized,
        array $regInstArray
    ): void {
        $instanceDataMock = $this->createConfiguredMock(
            InstanceData::class,
            ['getCode' => RegisteredInstanceRepository::REGISTERED_INSTANCE, 'getValue' => $regInstSerialized]
        );
        $this->instanceDataRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(RegisteredInstanceRepository::REGISTERED_INSTANCE)
            ->willReturn($instanceDataMock);
        if ($regInstSerialized) {
            $this->serializerMock
                ->expects($this->once())
                ->method('unserialize')
                ->with($regInstSerialized)
                ->willReturn($regInstArray);
        }
        $this->dataObjectHelperMock
            ->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $registeredInstanceMock,
                $regInstArray,
                RegisteredInstance::class
            );
    }

    public function testSave(): void
    {
        $regInstArray = [];
        $regInstSerialized = '';
        $registeredInstanceMock = $this->createMock(RegisteredInstance::class);
        $registeredInstanceMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn($regInstArray);
        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($regInstArray)
            ->willReturn($regInstSerialized);
        $instanceDataMock = $this->createPartialMock(InstanceData::class, []);
        $this->instanceDataFactoryMock->method('create')->willReturn($instanceDataMock);

        $this->instanceDataRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($instanceDataMock);
        $this->model->save($registeredInstanceMock);
    }
}
