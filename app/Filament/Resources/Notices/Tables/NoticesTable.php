<?php

namespace App\Filament\Resources\Notices\Tables;

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

class NoticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Edital')->searchable()->sortable(),
                TextColumn::make('title')->label('Título')->searchable()->wrap(),
                TextColumn::make('items_count')->label('Itens')->counts('items')->sortable(),
                TextColumn::make('opens_at')->label('Abertura')->date('d/m/Y')->sortable(),
                TextColumn::make('closes_at')->label('Encerramento')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Situação')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'open' => 'Aberto', 'closed' => 'Encerrado', default => 'Rascunho'
                })->color(fn (string $state) => match ($state) {
                    'open' => 'success', 'closed' => 'danger', default => 'gray'
                }),
                IconColumn::make('is_demo')->label('Demonstração')->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
