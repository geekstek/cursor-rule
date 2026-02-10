<?php

declare(strict_types=1);

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Geekstek\ActivityLog\Filament\Resources\ActivityLogs\Actions\ActivityLogTimelineTableAction;
use Geekstek\Administrative\Filament\Resources\EmployeeProfiles\EmployeeProfileResource;
use Geekstek\Administrative\Models\Community;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Support\Icons\Heroicon;

class CommunitiesTable
{
    public const TRANSLATIONS = 'community::resources/community.';

    public static function configure(Table $table, ?array $columns = null, ?array $filters = null): Table
    {
        $columns = $columns ?? self::getColumns();
        $filters = $filters ?? self::getFilters();

        return $table
            ->columns($columns)
            ->filters($filters)
            ->filtersFormSchema(fn (array $filters): array => self::getFiltersFormSchema($filters))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->closeModalByClickingAway(false),
                    EditAction::make()
                        ->closeModalByEscaping(false)
                        ->closeModalByClickingAway(false),
                    DeleteAction::make(),
                    ActivityLogTimelineTableAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->searchDebounce('1000ms')
            ->searchOnBlur()
            ->defaultSort('created_at', 'asc');
    }

    public static function getColumns(): array
    {
        return [
            // id字段必须按这格式
            TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('propertyType.name')
                ->label('物业类型')
                ->weight(FontWeight::Bold)
                ->searchable()
                ->toggleable(),
            TextColumn::make('name')
                ->label('社区名称')
                ->searchable()
                ->toggleable(),
            TextColumn::make('slug')
                ->label('社区编码')
                ->searchable()
                ->toggleable(),
            TextColumn::make('units_count')
                ->label('单元数量')
                ->numeric()
                ->sortable()
                ->color(fn (Community $record): string => $record->units_count > 0 ? 'primary' : 'gray')
                ->summarize(Sum::make()->label('单元数量'))
                ->toggleable(),
            // created_at 字段必须按这格式
            TextColumn::make('created_at')
                ->label('创建时间')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            // updated_at 字段必须按这格式
            TextColumn::make('updated_at')
                ->label('更新时间')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getFilters(): array
    {
        return [
            DateRangeFilter::make('created_at')
                ->label('创建时间')
                ->displayFormat('YYYY-MM-DD')
                ->format('Y-m-d'),
            DateRangeFilter::make('updated_at')
                ->label('更新时间')
                ->displayFormat('YYYY-MM-DD')
                ->format('Y-m-d'),
        ];
    }

    /**
 * 筛选表单布局配置
 *
 * 设计原则:
 * - 瀑布式布局：两个 Grid::make(1) 分左/右栏，全局 filtersFormColumns(2) 自动实现两栏
 * - 零 Description：Section 标题精准，配合 Icon + Color 传递意图
 * - Heroicon Enum：使用实心图标 (Heroicon::XXX)，非线框图标
 *
 * @param  array<string, \Filament\Tables\Filters\BaseFilter>  $filters
 */
public static function getFiltersFormSchema(array $filters): array
{
    return [
        // ===== 左栏 =====
        Grid::make(1)->schema([
            Section::make('状态筛选')
                ->icon(Heroicon::CheckCircle)
                ->iconColor('success')
                ->compact()
                ->schema([
                    $filters['status'],
                ]),
        ]),

        // ===== 右栏 =====
        Grid::make(1)->schema([
            Section::make('时间筛选')
                ->icon(Heroicon::Clock)
                ->iconColor('gray')
                ->compact()
                ->schema([
                    $filters['created_at'],
                    $filters['updated_at'],
                ]),
        ]),
    ];
}
}
