<?php

namespace App\Filament\Resources\Lots\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'businessProfiles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_category_id')->label('Categoria de negócio')->relationship('category', 'name')->required()->preload(),
                TextInput::make('target_audience_score')->label('Público-alvo')->required()->numeric()->minValue(0)->maxValue(100),
                TextInput::make('demand_density_score')->label('Demanda e densidade')->required()->numeric()->minValue(0)->maxValue(100),
                TextInput::make('income_fit_score')->label('Compatibilidade de renda')->required()->numeric()->minValue(0)->maxValue(100),
                TextInput::make('mobility_access_score')->label('Mobilidade e acesso')->required()->numeric()->minValue(0)->maxValue(100),
                TextInput::make('opportunity_gap_score')->label('Carência ou oportunidade')->required()->numeric()->minValue(0)->maxValue(100),
                Textarea::make('reasons')->label('Justificativas (uma por linha)')->required()->rows(4)->formatStateUsing(fn ($state) => is_array($state) ? implode(PHP_EOL, $state) : $state)->dehydrateStateUsing(fn ($state) => collect(preg_split('/\r\n|\r|\n/', $state))->filter()->values()->all())->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('category.name')
            ->columns([
                TextColumn::make('category.name')->label('Categoria')->sortable(),
                TextColumn::make('target_audience_score')->label('Público')->suffix('/100')->sortable(),
                TextColumn::make('demand_density_score')->label('Demanda')->suffix('/100')->sortable(),
                TextColumn::make('income_fit_score')->label('Renda')->suffix('/100')->sortable(),
                TextColumn::make('mobility_access_score')->label('Mobilidade')->suffix('/100')->sortable(),
                TextColumn::make('opportunity_gap_score')->label('Oportunidade')->suffix('/100')->sortable(),
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
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
