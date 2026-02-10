<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Imports; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Geekstek\XXX\Enums\YYYStatus; // 替换为实际的枚举类
use Geekstek\XXX\Models\RelatedModel; // 替换为实际的关联模型
use Geekstek\XXX\Models\YYY; // XXX替换为实际的业务命名空间, YYY替换为模型名(单数)
use Geekstek\Support\Filament\Imports\BaseImporter;
use Illuminate\Validation\Rule;

class YYYImporter extends BaseImporter // YYY替换为模型名(单数)
{
    public const TRANSLATIONS = 'xxx::resources/yyy.'; // 替换为实际的翻译前缀

    protected static ?string $model = YYY::class;

    public static function getColumns(): array
    {
        return [
            // ==================== 基础字段 ====================
            ImportColumn::make('name')
                ->label(__(self::TRANSLATIONS . 'fields.name.label'))
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            // ==================== BelongsTo 关联 (使用 relationship) ====================
            ImportColumn::make('related_model')
                ->label(__(self::TRANSLATIONS . 'fields.related_model_id.label'))
                ->requiredMapping()
                ->relationship(resolveUsing: function (string $state, array $options): ?RelatedModel {
                    return RelatedModel::query()
                        ->where('name', $state)
                        ->where('tenant_id', $options['tenant_id'] ?? null)
                        ->first();
                }),

            // ==================== 非 BelongsTo 关联 (手动解析) ====================
            // 当关联关系不是 BelongsTo/BelongsToMany 时, 使用 fillRecordUsing 手动查询
            ImportColumn::make('related_model_name')
                ->label(__(self::TRANSLATIONS . 'fields.related_model_id.label'))
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                ])
                ->fillRecordUsing(function ($state, $record, $options) {
                    $relatedModel = RelatedModel::query()
                        ->where('name', $state)
                        ->where('tenant_id', $options['tenant_id'] ?? null)
                        ->first();

                    if (! $relatedModel) {
                        throw new RowImportFailedException("找不到关联记录：{$state}");
                    }

                    $record->related_model_id = $relatedModel->id;
                }),

            // ==================== 枚举字段 ====================
            // 如果是必填, 使用 requiredMapping() > castStateUsing() > Rule::in(Enum::getValuesArray())
            // 使用 castStateUsing() 转换为枚举值
            // 使用 Rule::in(Enum::getValuesArray()) 验证枚举值
            ImportColumn::make('status')
                ->label(__(self::TRANSLATIONS . 'fields.status.label'))
                ->requiredMapping()
                ->castStateUsing(function ($state): ?string {
                    return YYYStatus::fromLabel($state)->value;
                })
                ->rules([
                    'required',
                    Rule::in(YYYStatus::getValuesArray()),
                ]),
            
            // 如果是可空
            ImportColumn::make('status')
                ->label(__(self::TRANSLATIONS . 'fields.status.label'))
                ->castStateUsing(function ($state): ?string {
                    // 如果为空, 需要返回默认值时 "二选一"
                    if (blank($state)) { 
                        return YYYStatus::DEFAULT->value;
                    }

                    // 或返回 null "二选一"
                    // if (blank($state)) {
                    //     return null;
                    // }

                    return YYYStatus::fromLabel($state)->value;
                })
                ->rules([
                    'nullable',
                    Rule::in(YYYStatus::getValuesArray()),
                ]),

            // ==================== 布尔字段 ====================
            ImportColumn::make('is_active')
                ->label(__(self::TRANSLATIONS . 'fields.is_active.label'))
                ->castStateUsing(function ($state): ?bool {
                    // 如果为空代表启用时
                    if (blank($state)) {
                        return true;
                    }

                    return $state === '是';
                })
                ->rules([
                    'nullable',
                    'in:是,否',
                ]),

            // ==================== 数值字段 ====================
            ImportColumn::make('amount')
                ->label(__(self::TRANSLATIONS . 'fields.amount.label'))
                ->numeric()
                ->rules([
                    'nullable',
                    'numeric',
                    'min:0',
                ]),

            // ==================== 日期字段 ====================
            ImportColumn::make('effective_date')
                ->label(__(self::TRANSLATIONS . 'fields.effective_date.label'))
                ->rules([
                    'nullable',
                    'date',
                    'date_format:Y-m-d',
                ]),

            // ==================== No-op 列 (仅供 resolveRecord 使用) ====================
            // 该列仅在 resolveRecord() 中通过 $this->data 读取, 不写入目标 Model
            // ImportColumn::make('lookup_field')
            //     ->label('查找字段')
            //     ->rules(['required', 'string'])
            //     ->fillRecordUsing(function () {
            //         // no-op：仅用于 resolveRecord() 中匹配记录
            //     }),

            // ==================== JSON 字段 ====================
            ImportColumn::make('meta')
                ->label(__(self::TRANSLATIONS . 'fields.meta.label'))
                ->castStateUsing(function ($state): ?array {
                    return json_decode($state, true);
                })
                ->rules([
                    'nullable',
                    'json',
                ]),
        ];
    }

    public function resolveRecord(): ?YYY
    {
        // 方式 A: 仅创建新记录
        // return new YYY();

        // 方式 B: 更新已有记录或创建新记录 (通过唯一键匹配)
        // return YYY::firstOrNew([
        //     'tenant_id' => $this->options['tenant_id'],
        //     'name' => $this->data['name'],
        // ]);

        // 方式 C: 仅更新已有记录, 找不到时标记失败
        // $record = YYY::query()
        //     ->where('tenant_id', $this->options['tenant_id'])
        //     ->where('code', $this->data['code'])
        //     ->first();
        //
        // if (! $record) {
        //     throw new RowImportFailedException("找不到记录：{$this->data['code']}");
        // }
        //
        // return $record;

        return new YYY();
    }

    /**
     * 导入选项表单 -- 传递租户 ID
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Hidden::make('tenant_id')
                ->default(Filament::getTenant()?->getKey()),
        ];
    }

    /**
     * 保存前钩子 -- 设置租户 ID
     */
    protected function beforeSave(): void
    {
        if ($this->options['tenant_id'] ?? false) {
            $this->getRecord()->tenant_id = $this->options['tenant_id'];
        }
    }

    /**
     * 异常兜底 -- 将底层异常转换为行级失败消息
     */
    public function saveRecord(): void
    {
        try {
            parent::saveRecord();
        } catch (\Throwable $e) {
            throw new RowImportFailedException('保存记录失败：' . $e->getMessage());
        }
    }

    /**
     * 导入名称 -- 用于通知消息
     */
    protected static function getImportName(): string
    {
        return __(self::TRANSLATIONS . 'labels.navigation_label');
    }
}
