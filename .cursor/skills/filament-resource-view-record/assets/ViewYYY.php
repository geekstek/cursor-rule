<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Pages; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Geekstek\XXX\Filament\Resources\YYY\ZZZResource; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数), ZZZ替换为模型名(单数)

class ViewYYY extends ViewRecord // YYY替换为模型名(单数)
{
    protected static string $resource = ZZZResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->closeModalByClickingAway(false)
                ->closeModalByEscaping(false),
        ];
    }
}
