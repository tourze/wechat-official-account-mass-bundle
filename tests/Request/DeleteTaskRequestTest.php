<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountMassBundle\Request\DeleteTaskRequest;

/**
 * @internal
 */
#[CoversClass(DeleteTaskRequest::class)]
final class DeleteTaskRequestTest extends RequestTestCase
{
    private DeleteTaskRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // DeleteTaskRequest 是一个请求类，不是服务，直接实例化是合理的做法
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

        // 强化类型安全检查
        $this->assertNotNull($options, 'Request options should not be null');
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);

        // 确保json键对应的值也是数组
        $jsonData = $options['json'];
        $this->assertIsArray($jsonData, 'JSON data should be an array');

        $this->assertArrayHasKey('msg_id', $jsonData);
        $this->assertSame($msgId, $jsonData['msg_id']);
    }
}
