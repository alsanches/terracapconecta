<?php

namespace App\Filament\Resources\DataSources\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DataSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome da fonte')->required(),
                TextInput::make('slug')->label('Identificador')->required()->unique(ignoreRecord: true),
                TextInput::make('organization')->label('Órgão responsável')->required(),
                Select::make('source_type')->label('Tipo de acesso')->options(['manual' => 'Manual', 'csv' => 'Arquivo CSV', 'geojson_http' => 'GeoJSON HTTP', 'api_json' => 'API JSON'])->required(),
                Select::make('adapter_key')->label('Adaptador autorizado')->options([
                    'ipedf_regions_geojson' => 'IPEDF — limites das RAs',
                    'ipedf_pdad_demo' => 'IPEDF — PDAD demonstrativa',
                    'terracap_manual_demo' => 'Terracap — cadastro manual demonstrativo',
                    'mobility_gdf_future' => 'Mobilidade GDF — simulação futura',
                ])->required()->helperText('Uma URL sozinha não executa integração; é obrigatório escolher um adaptador previamente autorizado.'),
                TextInput::make('base_url')->label('Endereço da fonte')->url(),
                Select::make('frequency')->label('Periodicidade')->options(['manual' => 'Manual', 'diaria' => 'Diária', 'semanal' => 'Semanal', 'mensal' => 'Mensal', 'anual' => 'Anual'])->required()->default('manual'),
                Select::make('status')->label('Situação')->options(['draft' => 'Rascunho', 'active' => 'Ativa', 'paused' => 'Pausada'])->required()->default('draft'),
                TextInput::make('credential_reference')->label('Referência do segredo')->helperText('Informe apenas o nome do segredo do servidor, nunca a credencial.'),
                TextInput::make('contact')->label('Contato técnico'),
                DateTimePicker::make('last_synced_at')->label('Última sincronização')->disabled()->dehydrated(false),
                Textarea::make('notes')->label('Observações')->columnSpanFull(),
            ]);
    }
}
