<?php

namespace App\Filament\Resources\Notices\Schemas;

use App\Models\Notice;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NoticeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code'),
                TextEntry::make('title'),
                TextEntry::make('modality'),
                TextEntry::make('opens_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('closes_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('document_path')
                    ->placeholder('-'),
                TextEntry::make('document_url')
                    ->placeholder('-'),
                IconEntry::make('is_demo')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Notice $record): bool => $record->trashed()),
            ]);
    }
}
