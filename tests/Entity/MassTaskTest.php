<?php

namespace WechatOfficialAccountMassBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

class MassTaskTest extends TestCase
{
    private MassTask $massTask;

    protected function setUp(): void
    {
        $this->massTask = new MassTask();
    }

    // ID测试（只读属性）
    public function testGetId_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getId());
    }

    // Valid属性测试
    public function testValidProperty_defaultIsFalse(): void
    {
        $this->assertFalse($this->massTask->isValid());
    }

    public function testSetValid_returnsCorrectValue_whenSet(): void
    {
        $this->massTask->setValid(true);
        $this->assertTrue($this->massTask->isValid());
        
        $this->massTask->setValid(false);
        $this->assertFalse($this->massTask->isValid());
    }

    // Account关联测试
    public function testGetAccount_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getAccount());
    }

    public function testSetAccount_returnsCorrectValue_whenSet(): void
    {
        $account = $this->createMock(Account::class);
        $this->massTask->setAccount($account);
        $this->assertSame($account, $this->massTask->getAccount());
    }

    // Title属性测试
    public function testGetTitle_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getTitle());
    }

    public function testSetTitle_returnsCorrectValue_whenSet(): void
    {
        $title = 'Test Title';
        $this->massTask->setTitle($title);
        $this->assertEquals($title, $this->massTask->getTitle());
    }

    // TagId属性测试
    public function testGetTagId_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getTagId());
    }

    public function testSetTagId_returnsCorrectValue_whenSet(): void
    {
        $tagId = '12345';
        $this->massTask->setTagId($tagId);
        $this->assertEquals($tagId, $this->massTask->getTagId());
    }

    // OpenIds属性测试
    public function testGetOpenIds_returnsEmptyArray_byDefault(): void
    {
        $this->assertEquals([], $this->massTask->getOpenIds());
    }

    public function testSetOpenIds_returnsCorrectValue_whenSet(): void
    {
        $openIds = ['openid1', 'openid2', 'openid3'];
        $this->massTask->setOpenIds($openIds);
        $this->assertEquals($openIds, $this->massTask->getOpenIds());
    }

    // SendTime属性测试
    public function testGetSendTime_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getSendTime());
    }

    public function testSetSendTime_returnsCorrectValue_whenSet(): void
    {
        $sendTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setSendTime($sendTime);
        $this->assertSame($sendTime, $this->massTask->getSendTime());
    }

    // Type属性测试
    public function testGetType_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getType());
    }

    public function testSetType_returnsCorrectValue_whenSet(): void
    {
        $type = MassType::TEXT;
        $this->massTask->setType($type);
        $this->assertSame($type, $this->massTask->getType());
    }

    // Content属性测试
    public function testSetContent_returnsCorrectValue_whenSet(): void
    {
        $content = 'Test Content';
        $this->massTask->setContent($content);
        $this->assertEquals($content, $this->massTask->getContent());
    }

    // Sent属性测试
    public function testIsSent_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->isSent());
    }

    public function testSetSent_returnsCorrectValue_whenSet(): void
    {
        $this->massTask->setSent(true);
        $this->assertTrue($this->massTask->isSent());
        
        $this->massTask->setSent(false);
        $this->assertFalse($this->massTask->isSent());
    }

    // MediaId属性测试
    public function testGetMediaId_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getMediaId());
    }

    public function testSetMediaId_returnsCorrectValue_whenSet(): void
    {
        $mediaId = 'media_id_123';
        $this->massTask->setMediaId($mediaId);
        $this->assertEquals($mediaId, $this->massTask->getMediaId());
    }

    // MsgTaskId属性测试
    public function testGetMsgTaskId_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getMsgTaskId());
    }

    public function testSetMsgTaskId_returnsCorrectValue_whenSet(): void
    {
        $msgTaskId = 'msg_task_id_123';
        $this->massTask->setMsgTaskId($msgTaskId);
        $this->assertEquals($msgTaskId, $this->massTask->getMsgTaskId());
    }

    // MsgDataId属性测试
    public function testGetMsgDataId_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getMsgDataId());
    }

    public function testSetMsgDataId_returnsCorrectValue_whenSet(): void
    {
        $msgDataId = 'msg_data_id_123';
        $this->massTask->setMsgDataId($msgDataId);
        $this->assertEquals($msgDataId, $this->massTask->getMsgDataId());
    }

    // CreateTime属性测试
    public function testGetCreateTime_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getCreateTime());
    }

    public function testSetCreateTime_returnsCorrectValue_whenSet(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setCreateTime($createTime);
        $this->assertSame($createTime, $this->massTask->getCreateTime());
    }

    // UpdateTime属性测试
    public function testGetUpdateTime_returnsNull_whenNotSet(): void
    {
        $this->assertNull($this->massTask->getUpdateTime());
    }

    public function testSetUpdateTime_returnsCorrectValue_whenSet(): void
    {
        $updateTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->massTask->getUpdateTime());
    }

    // FormatMessage方法测试
    public function testFormatMessage_returnsTextMessage_whenTypeIsText(): void
    {
        $content = 'Test Content';
        $this->massTask->setType(MassType::TEXT);
        $this->massTask->setContent($content);

        $expectedResult = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
        ];

        $this->assertEquals($expectedResult, $this->massTask->formatMessage());
    }

    public function testFormatMessage_returnsVoiceMessage_whenTypeIsVoice(): void
    {
        $mediaId = 'media_id_123';
        $this->massTask->setType(MassType::VOICE);
        $this->massTask->setMediaId($mediaId);

        $expectedResult = [
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $mediaId,
            ],
        ];

        $this->assertEquals($expectedResult, $this->massTask->formatMessage());
    }

    public function testFormatMessage_returnsEmptyArray_whenTypeIsUndefined(): void
    {
        $this->assertEquals([], $this->massTask->formatMessage());
    }
} 