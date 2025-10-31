<?php

declare(strict_types=1);

namespace WechatOfficialAccountMassBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatOfficialAccountMassBundle\Entity\MassTask;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信公众号')) {
            $item->addChild('微信公众号');
        }

        $wechatMenu = $item->getChild('微信公众号');
        if (null === $wechatMenu) {
            return;
        }

        // 添加群发任务菜单项
        $wechatMenu->addChild('群发任务')
            ->setUri($this->linkGenerator->getCurdListPage(MassTask::class))
            ->setAttribute('icon', 'fas fa-bullhorn')
        ;
    }
}
