<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;

/**
 * @internal
 */
#[CoversClass(SendByOpenIdRequest::class)]
final class SendByOpenIdRequestTest extends RequestTestCase
{
    private SendByOpenIdRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // SendByOpenIdRequest 是一个请求类，不是服务，直接实例化是合理的做法
        $this->request = new SendByOpenIdRequest();
    }

    public function testGetRequestPathReturnsCorrectUrl(): void
    {
        $expectedUrl = 'https://api.weixin.qq.com/cgi-bin/message/mass/send';
        $this->assertEquals($expectedUrl, $this->request->getRequestPath());
    }

    public function testGetRequestOptionsReturnsCorrectOptionsWithRequiredFields(): void
    {
        // 设置必需的字段
        $openIds = ['openid1', 'openid2', 'openid3'];
        $message = [
            'msgtype' => 'text',
            'text' => [
                'content' => 'Test content',
            ],
        ];

        $this->request->setToUsers($openIds);
        $this->request->setMessage($message);

        $expected = [
            'json' => [
                'touser' => $openIds,
                'send_ignore_reprint' => 1, // 默认为true，所以值为1
                'msgtype' => 'text',
                'text' => [
                    'content' => 'Test content',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->request->getRequestOptions());
    }

    public function testGetRequestOptionsIncludesClientMsgIdWhenProvided(): void
    {
        $openIds = ['openid1', 'openid2'];
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];
        $clientMsgId = 'client_msg_id_123';

        $this->request->setToUsers($openIds);
        $this->request->setMessage($message);
        $this->request->setClientMsgId($clientMsgId);

        $options = $this->request->getRequestOptions();
        $this->assertNotNull($options, 'Request options should not be null');
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['json']);
        $jsonOptions = $options['json'];
        $this->assertArrayHasKey('clientmsgid', $jsonOptions);
        $this->assertEquals($clientMsgId, $jsonOptions['clientmsgid']);
    }

    public function testSetAndGetToUsersWorksCorrectly(): void
    {
        $openIds = ['openid1', 'openid2', 'openid3'];
        $this->request->setToUsers($openIds);
        $this->assertEquals($openIds, $this->request->getToUsers());
    }

    public function testSetAndGetAccountWorksCorrectly(): void
    {
        // 必须Mock具体类Account因为：
        // 1. Request对象需要携带Account信息用于微信API身份验证
        // 2. Account实体包含特定的属性和方法，测试需要验证设置和获取行为
        // 3. Mock实体类有助于隔离数据库依赖，确保单元测试的独立性
        $account = $this->createMock(Account::class);
        $this->request->setAccount($account);
        $this->assertSame($account, $this->request->getAccount());
    }

    public function testSetAndGetMessageWorksCorrectly(): void
    {
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];
        $this->request->setMessage($message);
        $this->assertEquals($message, $this->request->getMessage());
    }

    public function testSetAndGetClientMsgIdWorksCorrectly(): void
    {
        $clientMsgId = 'client_msg_id_123';
        $this->request->setClientMsgId($clientMsgId);
        $this->assertEquals($clientMsgId, $this->request->getClientMsgId());
    }

    public function testSetAndGetSendIgnoreReprintWorksCorrectly(): void
    {
        // 默认值应为true
        $this->assertTrue($this->request->isSendIgnoreReprint());

        // 设置为false
        $this->request->setSendIgnoreReprint(false);
        $this->assertFalse($this->request->isSendIgnoreReprint());

        // 设置为true
        $this->request->setSendIgnoreReprint(true);
        $this->assertTrue($this->request->isSendIgnoreReprint());
    }
}
