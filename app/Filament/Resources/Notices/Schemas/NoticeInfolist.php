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
                TextEntry::make('procedure_type')->label('Procedimento')->placeholder('-'),
                TextEntry::make('process_number')->label('Processo')->placeholder('-'),
                TextEntry::make('opens_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('closes_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status'),
                IconEntry::make('public_visible')->label('Publicado no site')->boolean(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('document_path')
                    ->placeholder('-'),
                TextEntry::make('document_url')
                    ->placeholder('-'),
                TextEntry::make('official_page_url')->label('Página oficial')->url(fn ($record) => $record->official_page_url)->openUrlInNewTab()->placeholder('-'),
                TextEntry::make('proposal_url')->label('Canal de proposta')->url(fn ($record) => $record->proposal_url)->openUrlInNewTab()->placeholder('-'),
                TextEntry::make('result_url')->label('Resultado')->url(fn ($record) => $record->result_url)->openUrlInNewTab()->placeholder('-'),
                TextEntry::make('regions_summary')->label('Regiões')->placeholder('-')->columnSpanFull(),
                TextEntry::make('source_checked_at')->label('Fonte conferida')->dateTime('d/m/Y H:i')->placeholder('-'),
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
