<div
    wire:ignore
    x-data="terracapLocationPicker({
        latitude: @js($get('latitude')),
        longitude: @js($get('longitude')),
        latitudePath: 'data.latitude',
        longitudePath: 'data.longitude',
    })"
    x-init="mount()"
>
    <div x-ref="map" class="terracap-location-map" aria-label="Selecione a localização do lote no mapa"></div>
    <p class="mt-2 text-sm text-gray-500">Clique dentro de uma Região Administrativa para preencher latitude e longitude. A região será determinada automaticamente ao salvar.</p>
</div>
