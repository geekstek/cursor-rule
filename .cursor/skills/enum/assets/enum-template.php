<?php

declare(strict_types=1);

namespace Geekstek\XXX\Enums; // 替换为实际的业务命名空间

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Geekstek\Support\Enums\Concerns\HasEnumHelpers;
use Illuminate\Contracts\Support\Htmlable;

/**
 * 枚举声明
 * **必须** 使用 `string` 类型的枚举值
 * **必须** 实现 `HasLabel` 接口
 * **必须** 使用 `HasEnumHelpers` trait
 * **判断** 是否需要实现 `HasDescription` 接口: 如果case不足以描述清楚语义时才实现，否则不实现
 * **判断** 是否需要实现 `HasIcon` 接口: 如果业务有明显的颜色语义则实现，否则不实现
 * **判断** 是否需要实现 `HasColor` 接口: 如果业务有明显的颜色语义则实现，否则不实现
 * 
 * 枚举类命名
 * - 使用 **PascalCase**
 * - 以具体业务含义命名，如 `PaymentStatus`, `BillStatus`
 * - 避免使用泛化名称如 `Status`, `Type`
 * 
 * 枚举值命名
 * - 使用 **SCREAMING_SNAKE_CASE**
 * - 使用清晰的业务术语
 * - 保持简洁但具有描述性
 * 
 * 避免使用泛化名称如 `Status`, `Type`
 * 
 */
enum XXX: string implements HasColor, HasDescription, HasLabel, HasIcon
{
    use HasEnumHelpers;

    private const TRANSLATIONS = 'package::enums/xxx-enum.'; // package 为 lower slug 业务命名空间名,替换为实际的翻译文件路径

    case PASS = 'pass';
    case FAIL = 'fail';

    public const DEFAULT = self::PASS;

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::PASS => '通过',
            self::FAIL => '未通过',
        };
    }

    public function getDescription(): string | Htmlable | null
    {
        return match ($this) {
            self::PASS => '通过',
            self::FAIL => '未通过',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::PASS => Heroicon::CheckCircle,
            self::FAIL => Heroicon::XCircle,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PASS => 'success',
            self::FAIL => 'danger',
        };
    }
}
