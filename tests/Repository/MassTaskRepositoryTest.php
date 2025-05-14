<?php

namespace WechatOfficialAccountMassBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMassBundle\Repository\MassTaskRepository;

class MassTaskRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(MassTaskRepository::class));
    }
} 