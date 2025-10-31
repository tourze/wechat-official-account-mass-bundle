<?php

namespace WechatOfficialAccountMassBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;
use WechatOfficialAccountMassBundle\Request\SendByTagRequest;
use WechatOfficialAccountMassBundle\Request\SendToAllRequest;

#[WithMonologChannel(channel: 'wechat_official_account_mass')]
class MassTaskService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MassTaskRepository $taskRepository,
        private readonly OfficialAccountClient $client,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<int, MassTask>
     */
    public function getPendingTasks(?\DateTimeInterface $sendTime = null): array
    {
        $sendTime ??= CarbonImmutable::now();

        /** @var array<int, MassTask> */
        return $this->taskRepository->createQueryBuilder('a')
            ->where('a.sent = false AND a.valid = true')
            ->andWhere('a.sendTime <= :now')
            ->setParameter('now', $sendTime)
            ->getQuery()
            ->getResult()
        ;
    }

    public function markTaskAsSent(MassTask $task): void
    {
        try {
            $task->setSent(true);
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('记录发送状态时发生错误', [
                'exception' => $exception,
                'taskId' => $task->getId(),
                'taskTitle' => $task->getTitle(),
            ]);
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function sendMassTask(MassTask $task): array
    {
        $request = $this->createRequestFromTask($task);

        $response = $this->client->request($request);

        /** @var array<string, mixed> $response */
        $this->updateTaskWithResponse($task, $response);

        return $response;
    }

    public function processPendingTasks(): int
    {
        $tasks = $this->getPendingTasks();
        $processedCount = 0;

        foreach ($tasks as $task) {
            try {
                $this->markTaskAsSent($task);
                $this->sendMassTask($task);
                ++$processedCount;
            } catch (\Throwable $exception) {
                $this->logger->error('处理群发任务时发生错误', [
                    'exception' => $exception,
                    'taskId' => $task->getId(),
                    'taskTitle' => $task->getTitle(),
                ]);
            }
        }

        return $processedCount;
    }

    private function createRequestFromTask(MassTask $task): SendByTagRequest|SendByOpenIdRequest|SendToAllRequest
    {
        $request = null;

        if (null !== $task->getTagId()) {
            $request = new SendByTagRequest();
            $request->setTagId($task->getTagId());
        } elseif (count($task->getOpenIds()) > 0) {
            $request = new SendByOpenIdRequest();
            $request->setToUsers($task->getOpenIds());
        } else {
            $request = new SendToAllRequest();
        }

        $account = $task->getAccount();
        if (null === $account) {
            throw new \InvalidArgumentException('Task must have an account to send mass message');
        }

        $request->setAccount($account);
        $request->setMessage($task->formatMessage());

        return $request;
    }

    /**
     * @param array<string, mixed> $response
     */
    private function updateTaskWithResponse(MassTask $task, array $response): void
    {
        if (isset($response['msg_id'])) {
            $task->setMsgTaskId(is_string($response['msg_id']) ? $response['msg_id'] : null);
        }

        if (isset($response['msg_data_id'])) {
            $task->setMsgDataId(is_string($response['msg_data_id']) ? $response['msg_data_id'] : null);
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}
