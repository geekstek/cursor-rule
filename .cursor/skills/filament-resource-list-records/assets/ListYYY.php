<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Pages; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Geekstek\XXX\Filament\Resources\YYY\ZZZResource; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数), ZZZ替换为模型名(单数)
use Geekstek\XXX\Models\AssetRateAssignment;
use Geekstek\Support\Filament\Tables\Concerns\HasAutoScrollToTop;
use Geekstek\Support\Filament\Tables\Concerns\HasTenantAwareSessionKeys;
use Geekstek\Support\Jobs\TransactionalImportCsv;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class ListYYY extends ListRecords // YYY替换为模型名(复数)
{
    use HasAutoScrollToTop;
    use HasTenantAwareSessionKeys;

    protected static string $resource = ZZZResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                SmartImportAction::make('importEmployeeProfile')
                    ->label('导入员工档案')
                    ->icon(Heroicon::ArrowDownTray)
                    ->importer(EmployeeProfileImporter::class)
                    ->job(TransactionalImportCsv::class)
                    ->templateFilePath('imports/员工档案导入模板.xlsx')
                    ->enableExcelToCsv(true)
                    ->chunkSize(1000)
                    ->authorize('import'),
                ExportAction::make()
                    ->label('导出')
                    ->icon(Heroicon::ArrowUpTray)
                    ->exporter(EmployeeProfileExporter::class)
                    ->enableVisibleTableColumnsByDefault()
                    ->columnMapping(false)
                    ->authorize('export'),
            ])
                ->label('更多')
                ->color('gray')
                ->button(),
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
