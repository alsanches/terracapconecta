<?php

namespace App\Filament\Resources\AdministrativeRegions\Pages;

use App\Filament\Resources\AdministrativeRegions\AdministrativeRegionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdministrativeRegion extends EditRecord
{
    protected static string $resource = AdministrativeRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
