<?php

namespace App\Filament\Resources\SyncRuns\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SyncRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source.name')->label('Fonte')->searchable()->sortable(),
                TextColumn::make('mode')->label('Modo')->badge()->formatStateUsing(fn () => 'Simulação'),
                TextColumn::make('status')->label('Resultado')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                    'success' => 'Sucesso', 'failed' => 'Falha', 'running' => 'Em execução', default => 'Na fila'
                })->color(fn (string $state) => match ($state) {
                    'success' => 'success', 'failed' => 'danger', 'running' => 'info', default => 'gray'
                }),
                TextColumn::make('received_count')->label('Recebidos')->numeric()->sortable(),
                TextColumn::make('imported_count')->label('Importados')->numeric()->sortable(),
                TextColumn::make('rejected_count')->label('Rejeitados')->numeric()->sortable(),
                TextColumn::make('started_at')->label('Início')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('finished_at')->label('Fim')->dateTime('d/m/Y H:i:s')->sortable(),
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
