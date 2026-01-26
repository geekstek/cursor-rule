<?php

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class XXXForm
{
    public static string $resourceClass = XXXResource::class; //用于统一翻译键前缀，遵循 `resources/lang/**/resources/<resource>.*`

    public static function configure(Schema $schema, ?array $components = null): Schema
    {
        $components = $components ?? self::getComponents();

        return $schema
            ->components($components)
            ->columns(3); // 1. 全局开启 3 列
    }

    public static function getComponents(): array
    {
        return [
            // --- 左侧：核心内容 (2/3) ---
            Grid::make()
                ->columnSpan(2) // 占据 2 列
                ->schema([
                    // Primary Section, Warning Section 等
                    Section::make('basic')
                        ->heading(__(static::$resourceClass::TRANSLATIONS . 'sections.basic.label'))
                        ->icon(Heroicon::Phone)
                        ->iconColor('primary')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.name.label'))
                                ->required()
                                ->maxLength(255)
                                ->placeholder('请输入联系人姓名'),
                            Select::make('relationship')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.relationship.label'))
                                ->options([
                                    '父亲' => '父亲',
                                    '母亲' => '母亲',
                                    '配偶' => '配偶',
                                    '其他' => '其他',
                                ])
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('请选择与员工关系'),
                            TextInput::make('phone')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.phone.label'))
                                ->tel()
                                ->required()
                                ->maxLength(20)
                                ->placeholder('请输入联系电话'),
                        ])
                        ->columnSpanFull(),
                    Section::make('remark')
                        ->heading(__(self::TRANSLATIONS . 'sections.remark.label'))
                        ->icon(Heroicon::DocumentText)
                        ->iconColor('gray')
                        ->schema([
                            Textarea::make('remark')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.remark.label'))
                                ->rows(3)
                                ->placeholder('请输入备注信息（选填）'),
                        ])
                        ->columnSpanFull(),
                ]),

            // --- 右侧：侧边栏设置 (1/3) ---
            Grid::make()
                ->columnSpan(1) // 占据 1 列
                ->schema([
                    // Gray Section, Info Section 等
                    Section::make('status')
                        ->heading(__(static::$resourceClass::TRANSLATIONS . 'sections.status.label'))
                        ->icon(Heroicon::CheckCircle)
                        ->iconColor('success')
                        ->columns(1)
                        ->schema([
                            Toggle::make('is_primary')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.is_primary.label'))
                                ->helperText('主要联系的人')
                                ->onIcon(Heroicon::HandThumbUp)
                                ->offIcon(Heroicon::HandThumbDown)
                                ->onColor('success')
                                ->offColor('primary')
                                ->default(false),
                            Toggle::make('is_active')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.is_active.label'))
                                ->helperText('关闭后该联系人已失效')
                                ->onIcon(Heroicon::Check)
                                ->offIcon(Heroicon::XMark)
                                ->onColor('success')
                                ->offColor('danger')
                                ->default(true),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }
}