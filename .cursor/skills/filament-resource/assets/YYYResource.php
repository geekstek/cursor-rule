<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Geekstek\XXX\Filament\Resources\YYY\Pages\CreateYYY;
use Geekstek\XXX\Filament\Resources\YYY\Pages\EditYYY;
use Geekstek\XXX\Filament\Resources\YYY\Pages\ListYYY; // 模型名(复数)
use Geekstek\XXX\Filament\Resources\YYY\Pages\ViewYYY;
use Geekstek\XXX\Filament\Resources\YYY\RelationManagers\ZZZManager; // 要开发 RelationManager 时, 加入对应的 RelationManager 类
use Geekstek\XXX\Filament\Resources\YYY\Schemas\YYYForm;
use Geekstek\XXX\Filament\Resources\YYY\Schemas\YYYInfolist;
use Geekstek\XXX\Filament\Resources\YYY\Tables\YYYTable; // 模型名(复数)
use Geekstek\XXX\Models\YYY; // 模型名
use Geekstek\Uacl\Attributes\ResourcePermissions;
use Geekstek\Uacl\Concerns\HasResourcePermission;

#[ResourcePermissions(permissions: [
    'view-any',
    'view',
    'update',
    'delete',
    'delete-any',
    'import',
    'export',
    'activity-log',
    '...' // 根据自定义的Filament Action 来加入权限
])]
class YYYResource extends Resource
{
    use HasResourcePermission;

    public const TRANSLATIONS = 'XXX::resources/YYY.';

    protected static ?string $model = YYY::class;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::User;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;

    protected static bool $isScopedToTenant = true; // 如果使用 Multi-Tenancy 数据隔离时使用

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'employee_number';

    public static function table(Table $table): Table
    {
        return YYYTable::configure($table); // 调用对应Resource 的 Table 定义类，模型名(复数)
    }

    public static function form(Schema $schema): Schema
    {
        return YYYForm::configure($schema); // 调用对应Resource 的 Form 定义类
    }

    public static function infolist(Schema $schema): Schema
    {
        return YYYInfolist::configure($schema); // 调用对应Resource 的 Infolist 定义类
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getRelations(): array
    {
        return [
            'zzz' => ZZZManager::class, // 要开发 RelationManager 时, 加入对应的 RelationManager 类
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListYYY::route('/'), // 模型名(复数)
            'create' => CreateYYY::route('/create'),
            'edit' => EditYYY::route('/{record}/edit'),
            'view' => ViewYYY::route('/{record}'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->name; // 要全局搜索的标题字段
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            // 要全局搜索的字段, 如: 'name', 'code', 'description'
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            // 要全局搜索的结果详情, 如: '名称' => $record->name, '编码' => $record->code, '描述' => $record->description
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('view')
                ->url(self::getUrl('view', ['record' => $record])),
            Action::make('edit')
                ->url(self::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('XXX::panels/navigation-group.labels.YYY'); // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)
    }

    public static function getNavigationLabel(): string
    {
        return __(self::TRANSLATIONS . 'labels.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __(self::TRANSLATIONS . 'labels.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __(self::TRANSLATIONS . 'labels.plural_model_label');
    }
}
