<?php

namespace App\Filament\Resources\AdministrativeRegions\Pages;

use App\Filament\Resources\AdministrativeRegions\AdministrativeRegionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdministrativeRegion extends ViewRecord
{
    protected static string $resource = AdministrativeRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
