<?php

namespace WechatOfficialAccountMassBundle\Procedure;

use AntdCpBundle\Builder\Action\ApiCallAction;
use AppBundle\Procedure\Base\ApiCallActionProcedure;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCSecurityBundle\Attribute\MethodPermission;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;
use WechatOfficialAccountMassBundle\Request\DeleteTaskRequest;

#[Log]
#[MethodExpose(StopWechatOfficialAccountMassTask::NAME)]
#[IsGranted('ROLE_OPERATOR')]
#[MethodPermission(permission: MassTask::class . '::renderStopAction', title: '停止发送')]
class StopWechatOfficialAccountMassTask extends ApiCallActionProcedure
{
    public const NAME = 'StopWechatOfficialAccountMassTask';

    public function __construct(
        private readonly MassTaskRepository $taskRepository,
        private readonly OfficialAccountClient $client,
    ) {
    }

    public function getAction(): ApiCallAction
    {
        return ApiCallAction::gen()
            ->setLabel('停止发送')
            ->setConfirmText('是否确定停止当前发送任务')
            ->setApiName(StopWechatOfficialAccountMassTask::NAME);
    }

    public function execute(): array
    {
        $that = $this->taskRepository->findOneBy(['id' => $this->id]);
        if (!$that) {
            throw new ApiException('找不到记录');
        }

        if (!$that->isSent()) {
            throw new ApiException('只有已经发送成功的消息才能删除');
        }

        $request = new DeleteTaskRequest();
        $request->setMsgId($that->getMsgTaskId());
        try {
            $this->client->request($request);
        } catch (\Throwable $exception) {
            throw new ApiException($exception->getMessage(), previous: $exception);
        }

        return [
            '__message' => '停止成功',
        ];
    }
}
