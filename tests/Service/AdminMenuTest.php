<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Service\AdminMenu;

/**
 * AdminMenu服务测试
 *
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试基类要求实现此方法
    }

    public function testInvokeCreatesWechatMenuIfNotExists(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->expects(self::once())
            ->method('getCurdListPage')
            ->with(MassTask::class)
            ->willReturn('/admin/mass-task')
        ;

        $rootItem = $this->createMock(ItemInterface::class);
        $wechatMenuItem = $this->createMock(ItemInterface::class);

        $rootItem->expects(self::exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturnOnConsecutiveCalls(null, $wechatMenuItem)
        ;

        $rootItem->expects(self::once())
            ->method('addChild')
            ->with('微信公众号')
            ->willReturn($wechatMenuItem)
        ;

        $massTaskMenuItem = $this->createMock(ItemInterface::class);
        $massTaskMenuItem->expects(self::once())
            ->method('setUri')
            ->with('/admin/mass-task')
            ->willReturnSelf()
        ;
        $massTaskMenuItem->expects(self::once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-bullhorn')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects(self::once())
            ->method('addChild')
            ->with('群发任务')
            ->willReturn($massTaskMenuItem)
        ;

        // 在容器中设置Mock的依赖
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }

    public function testInvokeUsesExistingWechatMenu(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->expects(self::once())
            ->method('getCurdListPage')
            ->with(MassTask::class)
            ->willReturn('/admin/mass-task')
        ;

        $rootItem = $this->createMock(ItemInterface::class);
        $wechatMenuItem = $this->createMock(ItemInterface::class);

        $rootItem->expects(self::exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturn($wechatMenuItem)
        ;

        $rootItem->expects(self::never())
            ->method('addChild')
        ;

        $massTaskMenuItem = $this->createMock(ItemInterface::class);
        $massTaskMenuItem->expects(self::once())
            ->method('setUri')
            ->with('/admin/mass-task')
            ->willReturnSelf()
        ;
        $massTaskMenuItem->expects(self::once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-bullhorn')
            ->willReturnSelf()
        ;

        $wechatMenuItem->expects(self::once())
            ->method('addChild')
            ->with('群发任务')
            ->willReturn($massTaskMenuItem)
        ;

        // 在容器中设置Mock的依赖
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }

    public function testInvokeHandlesNullWechatMenu(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        $rootItem = $this->createMock(ItemInterface::class);

        $rootItem->expects(self::exactly(2))
            ->method('getChild')
            ->with('微信公众号')
            ->willReturnOnConsecutiveCalls(null, null)
        ;

        $rootItem->expects(self::once())
            ->method('addChild')
            ->with('微信公众号')
            ->willReturn($this->createMock(ItemInterface::class))
        ;

        // 在容器中设置Mock的依赖
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }
}
