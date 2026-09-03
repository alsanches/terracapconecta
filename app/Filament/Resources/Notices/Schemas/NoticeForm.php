<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->label('Número do edital')->required()->unique(ignoreRecord: true),
                TextInput::make('title')->label('Título')->required()->columnSpanFull(),
                TextInput::make('modality')->label('Modalidade')->required()->default('Licitação pública'),
                DatePicker::make('opens_at')->label('Abertura'),
                DatePicker::make('closes_at')->label('Encerramento')->afterOrEqual('opens_at'),
                Select::make('status')->label('Situação')->options(['draft' => 'Rascunho', 'open' => 'Aberto', 'closed' => 'Encerrado'])->required()->default('draft'),
                Textarea::make('description')->label('Descrição')->columnSpanFull(),
                FileUpload::make('document_path')->label('PDF demonstrativo')->disk('public')->directory('notices')->acceptedFileTypes(['application/pdf'])->maxSize(10240),
                TextInput::make('document_url')->label('Link externo do documento')->url(),
                Toggle::make('is_demo')->label('Edital demonstrativo')->default(true)->required(),
            ]);
    }
}
