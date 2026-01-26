<?php

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class XXXInfolist
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
                        ->inlineLabel()
                        ->columns(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.name.label'))
                                ->placeholder('没有填写姓名'),
                            TextEntry::make('phone')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.phone.label'))
                                ->placeholder('没有填写联系电话'),
                        ])
                        ->columnSpanFull(),
                    Section::make('remark')
                        ->heading(__(static::$resourceClass::TRANSLATIONS . 'sections.remark.label'))
                        ->icon(Heroicon::DocumentText)
                        ->iconColor('gray')
                        ->inlineLabel()
                        ->schema([
                            TextEntry::make('remark')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.remark.label'))
                                ->placeholder('没有填写备注信息'),
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
                        ->inlineLabel()
                        ->columns(1)
                        ->schema([
                            IconEntry::make('is_primary')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.is_primary.label'))
                                ->trueIcon(Heroicon::HandThumbUp)
                                ->falseIcon(Heroicon::HandThumbDown)
                                ->trueColor('success')
                                ->falseColor('primary'),
                            IconEntry::make('is_active')
                                ->label(__(static::$resourceClass::TRANSLATIONS . 'fields.is_active.label'))
                                ->helperText('关闭后该联系人已失效')
                                ->trueIcon(Heroicon::Check)
                                ->falseIcon(Heroicon::XMark)
                                ->trueColor('success')
                                ->falseColor('danger'),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }
}