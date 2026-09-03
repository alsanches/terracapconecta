<?php

namespace App\Filament\Resources\SyncRuns\Pages;

use App\Filament\Resources\SyncRuns\SyncRunResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSyncRun extends EditRecord
{
    protected static string $resource = SyncRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
