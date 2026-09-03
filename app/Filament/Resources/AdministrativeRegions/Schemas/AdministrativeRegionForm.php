<?php

namespace App\Filament\Resources\AdministrativeRegions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdministrativeRegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('data_source_id')
                    ->numeric(),
                TextInput::make('official_code')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('area_sq_km')
                    ->numeric(),
                TextInput::make('center_latitude')
                    ->numeric(),
                TextInput::make('center_longitude')
                    ->numeric(),
                Textarea::make('geometry_json')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('source_version'),
                TextInput::make('source_url')
                    ->url(),
            ]);
    }
}
