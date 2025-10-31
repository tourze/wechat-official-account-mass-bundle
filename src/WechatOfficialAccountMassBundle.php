<?php

namespace WechatOfficialAccountMassBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;

class WechatOfficialAccountMassBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            WechatOfficialAccountBundle::class => ['all' => true],
        ];
    }
}
