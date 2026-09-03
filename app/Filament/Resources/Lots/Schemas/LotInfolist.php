<?php

namespace App\Filament\Resources\Lots\Schemas;

use App\Models\Lot;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('administrative_region_id')
                    ->numeric(),
                TextEntry::make('code'),
                TextEntry::make('title'),
                TextEntry::make('address'),
                TextEntry::make('area_sqm')
                    ->numeric(),
                TextEntry::make('zoning'),
                TextEntry::make('destination'),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                TextEntry::make('boundary_json')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                IconEntry::make('is_demo')
                    ->boolean(),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('search_enabled')
                    ->boolean(),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Lot $record): bool => $record->trashed()),
            ]);
    }
}
