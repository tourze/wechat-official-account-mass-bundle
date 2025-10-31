<?php

namespace WechatOfficialAccountMassBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatOfficialAccountMassBundle\Service\MassTaskService;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '公众号群发')]
class SendMassCommand extends Command
{
    public const NAME = 'wechat:send-mass';

    public function __construct(
        private readonly MassTaskService $massTaskService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $processedCount = $this->massTaskService->processPendingTasks();

            if ($processedCount > 0) {
                $io->success(sprintf('成功处理 %d 个群发任务', $processedCount));
            } else {
                $io->info('没有待处理的群发任务');
            }

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error(sprintf('处理群发任务时发生错误: %s', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
