<?php

namespace WechatOfficialAccountMassBundle\Command;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;
use WechatOfficialAccountMassBundle\Request\SendByTagRequest;
use WechatOfficialAccountMassBundle\Request\SendToAllRequest;

#[AsCronTask('* * * * *')]
#[AsCommand(name: 'wechat:send-mass', description: '公众号群发')]
class SendMassCommand extends Command
{
    public const NAME = 'wechat:send-mass';
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MassTaskRepository $taskRepository,
        private readonly OfficialAccountClient $client,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var MassTask[] $models */
        $models = $this->taskRepository->createQueryBuilder('a')
            ->where('a.sent = false AND a.valid = true')
            ->andWhere('a.sendTime <= :now')
            ->setParameter('now', Carbon::now())
            ->getQuery()
            ->getResult();

        foreach ($models as $task) {
            try {
                $task->setSent(true);
                $this->entityManager->persist($task);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error('记录发送状态时发生错误', [
                    'exception' => $exception,
                    'task' => $task,
                ]);
                continue;
            }

            $request = null;
            // 有设置标签，就走标签逻辑
            if ($task->getTagId()) {
                $request = new SendByTagRequest();
                $request->setTagId($task->getTagId());
            }
            // 有设置用户列表
            if ($task->getOpenIds()) {
                $request = new SendByOpenIdRequest();
                $request->setToUsers($task->getOpenIds());
            }
            if (!$request) {
                $request = new SendToAllRequest();
            }

            $request->setAccount($task->getAccount());
            $request->setMessage($task->formatMessage());

            $response = $this->client->request($request);
            if (isset($response['msg_id'])) {
                $task->setMsgTaskId($response['msg_id']);
            }
            if (isset($response['msg_data_id'])) {
                $task->setMsgDataId($response['msg_data_id']);
            }
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
