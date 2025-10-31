<?php

namespace WechatOfficialAccountMassBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMassBundle\Entity\MassTask;
use WechatOfficialAccountMassBundle\Enum\MassType;

/**
 * @internal
 */
#[CoversClass(MassTask::class)]
final class MassTaskTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new MassTask();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'content' => ['content', 'test_value'],
            'openIds' => ['openIds', ['key' => 'value']],
        ];
    }

    private MassTask $massTask;

    protected function setUp(): void
    {
        parent::setUp();

        // MassTask 是一个实体类，不是服务，直接实例化是合理的做法
        $this->massTask = new MassTask();
    }

    // ID测试（只读属性）
    public function testGetIdReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getId());
    }

    // Valid属性测试
    public function testValidPropertyDefaultIsFalse(): void
    {
        $this->assertFalse($this->massTask->isValid());
    }

    public function testSetValidReturnsCorrectValueWhenSet(): void
    {
        $this->massTask->setValid(true);
        $this->assertTrue($this->massTask->isValid());

        $this->massTask->setValid(false);
        $this->assertFalse($this->massTask->isValid());
    }

    // Account关联测试
    public function testGetAccountReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getAccount());
    }

    public function testSetAccountReturnsCorrectValueWhenSet(): void
    {
        // 必须Mock具体类Account因为：
        // 1. MassTask需要与Account实体建立关联关系，验证设置和获取行为
        // 2. Account实体包含特定的微信账号信息，测试需要隔离数据库依赖
        // 3. Mock实体类有助于单元测试聚焦业务逻辑而非数据持久化
        $account = $this->createMock(Account::class);
        $this->massTask->setAccount($account);
        $this->assertSame($account, $this->massTask->getAccount());
    }

    // Title属性测试
    public function testGetTitleReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getTitle());
    }

    public function testSetTitleReturnsCorrectValueWhenSet(): void
    {
        $title = 'Test Title';
        $this->massTask->setTitle($title);
        $this->assertEquals($title, $this->massTask->getTitle());
    }

    // TagId属性测试
    public function testGetTagIdReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getTagId());
    }

    public function testSetTagIdReturnsCorrectValueWhenSet(): void
    {
        $tagId = '12345';
        $this->massTask->setTagId($tagId);
        $this->assertEquals($tagId, $this->massTask->getTagId());
    }

    // OpenIds属性测试
    public function testGetOpenIdsReturnsEmptyArrayByDefault(): void
    {
        $this->assertEquals([], $this->massTask->getOpenIds());
    }

    public function testSetOpenIdsReturnsCorrectValueWhenSet(): void
    {
        $openIds = ['openid1', 'openid2', 'openid3'];
        $this->massTask->setOpenIds($openIds);
        $this->assertEquals($openIds, $this->massTask->getOpenIds());
    }

    // SendTime属性测试
    public function testGetSendTimeReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getSendTime());
    }

    public function testSetSendTimeReturnsCorrectValueWhenSet(): void
    {
        $sendTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setSendTime($sendTime);
        $this->assertSame($sendTime, $this->massTask->getSendTime());
    }

    // Type属性测试
    public function testGetTypeReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getType());
    }

    public function testSetTypeReturnsCorrectValueWhenSet(): void
    {
        $type = MassType::TEXT;
        $this->massTask->setType($type);
        $this->assertSame($type, $this->massTask->getType());
    }

    // Content属性测试
    public function testSetContentReturnsCorrectValueWhenSet(): void
    {
        $content = 'Test Content';
        $this->massTask->setContent($content);
        $this->assertEquals($content, $this->massTask->getContent());
    }

    // Sent属性测试
    public function testIsSentReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->isSent());
    }

    public function testSetSentReturnsCorrectValueWhenSet(): void
    {
        $this->massTask->setSent(true);
        $this->assertTrue($this->massTask->isSent());

        $this->massTask->setSent(false);
        $this->assertFalse($this->massTask->isSent());
    }

    // MediaId属性测试
    public function testGetMediaIdReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getMediaId());
    }

    public function testSetMediaIdReturnsCorrectValueWhenSet(): void
    {
        $mediaId = 'media_id_123';
        $this->massTask->setMediaId($mediaId);
        $this->assertEquals($mediaId, $this->massTask->getMediaId());
    }

    // MsgTaskId属性测试
    public function testGetMsgTaskIdReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getMsgTaskId());
    }

    public function testSetMsgTaskIdReturnsCorrectValueWhenSet(): void
    {
        $msgTaskId = 'msg_task_id_123';
        $this->massTask->setMsgTaskId($msgTaskId);
        $this->assertEquals($msgTaskId, $this->massTask->getMsgTaskId());
    }

    // MsgDataId属性测试
    public function testGetMsgDataIdReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getMsgDataId());
    }

    public function testSetMsgDataIdReturnsCorrectValueWhenSet(): void
    {
        $msgDataId = 'msg_data_id_123';
        $this->massTask->setMsgDataId($msgDataId);
        $this->assertEquals($msgDataId, $this->massTask->getMsgDataId());
    }

    // CreateTime属性测试
    public function testGetCreateTimeReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getCreateTime());
    }

    public function testSetCreateTimeReturnsCorrectValueWhenSet(): void
    {
        $createTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setCreateTime($createTime);
        $this->assertSame($createTime, $this->massTask->getCreateTime());
    }

    // UpdateTime属性测试
    public function testGetUpdateTimeReturnsNullWhenNotSet(): void
    {
        $this->assertNull($this->massTask->getUpdateTime());
    }

    public function testSetUpdateTimeReturnsCorrectValueWhenSet(): void
    {
        $updateTime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $this->massTask->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->massTask->getUpdateTime());
    }

    // FormatMessage方法测试
    public function testFormatMessageReturnsTextMessageWhenTypeIsText(): void
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

    public function testFormatMessageReturnsVoiceMessageWhenTypeIsVoice(): void
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

    public function testFormatMessageReturnsEmptyArrayWhenTypeIsUndefined(): void
    {
        $this->assertEquals([], $this->massTask->formatMessage());
    }
}
