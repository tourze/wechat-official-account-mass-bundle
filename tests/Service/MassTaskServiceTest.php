<?php

namespace WechatOfficialAccountMassBundle\Tests\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;
use WechatOfficialAccountMassBundle\Request\SendByTagRequest;
use WechatOfficialAccountMassBundle\Request\SendToAllRequest;
use WechatOfficialAccountMassBundle\Service\MassTaskService;

/**
 * @internal
 */
#[CoversClass(MassTaskService::class)]
#[RunTestsInSeparateProcesses]
final class MassTaskServiceTest extends AbstractIntegrationTestCase
{
    private MassTaskService $service;

    private MassTaskRepository $repository;

    private OfficialAccountClient&MockObject $client;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MassTaskRepository::class);

        // Mock OfficialAccountClient 以避免真实的 API 调用
        $this->client = $this->createMock(OfficialAccountClient::class);
        self::getContainer()->set(OfficialAccountClient::class, $this->client);

        $this->service = self::getService(MassTaskService::class);
    }

    private function createTestAccount(string $name = 'Test Account'): Account
    {
        $em = self::getService(EntityManagerInterface::class);

        $account = new Account();
        $account->setName($name);
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $account->setValid(true);

        $em->persist($account);
        $em->flush();

        return $account;
    }

    public function testGetPendingTasksReturnsPendingTasks(): void
    {
        // 清理所有待处理任务，确保测试环境干净
        /** @var MassTask[] $pendingTasks */
        $pendingTasks = $this->repository->findBy(['sent' => false, 'valid' => true]);
        foreach ($pendingTasks as $task) {
            $task->setSent(true);
            $this->repository->save($task);
        }

        // 创建测试数据
        $account = $this->createTestAccount('Test Account 1');

        $task1 = new MassTask();
        $task1->setAccount($account);
        $task1->setTitle('Pending Task 1');
        $task1->setType(MassType::TEXT);
        $task1->setContent('Test content 1');
        $task1->setSendTime(CarbonImmutable::now()->subHour());
        $task1->setValid(true);
        $task1->setSent(false);
        $this->repository->save($task1);

        $task2 = new MassTask();
        $task2->setAccount($account);
        $task2->setTitle('Pending Task 2');
        $task2->setType(MassType::TEXT);
        $task2->setContent('Test content 2');
        $task2->setSendTime(CarbonImmutable::now()->subMinutes(30));
        $task2->setValid(true);
        $task2->setSent(false);
        $this->repository->save($task2);

        // 已发送的任务（不应返回）
        $task3 = new MassTask();
        $task3->setAccount($account);
        $task3->setTitle('Sent Task');
        $task3->setType(MassType::TEXT);
        $task3->setContent('Test content 3');
        $task3->setSendTime(CarbonImmutable::now()->subHour());
        $task3->setValid(true);
        $task3->setSent(true);
        $this->repository->save($task3);

        // 未来的任务（不应返回）
        $task4 = new MassTask();
        $task4->setAccount($account);
        $task4->setTitle('Future Task');
        $task4->setType(MassType::TEXT);
        $task4->setContent('Test content 4');
        $task4->setSendTime(CarbonImmutable::now()->addHour());
        $task4->setValid(true);
        $task4->setSent(false);
        $this->repository->save($task4);

        $result = $this->service->getPendingTasks();

        $this->assertCount(2, $result);
        $this->assertContains($task1, $result);
        $this->assertContains($task2, $result);
    }

    public function testGetPendingTasksWithCustomSendTime(): void
    {
        $account = $this->createTestAccount('User Test');
        $customTime = CarbonImmutable::parse('2024-01-01 12:00:00');

        $task = new MassTask();
        $task->setAccount($account);
        $task->setTitle('Old Task');
        $task->setType(MassType::TEXT);
        $task->setContent('Old content');
        $task->setSendTime(CarbonImmutable::parse('2023-12-31 12:00:00'));
        $task->setValid(true);
        $task->setSent(false);
        $this->repository->save($task);

        $result = $this->service->getPendingTasks($customTime);

        $this->assertCount(1, $result);
        $this->assertEquals('Old Task', $result[0]->getTitle());
    }

    public function testMarkTaskAsSentSuccessfully(): void
    {
        $account = $this->createTestAccount('Mark Test');

        $task = new MassTask();
        $task->setAccount($account);
        $task->setTitle('Task to Mark');
        $task->setType(MassType::TEXT);
        $task->setContent('Content');
        $task->setSendTime(CarbonImmutable::now());
        $task->setValid(true);
        $task->setSent(false);
        $this->repository->save($task);

        $this->assertFalse($task->isSent());

        $this->service->markTaskAsSent($task);

        $this->assertTrue($task->isSent());

        // 验证数据库中也已更新
        /** @var MassTask $updatedTask */
        $updatedTask = $this->repository->find($task->getId());
        $this->assertTrue($updatedTask->isSent());
    }

    public function testSendMassTaskWithTagId(): void
    {
        $account = $this->createTestAccount('Tag Test');

        $task = new MassTask();
        $task->setAccount($account);
        $task->setTitle('Tag Task');
        $task->setTagId('123');
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        $task->setSendTime(CarbonImmutable::now());
        $this->repository->save($task);

        $response = ['msg_id' => 'msg123', 'msg_data_id' => 'data123'];

        $this->client->expects($this->once())
            ->method('request')
            ->with(self::callback(function ($request) {
                return $request instanceof SendByTagRequest
                    && '123' === $request->getTagId();
            }))
            ->willReturn($response)
        ;

        $result = $this->service->sendMassTask($task);

        $this->assertEquals($response, $result);
        $this->assertEquals('msg123', $task->getMsgTaskId());
        $this->assertEquals('data123', $task->getMsgDataId());

        // 验证数据库更新
        /** @var MassTask $updatedTask */
        $updatedTask = $this->repository->find($task->getId());
        $this->assertEquals('msg123', $updatedTask->getMsgTaskId());
        $this->assertEquals('data123', $updatedTask->getMsgDataId());
    }

    public function testSendMassTaskWithOpenIds(): void
    {
        $account = $this->createTestAccount('OpenID Test');

        $task = new MassTask();
        $task->setAccount($account);
        $task->setTitle('OpenID Task');
        $task->setOpenIds(['openid1', 'openid2']);
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        $task->setSendTime(CarbonImmutable::now());
        $this->repository->save($task);

        $response = ['msg_id' => 'msg456'];

        $this->client->expects($this->once())
            ->method('request')
            ->with(self::callback(function ($request) {
                return $request instanceof SendByOpenIdRequest
                    && $request->getToUsers() === ['openid1', 'openid2'];
            }))
            ->willReturn($response)
        ;

        $result = $this->service->sendMassTask($task);

        $this->assertEquals($response, $result);
        $this->assertEquals('msg456', $task->getMsgTaskId());
        $this->assertNull($task->getMsgDataId());
    }

    public function testSendMassTaskToAll(): void
    {
        $account = $this->createTestAccount('All Test');

        $task = new MassTask();
        $task->setAccount($account);
        $task->setTitle('Send to All');
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        $task->setSendTime(CarbonImmutable::now());
        $this->repository->save($task);

        $response = ['msg_data_id' => 'data789'];

        $this->client->expects($this->once())
            ->method('request')
            ->with(self::isInstanceOf(SendToAllRequest::class))
            ->willReturn($response)
        ;

        $result = $this->service->sendMassTask($task);

        $this->assertEquals($response, $result);
        $this->assertNull($task->getMsgTaskId());
        $this->assertEquals('data789', $task->getMsgDataId());
    }

    public function testProcessPendingTasksWithNoTasks(): void
    {
        // 清理所有待处理任务
        /** @var MassTask[] $pendingTasks */
        $pendingTasks = $this->repository->findBy(['sent' => false, 'valid' => true]);
        foreach ($pendingTasks as $task) {
            $task->setSent(true);
            $this->repository->save($task);
        }

        $this->client->expects($this->never())
            ->method('request')
        ;

        $processedCount = $this->service->processPendingTasks();

        $this->assertEquals(0, $processedCount);
    }

    public function testProcessPendingTasksSuccessfully(): void
    {
        // 清理所有待处理任务，确保测试环境干净
        /** @var MassTask[] $pendingTasks */
        $pendingTasks = $this->repository->findBy(['sent' => false, 'valid' => true]);
        foreach ($pendingTasks as $task) {
            $task->setSent(true);
            $this->repository->save($task);
        }

        $account = $this->createTestAccount('Process Test');

        $task1 = new MassTask();
        $task1->setAccount($account);
        $task1->setTitle('Process Task 1');
        $task1->setType(MassType::TEXT);
        $task1->setContent('Message 1');
        $task1->setSendTime(CarbonImmutable::now()->subHour());
        $task1->setValid(true);
        $task1->setSent(false);
        $this->repository->save($task1);

        $task2 = new MassTask();
        $task2->setAccount($account);
        $task2->setTitle('Process Task 2');
        $task2->setType(MassType::TEXT);
        $task2->setContent('Message 2');
        $task2->setTagId('100');
        $task2->setSendTime(CarbonImmutable::now()->subMinutes(30));
        $task2->setValid(true);
        $task2->setSent(false);
        $this->repository->save($task2);

        $this->client->expects($this->exactly(2))
            ->method('request')
            ->willReturn(['msg_id' => 'test'])
        ;

        $processedCount = $this->service->processPendingTasks();

        $this->assertEquals(2, $processedCount);

        // 验证任务已标记为已发送
        /** @var MassTask $updatedTask1 */
        $updatedTask1 = $this->repository->find($task1->getId());
        /** @var MassTask $updatedTask2 */
        $updatedTask2 = $this->repository->find($task2->getId());

        $this->assertTrue($updatedTask1->isSent());
        $this->assertTrue($updatedTask2->isSent());
        $this->assertEquals('test', $updatedTask1->getMsgTaskId());
        $this->assertEquals('test', $updatedTask2->getMsgTaskId());
    }

    public function testProcessPendingTasksWithPartialFailure(): void
    {
        // 清理所有待处理任务，确保测试环境干净
        /** @var MassTask[] $pendingTasks */
        $pendingTasks = $this->repository->findBy(['sent' => false, 'valid' => true]);
        foreach ($pendingTasks as $task) {
            $task->setSent(true);
            $this->repository->save($task);
        }

        $account = $this->createTestAccount('Failure Test');

        $task1 = new MassTask();
        $task1->setAccount($account);
        $task1->setTitle('Task 1');
        $task1->setType(MassType::TEXT);
        $task1->setContent('Message 1');
        $task1->setSendTime(CarbonImmutable::now()->subHour());
        $task1->setValid(true);
        $task1->setSent(false);
        $this->repository->save($task1);

        $task2 = new MassTask();
        $task2->setAccount($account);
        $task2->setTitle('Task 2');
        $task2->setType(MassType::TEXT);
        $task2->setContent('Message 2');
        $task2->setSendTime(CarbonImmutable::now()->subMinutes(30));
        $task2->setValid(true);
        $task2->setSent(false);
        $this->repository->save($task2);

        // 第一个请求失败，第二个成功
        $this->client->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Exception('API Error')),
                ['msg_id' => 'success']
            )
        ;

        $processedCount = $this->service->processPendingTasks();

        // 即使第一个失败，也应该继续处理第二个
        $this->assertEquals(1, $processedCount);

        /** @var MassTask $updatedTask1 */
        $updatedTask1 = $this->repository->find($task1->getId());
        /** @var MassTask $updatedTask2 */
        $updatedTask2 = $this->repository->find($task2->getId());

        // 两个任务都已标记为已发送
        $this->assertTrue($updatedTask1->isSent());
        $this->assertTrue($updatedTask2->isSent());

        // 第一个任务失败了，所以没有 msgTaskId
        $this->assertNull($updatedTask1->getMsgTaskId());

        // 第二个任务成功发送，有 msgTaskId
        $this->assertEquals('success', $updatedTask2->getMsgTaskId());
    }
}
