<?php

namespace App\Filament\Resources\AdministrativeRegions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdministrativeRegionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('data_source_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('official_code'),
                TextEntry::make('slug'),
                TextEntry::make('name'),
                TextEntry::make('area_sq_km')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('center_latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('center_longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('geometry_json')
                    ->columnSpanFull(),
                TextEntry::make('source_version')
                    ->placeholder('-'),
                TextEntry::make('source_url')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
