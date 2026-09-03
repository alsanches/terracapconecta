<?php

namespace App\Filament\Resources\AdministrativeRegions\Pages;

use App\Filament\Resources\AdministrativeRegions\AdministrativeRegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdministrativeRegions extends ListRecords
{
    protected static string $resource = AdministrativeRegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
