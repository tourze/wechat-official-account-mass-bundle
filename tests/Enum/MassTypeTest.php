<?php

namespace WechatOfficialAccountMassBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMassBundle\Enum\MassType;

class MassTypeTest extends TestCase
{
    // 测试枚举值
    public function testEnumValues_areCorrect(): void
    {
        $this->assertEquals('1', MassType::TEXT->value);
        $this->assertEquals('3', MassType::VOICE->value);
    }

    // 测试getLabel方法
    public function testGetLabel_returnsCorrectLabel_forTextType(): void
    {
        $this->assertEquals('文本', MassType::TEXT->getLabel());
    }

    public function testGetLabel_returnsCorrectLabel_forVoiceType(): void
    {
        $this->assertEquals('语音', MassType::VOICE->getLabel());
    }

    // 测试from静态方法
    public function testFrom_returnsCorrectEnum_whenValidValueProvided(): void
    {
        $this->assertSame(MassType::TEXT, MassType::from('1'));
        $this->assertSame(MassType::VOICE, MassType::from('3'));
    }

    public function testFrom_throwsException_whenInvalidValueProvided(): void
    {
        $this->expectException(\ValueError::class);
        MassType::from('invalid');
    }

    // 测试tryFrom静态方法
    public function testTryFrom_returnsCorrectEnum_whenValidValueProvided(): void
    {
        $this->assertSame(MassType::TEXT, MassType::tryFrom('1'));
        $this->assertSame(MassType::VOICE, MassType::tryFrom('3'));
    }

    public function testTryFrom_returnsNull_whenInvalidValueProvided(): void
    {
        $this->assertNull(MassType::tryFrom('invalid'));
    }
} 