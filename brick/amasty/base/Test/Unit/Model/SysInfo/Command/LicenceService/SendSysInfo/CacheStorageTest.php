<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Unit\Model\SysInfo\Command\LicenceService\SendSysInfo;

use Amasty\Base\Model\InstanceHash\InstanceHash;
use Amasty\Base\Model\InstanceHash\InstanceHashFactory;
use Amasty\Base\Model\InstanceHash\Repository;
use Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo\CacheStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheStorageTest extends TestCase
{
    /**
     * @var CacheStorage
     */
    private $model;

    /**
     * @var Repository|MockObject
     */
    private $instanceHashRepositoryMock;

    /**
     * @var InstanceHashFactory|MockObject
     */
    private $instanceHashFactoryMock;

    protected function setUp(): void
    {
        $this->instanceHashRepositoryMock = $this->createMock(Repository::class);
        $this->instanceHashFactoryMock = $this->createMock(InstanceHashFactory::class);

        $this->model = new CacheStorage(
            null,
            $this->instanceHashRepositoryMock,
            $this->instanceHashFactoryMock
        );
    }

    /**
     * @param string $identifier
     * @param string|null $expected
     * @dataProvider getDataProvider
     * @return void
     */
    public function testGet(string $identifier, ?string $expected): void
    {
        $instanceHashMock = $this->createConfiguredMock(InstanceHash::class, ['getValue' => $expected]);
        $this->instanceHashRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($identifier)
            ->willReturn($instanceHashMock);

        $this->assertEquals($expected, $this->model->get($identifier));
    }

    public function getDataProvider(): array
    {
        return [
            ['identifier1', 'val1'],
            ['identifier2', null]
        ];
    }

    /**
     * @param string $identifier
     * @param string $value
     * @dataProvider setDataProvider
     * @return void
     */
    public function testSet(string $identifier, string $value): void
    {
        $instanceHashMock = $this->createPartialMock(InstanceHash::class, []);
        $this->instanceHashFactoryMock->method('create')->willReturn($instanceHashMock);
        $this->instanceHashRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($instanceHashMock);

        $this->assertTrue($this->model->set($identifier, $value));
    }

    public function setDataProvider(): array
    {
        return [
            ['identifier1', 'val1']
        ];
    }
}
