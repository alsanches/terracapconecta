<?php

namespace App\Filament\Resources\AdministrativeRegions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdministrativeRegionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('official_code')->label('Código')->searchable()->sortable(),
                TextColumn::make('name')->label('Região Administrativa')->searchable()->sortable(),
                TextColumn::make('area_sq_km')->label('Área')->suffix(' km²')->numeric(locale: 'pt_BR')->sortable(),
                TextColumn::make('lots_count')->label('Lotes')->counts('lots')->sortable(),
                TextColumn::make('source.name')->label('Origem')->searchable(),
                TextColumn::make('source_version')->label('Versão')->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
