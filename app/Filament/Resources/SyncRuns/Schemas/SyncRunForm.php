<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SyncRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('data_source_id')
                    ->required()
                    ->numeric(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('mode')
                    ->required()
                    ->default('simulation'),
                TextInput::make('status')
                    ->required()
                    ->default('queued'),
                TextInput::make('received_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('imported_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('rejected_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
            ]);
    }
}
