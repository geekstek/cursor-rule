<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Pages; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconSize;
use Geekstek\XXX\Enums\AssetRateCycle;
use Geekstek\XXX\Filament\Resources\YYY\ZZZResource; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数), ZZZ替换为模型名(单数)
use Geekstek\XXX\Models\AssetRateAssignment;
use Geekstek\Support\Filament\Tables\Concerns\HasAutoScrollToTop;
use Geekstek\Support\Filament\Tables\Concerns\HasTenantAwareSessionKeys;
use Geekstek\Support\Jobs\TransactionalImportCsv;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListYYY extends ListRecords // YYY替换为模型名(复数)
{
    use HasAutoScrollToTop;
    use HasTenantAwareSessionKeys;

    protected static string $resource = ZZZResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ActionGroup::make([
            //     SmartImportAction::make('importEmployeeProfile')
            //         ->label('导入员工档案')
            //         ->icon(Heroicon::ArrowDownTray)
            //         ->importer(EmployeeProfileImporter::class)
            //         ->job(TransactionalImportCsv::class)
            //         ->templateFilePath('imports/员工档案导入模板.xlsx')
            //         ->enableExcelToCsv(true)
            //         ->chunkSize(1000),
            //     ExportAction::make()
            //         ->label('导出')
            //         ->icon(Heroicon::ArrowUpTray)
            //         ->exporter(EmployeeProfileExporter::class)
            //         ->columnMapping(false),
            // ])
            //     ->label('更多')
            //     ->color('gray')
            //     ->button(),
            CreateAction::make()
                ->createAnother(false)
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false),
        ];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate($this->getTableRecordsPerPage())->onEachSide(2);
    }

    public function getTabs(): array
    {
        return [];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return array_key_first($this->getCachedTabs());
    }
}
