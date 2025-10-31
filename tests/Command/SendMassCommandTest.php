<?php

namespace WechatOfficialAccountMassBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use WechatOfficialAccountMassBundle\Command\SendMassCommand;
use WechatOfficialAccountMassBundle\Service\MassTaskService;

/**
 * @internal
 */
#[CoversClass(SendMassCommand::class)]
#[RunTestsInSeparateProcesses]
final class SendMassCommandTest extends AbstractCommandTestCase
{
    private SendMassCommand $command;

    private MassTaskService&MockObject $massTaskService;

    protected function getCommandTester(): CommandTester
    {
        return new CommandTester($this->command);
    }

    protected function onSetUp(): void
    {
        $this->massTaskService = $this->createMock(MassTaskService::class);
        self::getContainer()->set(MassTaskService::class, $this->massTaskService);
        $this->command = self::getService(SendMassCommand::class);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $this->assertInstanceOf(SendMassCommand::class, $this->command);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('wechat:send-mass', SendMassCommand::NAME);
    }

    public function testCommandExecutionWithNoTasksShowsInfoMessage(): void
    {
        $this->massTaskService->expects($this->once())
            ->method('processPendingTasks')
            ->willReturn(0)
        ;

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('没有待处理的群发任务', $commandTester->getDisplay());
    }

    public function testCommandExecutionWithTasksShowsSuccessMessage(): void
    {
        $this->massTaskService->expects($this->once())
            ->method('processPendingTasks')
            ->willReturn(3)
        ;

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('成功处理 3 个群发任务', $commandTester->getDisplay());
    }

    public function testCommandExecutionHandlesException(): void
    {
        $this->massTaskService->expects($this->once())
            ->method('processPendingTasks')
            ->willThrowException(new \RuntimeException('Database connection failed'))
        ;

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('处理群发任务时发生错误', $commandTester->getDisplay());
        $this->assertStringContainsString('Database connection failed', $commandTester->getDisplay());
    }
}
