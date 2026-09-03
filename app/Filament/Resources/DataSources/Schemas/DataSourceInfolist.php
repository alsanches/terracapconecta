<?php

namespace App\Filament\Resources\DataSources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DataSourceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('organization'),
                TextEntry::make('source_type'),
                TextEntry::make('adapter_key'),
                TextEntry::make('base_url')
                    ->placeholder('-'),
                TextEntry::make('frequency'),
                TextEntry::make('status'),
                TextEntry::make('credential_reference')
                    ->placeholder('-'),
                TextEntry::make('contact')
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
