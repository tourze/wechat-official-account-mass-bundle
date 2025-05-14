<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Request\SendByOpenIdRequest;

class SendByOpenIdRequestTest extends TestCase
{
    private SendByOpenIdRequest $request;

    protected function setUp(): void
    {
        $this->request = new SendByOpenIdRequest();
    }

    public function testGetRequestPath_returnsCorrectUrl(): void
    {
        $expectedUrl = 'https://api.weixin.qq.com/cgi-bin/message/mass/send';
        $this->assertEquals($expectedUrl, $this->request->getRequestPath());
    }

    public function testGetRequestOptions_returnsCorrectOptions_withRequiredFields(): void
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

    public function testGetRequestOptions_includesClientMsgId_whenProvided(): void
    {
        $openIds = ['openid1', 'openid2'];
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];
        $clientMsgId = 'client_msg_id_123';

        $this->request->setToUsers($openIds);
        $this->request->setMessage($message);
        $this->request->setClientMsgId($clientMsgId);

        $options = $this->request->getRequestOptions();
        $this->assertArrayHasKey('clientmsgid', $options['json']);
        $this->assertEquals($clientMsgId, $options['json']['clientmsgid']);
    }

    public function testSetAndGetToUsers_worksCorrectly(): void
    {
        $openIds = ['openid1', 'openid2', 'openid3'];
        $this->request->setToUsers($openIds);
        $this->assertEquals($openIds, $this->request->getToUsers());
    }

    public function testSetAndGetAccount_worksCorrectly(): void
    {
        $account = $this->createMock(Account::class);
        $this->request->setAccount($account);
        $this->assertSame($account, $this->request->getAccount());
    }

    public function testSetAndGetMessage_worksCorrectly(): void
    {
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];
        $this->request->setMessage($message);
        $this->assertEquals($message, $this->request->getMessage());
    }

    public function testSetAndGetClientMsgId_worksCorrectly(): void
    {
        $clientMsgId = 'client_msg_id_123';
        $this->request->setClientMsgId($clientMsgId);
        $this->assertEquals($clientMsgId, $this->request->getClientMsgId());
    }

    public function testSetAndGetSendIgnoreReprint_worksCorrectly(): void
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