<?php

namespace WechatOfficialAccountMassBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Request\SendByTagRequest;

class SendByTagRequestTest extends TestCase
{
    private SendByTagRequest $request;

    protected function setUp(): void
    {
        $this->request = new SendByTagRequest();
    }

    public function testGetRequestPath_returnsCorrectUrl(): void
    {
        $expectedUrl = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall';
        $this->assertEquals($expectedUrl, $this->request->getRequestPath());
    }

    public function testGetRequestOptions_returnsCorrectOptions_withRequiredFields(): void
    {
        // 设置必需的字段
        $tagId = '100';
        $message = [
            'msgtype' => 'text',
            'text' => [
                'content' => 'Test content',
            ],
        ];

        $this->request->setTagId($tagId);
        $this->request->setMessage($message);

        $expected = [
            'json' => [
                'filter' => [
                    'is_to_all' => false,
                    'tag_id' => $tagId,
                ],
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
        $tagId = '100';
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];
        $clientMsgId = 'client_msg_id_123';

        $this->request->setTagId($tagId);
        $this->request->setMessage($message);
        $this->request->setClientMsgId($clientMsgId);

        $options = $this->request->getRequestOptions();
        $this->assertArrayHasKey('clientmsgid', $options['json']);
        $this->assertEquals($clientMsgId, $options['json']['clientmsgid']);
    }

    public function testGetRequestOptions_setSendIgnoreReprint_toZero_whenFalse(): void
    {
        $tagId = '100';
        $message = ['msgtype' => 'text', 'text' => ['content' => 'Test']];

        $this->request->setTagId($tagId);
        $this->request->setMessage($message);
        $this->request->setSendIgnoreReprint(false);

        $options = $this->request->getRequestOptions();
        $this->assertEquals(0, $options['json']['send_ignore_reprint']);
    }

    public function testSetAndGetTagId_worksCorrectly(): void
    {
        $tagId = '100';
        $this->request->setTagId($tagId);
        $this->assertEquals($tagId, $this->request->getTagId());
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