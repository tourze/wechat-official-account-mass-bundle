<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMassBundle\WechatOfficialAccountMassBundle;

final class WechatOfficialAccountMassBundleTest extends TestCase
{
    public function testBundleInstance(): void
    {
        $bundle = new WechatOfficialAccountMassBundle();
        
        self::assertInstanceOf(WechatOfficialAccountMassBundle::class, $bundle);
    }
    
    public function testBundlePath(): void
    {
        $bundle = new WechatOfficialAccountMassBundle();
        
        self::assertStringEndsWith('wechat-official-account-mass-bundle/src', $bundle->getPath());
    }
}