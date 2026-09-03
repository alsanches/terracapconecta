<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotResource;
use App\Services\RegionLocator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditLot extends EditRecord
{
    protected static string $resource = LotResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $region = app(RegionLocator::class)->locate((float) $data['latitude'], (float) $data['longitude']);
        if (! $region) {
            throw ValidationException::withMessages(['data.latitude' => 'O ponto informado está fora dos limites oficiais do Distrito Federal.']);
        }
        if ($data['status'] === 'published' && ! $this->record->noticeItems()->where('status', 'open')->exists()) {
            throw ValidationException::withMessages(['data.status' => 'Associe um item de edital em oferta antes de publicar o lote.']);
        }
        $data['administrative_region_id'] = $region->id;
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? now()) : null;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
