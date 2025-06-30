<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMassBundle\Request\DeleteTaskRequest;

class DeleteTaskRequestTest extends TestCase
{
    private DeleteTaskRequest $request;

    protected function setUp(): void
    {
        $this->request = new DeleteTaskRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertSame('https://api.weixin.qq.com/cgi-bin/message/mass/delete', $this->request->getRequestPath());
    }

    public function testGetSetMsgId(): void
    {
        $msgId = '123456789';
        $this->request->setMsgId($msgId);
        $this->assertSame($msgId, $this->request->getMsgId());
    }

    public function testGetRequestOptions(): void
    {
        $msgId = '987654321';
        $this->request->setMsgId($msgId);
        
        $options = $this->request->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('msg_id', $options['json']);
        $this->assertSame($msgId, $options['json']['msg_id']);
    }
}