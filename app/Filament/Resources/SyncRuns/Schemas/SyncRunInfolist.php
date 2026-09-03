<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SyncRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('data_source_id')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('mode'),
                TextEntry::make('status'),
                TextEntry::make('received_count')
                    ->numeric(),
                TextEntry::make('imported_count')
                    ->numeric(),
                TextEntry::make('rejected_count')
                    ->numeric(),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('metadata')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('finished_at')
                    ->dateTime()
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
