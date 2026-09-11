<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')->schema([
                    TextInput::make('code')->label('Número do edital')->required()->unique(ignoreRecord: true),
                    TextInput::make('title')->label('Título')->required()->columnSpanFull(),
                    TextInput::make('modality')->label('Modalidade')->required()->default('Licitação pública'),
                    TextInput::make('procedure_type')->label('Tipo de procedimento'),
                    TextInput::make('process_number')->label('Processo administrativo'),
                    Textarea::make('description')->label('Descrição')->columnSpanFull(),
                    Textarea::make('regions_summary')->label('Regiões abrangidas')->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                Section::make('Prazos')->schema([
                    DatePicker::make('published_on')->label('Data de publicação'),
                    DatePicker::make('opens_at')->label('Abertura'),
                    DatePicker::make('closes_at')->label('Encerramento')->afterOrEqual('opens_at'),
                    DatePicker::make('deposit_deadline')->label('Prazo de caução'),
                    DatePicker::make('proposal_deadline')->label('Prazo de proposta'),
                    DateTimePicker::make('auction_at')->label('Data da licitação')->seconds(false),
                ])->columns(3)->columnSpanFull(),
                Section::make('Publicação e origem')->schema([
                    Select::make('status')->label('Situação')->options([
                        'draft' => 'Rascunho', 'open' => 'Aberto', 'in_result' => 'Em resultado',
                        'closed' => 'Encerrado', 'cancelled' => 'Cancelado',
                    ])->required()->default('draft'),
                    Toggle::make('public_visible')->label('Publicado no site')->default(false),
                    Toggle::make('is_demo')->label('Origem demonstrativa')->default(true)->required(),
                    DateTimePicker::make('source_checked_at')->label('Última conferência da fonte')->seconds(false),
                ])->columns(2)->columnSpanFull(),
                Section::make('Documentos e canais oficiais')->schema([
                    FileUpload::make('document_path')->label('PDF local')->disk('public')->directory('notices')->acceptedFileTypes(['application/pdf'])->maxSize(10240),
                    TextInput::make('document_url')->label('Documento oficial')->url()->maxLength(2048),
                    TextInput::make('official_page_url')->label('Página oficial')->url()->maxLength(2048),
                    TextInput::make('proposal_url')->label('Canal oficial de proposta')->url()->maxLength(2048),
                    TextInput::make('result_url')->label('Página de resultado')->url()->maxLength(2048),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
