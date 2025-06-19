<?php

namespace WechatOfficialAccountMassBundle\Tests\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Command\SendMassCommand;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;
use WechatOfficialAccountMassBundle\Request\SendByTagRequest;
use WechatOfficialAccountMassBundle\Request\SendToAllRequest;

class SendMassCommandTest extends TestCase
{
    private SendMassCommand $command;
    private CommandTester $commandTester;
    private LoggerInterface $logger;
    private MassTaskRepository $taskRepository;
    private OfficialAccountClient $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taskRepository = $this->createMock(MassTaskRepository::class);
        $this->client = $this->createMock(OfficialAccountClient::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->command = new SendMassCommand(
            $this->logger,
            $this->taskRepository,
            $this->client,
            $this->entityManager
        );
        
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute_withNoTasks_returnsSuccess(): void
    {
        // 模拟查询构建器
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        // 设置模拟行为
        $this->taskRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.sent = false AND a.valid = true')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('a.sendTime <= :now')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('now', $this->callback(function ($now) {
                return $now instanceof CarbonImmutable;
            }))
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        // 执行命令并验证结果
        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }

    public function testExecute_withTask_withTagId_sendsByTag(): void
    {
        // 创建一个任务对象
        $task = new MassTask();
        $account = $this->createMock(Account::class);
        
        // 设置任务属性
        $task->setAccount($account);
        $task->setTagId('100');
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        
        // 模拟查询结果
        $this->setupQueryMock([$task]);
        
        // 模拟客户端响应
        $response = ['msg_id' => '12345', 'msg_data_id' => '67890'];
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($request) {
                return $request instanceof SendByTagRequest 
                    && $request->getTagId() === '100';
            }))
            ->willReturn($response);
        
        // 模拟实体管理器保存状态
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($task);
            
        $this->entityManager->expects($this->exactly(2))
            ->method('flush');
        
        // 执行命令
        $result = $this->commandTester->execute([]);
        
        // 验证结果
        $this->assertEquals(0, $result);
        $this->assertTrue($task->isSent());
        $this->assertEquals('12345', $task->getMsgTaskId());
        $this->assertEquals('67890', $task->getMsgDataId());
    }

    public function testExecute_withTask_withOpenIds_sendsByOpenId(): void
    {
        // 创建一个任务对象
        $task = new MassTask();
        $account = $this->createMock(Account::class);
        
        // 设置任务属性
        $task->setAccount($account);
        $task->setOpenIds(['openid1', 'openid2']);
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        
        // 模拟查询结果
        $this->setupQueryMock([$task]);
        
        // 模拟客户端响应
        $response = ['msg_id' => '12345', 'msg_data_id' => '67890'];
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($request) {
                return $request instanceof SendByOpenIdRequest 
                    && $request->getToUsers() === ['openid1', 'openid2'];
            }))
            ->willReturn($response);
        
        // 执行命令
        $result = $this->commandTester->execute([]);
        
        // 验证结果
        $this->assertEquals(0, $result);
    }

    public function testExecute_withTask_withoutTagOrOpenIds_sendsToAll(): void
    {
        // 创建一个任务对象
        $task = new MassTask();
        $account = $this->createMock(Account::class);
        
        // 设置任务属性
        $task->setAccount($account);
        $task->setType(MassType::TEXT);
        $task->setContent('Test message');
        
        // 模拟查询结果
        $this->setupQueryMock([$task]);
        
        // 模拟客户端响应
        $response = ['msg_id' => '12345', 'msg_data_id' => '67890'];
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($request) {
                return $request instanceof SendToAllRequest;
            }))
            ->willReturn($response);
        
        // 执行命令
        $result = $this->commandTester->execute([]);
        
        // 验证结果
        $this->assertEquals(0, $result);
    }

    public function testExecute_withExceptionOnUpdate_logsErrorAndContinues(): void
    {
        // 创建两个任务对象
        $task1 = new MassTask();
        $task2 = new MassTask();
        $account = $this->createMock(Account::class);
        
        // 设置任务属性
        $task1->setAccount($account);
        $task1->setType(MassType::TEXT);
        $task1->setContent('Test message 1');
        
        $task2->setAccount($account);
        $task2->setType(MassType::TEXT);
        $task2->setContent('Test message 2');
        
        // 模拟查询结果
        $this->setupQueryMock([$task1, $task2]);
        
        // 第一个任务抛出异常
        $exception = new \Exception('Test exception');
        
        // 使用PHPUnit 10兼容的方式设置多次调用的不同返回值
        $this->entityManager->expects($this->exactly(3))
            ->method('persist')
            ->withAnyParameters();
            
        $this->entityManager->expects($this->exactly(3))
            ->method('flush')
            ->will($this->onConsecutiveCalls(
                $this->throwException($exception),
                null,
                null
            ));
        
        // 应该记录错误日志
        $this->logger->expects($this->once())
            ->method('error')
            ->with('记录发送状态时发生错误', $this->callback(function ($context) use ($exception, $task1) {
                return $context['exception'] === $exception && $context['task'] === $task1;
            }));
        
        // 模拟客户端响应（只有第二个任务会发送）
        $response = ['msg_id' => '12345', 'msg_data_id' => '67890'];
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($request) {
                return $request instanceof SendToAllRequest;
            }))
            ->willReturn($response);
        
        // 执行命令
        $result = $this->commandTester->execute([]);
        
        // 验证结果
        $this->assertEquals(0, $result);
    }

    private function setupQueryMock(array $results): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $this->taskRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('where')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($results);
    }
} 