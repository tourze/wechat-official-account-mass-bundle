<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMassBundle\Request\PreviewTaskRequest;

class PreviewTaskRequestTest extends TestCase
{
    private PreviewTaskRequest $request;

    protected function setUp(): void
    {
        $this->request = new PreviewTaskRequest();
    }

    public function testGetRequestPath(): void
    {
        $this->assertSame('https://api.weixin.qq.com/cgi-bin/message/mass/preview', $this->request->getRequestPath());
    }

    public function testGetSetToUser(): void
    {
        $toUser = 'test_openid_123';
        $this->request->setToUser($toUser);
        $this->assertSame($toUser, $this->request->getToUser());
    }

    public function testGetRequestOptions(): void
    {
        $toUser = 'test_user_456';
        $message = [
            'msgtype' => 'text',
            'text' => ['content' => 'Test message']
        ];
        
        $this->request->setToUser($toUser);
        $this->request->setMessage($message);
        
        $options = $this->request->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);
        $this->assertArrayHasKey('touser', $options['json']);
        $this->assertSame($toUser, $options['json']['touser']);
        $this->assertArrayHasKey('msgtype', $options['json']);
        $this->assertArrayHasKey('text', $options['json']);
    }
}