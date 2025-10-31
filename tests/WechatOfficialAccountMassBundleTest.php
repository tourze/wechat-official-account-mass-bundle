<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatOfficialAccountMassBundle\WechatOfficialAccountMassBundle;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountMassBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatOfficialAccountMassBundleTest extends AbstractBundleTestCase
{
}
