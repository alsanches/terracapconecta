<?php

namespace App\Filament\Resources\Lots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable(),
                TextColumn::make('title')->label('Oportunidade')->searchable()->description(fn ($record) => $record->address)->wrap(),
                TextColumn::make('region.name')->label('RA')->searchable()->sortable(),
                TextColumn::make('area_sqm')->label('Área')->suffix(' m²')->numeric(locale: 'pt_BR')->sortable(),
                TextColumn::make('status')->label('Situação')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'published' => 'Publicado', 'withdrawn' => 'Retirado', default => 'Rascunho'
                })->color(fn (string $state) => match ($state) {
                    'published' => 'success', 'withdrawn' => 'danger', default => 'gray'
                }),
                IconColumn::make('is_featured')->label('Destaque')->boolean(),
                IconColumn::make('search_enabled')->label('Busca')->boolean(),
                TextColumn::make('published_at')->label('Publicação')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Excluído em')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
