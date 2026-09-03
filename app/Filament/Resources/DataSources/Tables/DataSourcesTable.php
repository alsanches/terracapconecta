<?php

namespace App\Filament\Resources\DataSources\Tables;

use App\Jobs\SimulateDataSourceSync;
use App\Models\DataSource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Fonte')->searchable()->description(fn (DataSource $record) => $record->organization),
                TextColumn::make('source_type')->label('Acesso')->badge(),
                TextColumn::make('frequency')->label('Periodicidade'),
                TextColumn::make('status')->label('Situação')->badge()->color(fn (string $state) => match ($state) {
                    'active' => 'success', 'paused' => 'warning', default => 'gray'
                }),
                TextColumn::make('last_synced_at')->label('Última execução')->since()->placeholder('Nunca')->sortable(),
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
                Action::make('test')->label('Testar fonte')->icon('heroicon-o-beaker')->action(function (DataSource $record) {
                    $recognized = in_array($record->adapter_key, ['ipedf_regions_geojson', 'ipedf_pdad_demo', 'terracap_manual_demo', 'mobility_gdf_future'], true);
                    Notification::make()
                        ->title($recognized ? 'Adaptador e configuração reconhecidos' : 'Adaptador não autorizado')
                        ->body('O teste não consultou serviços externos.')
                        ->color($recognized ? 'success' : 'danger')
                        ->send();
                }),
                Action::make('simulate')->label('Executar simulação')->icon('heroicon-o-play')->requiresConfirmation()->action(function (DataSource $record) {
                    try {
                        SimulateDataSourceSync::dispatchSync($record->id, auth()->id());
                        Notification::make()->title('Simulação concluída')->body('O histórico da execução já está disponível.')->success()->send();
                    } catch (\Throwable $exception) {
                        Notification::make()->title('A simulação registrou uma falha')->body($exception->getMessage())->danger()->send();
                    }
                }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
