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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('proposal_deadline')->label('Prazo principal')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Situação')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'open' => 'Aberto', 'in_result' => 'Em resultado', 'closed' => 'Encerrado', 'cancelled' => 'Cancelado', default => 'Rascunho'
                })->color(fn (string $state) => match ($state) {
                    'open' => 'success', 'in_result' => 'warning', 'closed', 'cancelled' => 'danger', default => 'gray'
                }),
                IconColumn::make('public_visible')->label('Público')->boolean(),
                IconColumn::make('is_demo')->label('Demonstração')->boolean(),
                TextColumn::make('source_checked_at')->label('Fonte conferida')->dateTime('d/m/Y H:i')->toggleable(),
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
                SelectFilter::make('status')->label('Situação')->options([
                    'draft' => 'Rascunho', 'open' => 'Aberto', 'in_result' => 'Em resultado',
                    'closed' => 'Encerrado', 'cancelled' => 'Cancelado',
                ]),
                TernaryFilter::make('public_visible')->label('Publicado no site'),
                TernaryFilter::make('is_demo')->label('Origem demonstrativa'),
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
