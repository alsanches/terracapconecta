<?php

namespace App\Filament\Resources\Lots\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação e destinação')->schema([
                    TextInput::make('code')->label('Código do lote')->required()->unique(ignoreRecord: true)->maxLength(80),
                    TextInput::make('title')->label('Título público')->required()->maxLength(180),
                    TextInput::make('address')->label('Endereço')->required()->columnSpanFull(),
                    TextInput::make('area_sqm')->label('Área')->suffix('m²')->required()->numeric()->minValue(1),
                    TextInput::make('zoning')->label('Zoneamento')->required(),
                    Textarea::make('destination')->label('Destinação permitida')->required()->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
                Section::make('Localização no mapa')->description('Informe o ponto do lote. A RA é conferida espacialmente antes da gravação.')->schema([
                    ViewField::make('location_picker')->label('Seleção visual')->view('filament.forms.components.location-picker')->dehydrated(false)->columnSpanFull(),
                    Select::make('administrative_region_id')->label('Região Administrativa')->relationship('region', 'name')->searchable()->preload()->required(),
                    TextInput::make('latitude')->required()->numeric()->minValue(-16.2)->maxValue(-15.4)->step('any'),
                    TextInput::make('longitude')->required()->numeric()->minValue(-48.4)->maxValue(-47.2)->step('any'),
                ])->columns(3)->columnSpanFull(),
                Section::make('Publicação')->schema([
                    Select::make('status')->label('Situação')->options(['draft' => 'Rascunho', 'published' => 'Publicado', 'withdrawn' => 'Retirado'])->required()->default('draft'),
                    DateTimePicker::make('published_at')->label('Publicado em')->seconds(false),
                    Toggle::make('is_demo')->label('Dado demonstrativo')->default(true)->required(),
                    Toggle::make('is_featured')->label('Destaque no mapa')->default(false)->required(),
                    Toggle::make('search_enabled')->label('Habilitado para busca')->default(false)->required(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
