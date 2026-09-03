<?php

namespace App\Filament\Resources\AdministrativeRegions;

use App\Filament\Resources\AdministrativeRegions\Pages\ListAdministrativeRegions;
use App\Filament\Resources\AdministrativeRegions\Pages\ViewAdministrativeRegion;
use App\Filament\Resources\AdministrativeRegions\Schemas\AdministrativeRegionForm;
use App\Filament\Resources\AdministrativeRegions\Schemas\AdministrativeRegionInfolist;
use App\Filament\Resources\AdministrativeRegions\Tables\AdministrativeRegionsTable;
use App\Models\AdministrativeRegion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdministrativeRegionResource extends Resource
{
    protected static ?string $model = AdministrativeRegion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Regiões Administrativas';

    protected static ?string $modelLabel = 'Região Administrativa';

    protected static ?string $pluralModelLabel = 'Regiões Administrativas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AdministrativeRegionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdministrativeRegionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdministrativeRegionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdministrativeRegions::route('/'),
            'view' => ViewAdministrativeRegion::route('/{record}'),
        ];
    }
}
