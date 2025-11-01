<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountMassBundle\Request\PreviewTaskRequest;

/**
 * @internal
 */
#[CoversClass(PreviewTaskRequest::class)]
final class PreviewTaskRequestTest extends RequestTestCase
{
    private PreviewTaskRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // PreviewTaskRequest 是一个请求类，不是服务，直接实例化是合理的做法
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
            'text' => ['content' => 'Test message'],
        ];

        $this->request->setToUser($toUser);
        $this->request->setMessage($message);

        $options = $this->request->getRequestOptions();

        // 强化类型安全检查
        $this->assertNotNull($options, 'Request options should not be null');
        $this->assertIsArray($options);
        $this->assertArrayHasKey('json', $options);

        // 确保json键对应的值也是数组
        $jsonData = $options['json'];
        $this->assertIsArray($jsonData, 'JSON data should be an array');

        $this->assertArrayHasKey('touser', $jsonData);
        $this->assertSame($toUser, $jsonData['touser']);
        $this->assertArrayHasKey('msgtype', $jsonData);
        $this->assertArrayHasKey('text', $jsonData);
        $this->assertSame('text', $jsonData['msgtype']);
        $this->assertIsArray($jsonData['text']);
        $this->assertArrayHasKey('content', $jsonData['text']);
        $this->assertSame('Test message', $jsonData['text']['content']);
    }
}
