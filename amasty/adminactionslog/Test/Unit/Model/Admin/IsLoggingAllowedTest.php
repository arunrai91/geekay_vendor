<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Test\Unit\Model\Admin;

use Amasty\AdminActionsLog\Model\Admin\IsLoggingAllowed;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\AdminActionsLog\Model\Admin\IsLoggingAllowed
 */
class IsLoggingAllowedTest extends TestCase
{
    /**
     * @var IsLoggingAllowed
     */
    private $isLoggingAllowed;

    /**
     * @var Session|MockObject
     */
    private $authSession;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configProvider;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->authSession = $this->createPartialMock(
            Session::class,
            ['isLoggedIn', '__call']
        );
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $this->isLoggingAllowed = $objectManager->getObject(
            IsLoggingAllowed::class,
            [
                'authSession' => $this->authSession,
                'configProvider' => $this->configProvider
            ]
        );
    }

    public function testExecuteNotLogged()
    {
        $this->authSession->expects($this->once())->method('isLoggedIn')->willReturn(false);

        $this->assertFalse($this->isLoggingAllowed->execute());
    }

    public function testExecuteWrongAdmin()
    {
        $this->authSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->authSession->expects($this->any())->method('__call')->with(...['getUser'])->willReturn(
            $this->createConfiguredMock(User::class, ['getId' => 'test_admin2'])
        );
        $this->configProvider->expects($this->any())->method('isEnabledLogAllAdmins')->willReturn(false);
        $this->configProvider->expects($this->any())->method('getAdminUsers')->willReturn(['test_admin1']);

        $this->assertFalse($this->isLoggingAllowed->execute());
    }

    public function testExecuteRightAdmin()
    {
        $this->authSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->authSession->expects($this->any())->method('__call')->with(...['getUser'])->willReturn(
            $this->createConfiguredMock(User::class, ['getId' => 'test_admin1'])
        );
        $this->configProvider->expects($this->any())->method('isEnabledLogAllAdmins')->willReturn(false);
        $this->configProvider->expects($this->any())->method('getAdminUsers')->willReturn(['test_admin1']);

        $this->assertTrue($this->isLoggingAllowed->execute());
    }
}
