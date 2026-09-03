<?php

namespace App\Filament\Resources\DataSources\Pages;

use App\Filament\Resources\DataSources\DataSourceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDataSource extends ViewRecord
{
    protected static string $resource = DataSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
