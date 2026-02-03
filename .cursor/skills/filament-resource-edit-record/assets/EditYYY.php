<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Pages; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Geekstek\XXX\Filament\Resources\YYY\ZZZResource; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数), ZZZ替换为模型名(单数)

class EditYYY extends EditRecord // YYY替换为模型名(单数)
{
    protected static string $resource = ZZZResource::class;

    protected ?bool $hasDatabaseTransactions = null;

    protected ?bool $hasUnsavedDataChangesAlert = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->closeModalByEscaping(false)
                ->closeModalByClickingAway(false),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
