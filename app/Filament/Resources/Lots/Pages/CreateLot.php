<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotResource;
use App\Services\RegionLocator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateLot extends CreateRecord
{
    protected static string $resource = LotResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['status'] === 'published') {
            throw ValidationException::withMessages(['data.status' => 'Cadastre o lote como rascunho, associe-o a um item de edital e então publique.']);
        }

        $region = app(RegionLocator::class)->locate((float) $data['latitude'], (float) $data['longitude']);
        if (! $region) {
            throw ValidationException::withMessages(['data.latitude' => 'O ponto informado está fora dos limites oficiais do Distrito Federal.']);
        }
        $data['administrative_region_id'] = $region->id;

        return $data;
    }
}
