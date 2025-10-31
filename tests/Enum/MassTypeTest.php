<?php

namespace WechatOfficialAccountMassBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatOfficialAccountMassBundle\Enum\MassType;

/**
 * @internal
 */
#[CoversClass(MassType::class)]
final class MassTypeTest extends AbstractEnumTestCase
{
    #[TestWith([MassType::TEXT, '1', '文本'])]
    #[TestWith([MassType::VOICE, '3', '语音'])]
    public function testEnumValueAndLabel(MassType $case, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $case->value);
        $this->assertEquals($expectedLabel, $case->getLabel());
    }

    public function testFromReturnsCorrectEnumWhenValidValueProvided(): void
    {
        $this->assertSame(MassType::TEXT, MassType::from('1'));
        $this->assertSame(MassType::VOICE, MassType::from('3'));
    }

    #[TestWith(['invalid'])]
    #[TestWith(['2'])]
    #[TestWith(['4'])]
    #[TestWith([''])]
    public function testFromThrowsExceptionWhenInvalidValueProvided(string $invalidValue): void
    {
        $this->expectException(\ValueError::class);
        MassType::from($invalidValue);
    }

    public function testTryFromReturnsCorrectEnumWhenValidValueProvided(): void
    {
        $this->assertSame(MassType::TEXT, MassType::tryFrom('1'));
        $this->assertSame(MassType::VOICE, MassType::tryFrom('3'));
    }

    #[TestWith(['invalid'])]
    #[TestWith(['2'])]
    #[TestWith(['4'])]
    #[TestWith([''])]
    public function testTryFromReturnsNullWhenInvalidValueProvided(string $invalidValue): void
    {
        $this->assertNull(MassType::tryFrom($invalidValue));
    }

    public function testValueUniqueness(): void
    {
        $values = array_map(fn (MassType $case) => $case->value, MassType::cases());
        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues, 'Enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (MassType $case) => $case->getLabel(), MassType::cases());
        $uniqueLabels = array_unique($labels);
        $this->assertCount(count($labels), $uniqueLabels, 'Enum labels must be unique');
    }

    public function testToArrayReturnsCorrectArray(): void
    {
        $expected = [
            'value' => MassType::TEXT->value,
            'label' => MassType::TEXT->getLabel(),
        ];
        $this->assertEquals($expected, MassType::TEXT->toArray());
    }

    public function testToSelectItemReturnsCorrectSelectItems(): void
    {
        $item = MassType::TEXT->toSelectItem();

        $this->assertIsArray($item);
        $this->assertCount(4, $item);

        $this->assertArrayHasKey('value', $item);
        $this->assertArrayHasKey('label', $item);
        $this->assertArrayHasKey('text', $item);
        $this->assertArrayHasKey('name', $item);

        $this->assertEquals(MassType::TEXT->value, $item['value']);
        $this->assertEquals(MassType::TEXT->getLabel(), $item['label']);
        $this->assertEquals(MassType::TEXT->getLabel(), $item['text']);
        $this->assertEquals(MassType::TEXT->getLabel(), $item['name']);
    }
}
