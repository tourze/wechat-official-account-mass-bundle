<?php

namespace WechatOfficialAccountMassBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatOfficialAccountMassBundle\DependencyInjection\WechatOfficialAccountMassExtension;

class WechatOfficialAccountMassExtensionTest extends TestCase
{
    public function testLoadServicesConfiguration(): void
    {
        $container = new ContainerBuilder();
        $extension = new WechatOfficialAccountMassExtension();
        
        $extension->load([], $container);
        
        // 验证服务是否被正确加载
        $this->assertTrue($container->hasDefinition('WechatOfficialAccountMassBundle\Command\SendMassCommand'));
        $this->assertTrue($container->hasDefinition('WechatOfficialAccountMassBundle\Repository\MassTaskRepository'));
    }
}