<?php

namespace WechatOfficialAccountMassBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 群发类型
 */
enum MassType: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case TEXT = '1';

    // case IMAGE = '2';
    case VOICE = '3';
    // case VIDEO = '4';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => '文本',
            // self::IMAGE => '图片',
            self::VOICE => '语音',
            // self::VIDEO => '视频',
        };
    }
}
