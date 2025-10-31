<?php

namespace WechatOfficialAccountMassBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

class MassTaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $account1 = new Account();
        $account1->setAppId('test_app_id_1');
        $account1->setAppSecret('test_app_secret_1');
        $account1->setName('测试账号1');
        $manager->persist($account1);

        $account2 = new Account();
        $account2->setAppId('test_app_id_2');
        $account2->setAppSecret('test_app_secret_2');
        $account2->setName('测试账号2');
        $manager->persist($account2);

        $account3 = new Account();
        $account3->setAppId('test_app_id_3');
        $account3->setAppSecret('test_app_secret_3');
        $account3->setName('测试账号3');
        $manager->persist($account3);

        $task1 = new MassTask();
        $task1->setAccount($account1);
        $task1->setTitle('测试群发消息1');
        $task1->setType(MassType::TEXT);
        $task1->setContent('这是一条测试群发消息');
        $task1->setSendTime(new \DateTimeImmutable());
        $task1->setValid(true);
        $task1->setSent(false);
        $task1->setCreateTime(new \DateTimeImmutable());
        $task1->setUpdateTime(new \DateTimeImmutable());

        $manager->persist($task1);

        $task2 = new MassTask();
        $task2->setAccount($account2);
        $task2->setTitle('测试语音群发');
        $task2->setType(MassType::VOICE);
        $task2->setContent('');
        $task2->setMediaId('test_media_id');
        $task2->setTagId('100');
        $task2->setSendTime(new \DateTimeImmutable('-1 day'));
        $task2->setValid(true);
        $task2->setSent(true);
        $task2->setMsgTaskId('task_123');
        $task2->setMsgDataId('data_456');
        $task2->setCreateTime(new \DateTimeImmutable('-1 day'));
        $task2->setUpdateTime(new \DateTimeImmutable());

        $manager->persist($task2);

        $task3 = new MassTask();
        $task3->setAccount($account3);
        $task3->setTitle('测试指定用户群发');
        $task3->setType(MassType::TEXT);
        $task3->setContent('指定用户群发内容');
        $task3->setOpenIds(['openid1', 'openid2', 'openid3']);
        $task3->setSendTime(new \DateTimeImmutable('+1 hour'));
        $task3->setValid(false);
        $task3->setSent(false);
        $task3->setCreateTime(new \DateTimeImmutable('-2 hours'));
        $task3->setUpdateTime(new \DateTimeImmutable('-1 hour'));

        $manager->persist($task3);

        $manager->flush();
    }
}
