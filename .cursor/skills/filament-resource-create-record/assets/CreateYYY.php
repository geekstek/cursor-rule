<?php

declare(strict_types=1);

namespace Geekstek\XXX\Filament\Resources\YYY\Pages; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数)

use Filament\Resources\Pages\CreateRecord;
use Geekstek\XXX\Filament\Resources\YYY\ZZZResource; // XXX替换为实际的业务命名空间, YYY替换为模型名(复数), ZZZ替换为模型名(单数)

class CreateYYY extends CreateRecord // YYY替换为模型名(单数)
{
    protected static string $resource = ZZZResource::class;

    protected static bool $canCreateAnother = false;

    protected ?bool $hasDatabaseTransactions = null;

    protected ?bool $hasUnsavedDataChangesAlert = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
