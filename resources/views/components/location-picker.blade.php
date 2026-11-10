@props(['context' => 'offer', 'value' => '', 'selection' => '', 'latitude' => null, 'longitude' => null])
@php
    $field = $context === 'offer' ? 'location_name' : 'location';
    $tokenField = $context === 'offer' ? 'location_selection' : 'place';
@endphp
<div class="location-picker field" data-location-picker data-context="{{ $context }}" data-search-url="{{ route('locations.search') }}">
    <label for="{{ $field }}"><x-icon name="pin" :size="17" />{{ $context === 'offer' ? 'Ubicación pública del trabajo' : 'Ciudad, distrito o departamento' }}</label>
    <input id="{{ $field }}" name="{{ $field }}" value="{{ $value }}" maxlength="255" placeholder="Ej. Wanchaq, Cusco" autocomplete="off" data-location-query aria-describedby="{{ $field }}-help">
    <input type="hidden" name="{{ $tokenField }}" value="{{ $selection }}" data-location-selection>
    <button class="button button-secondary button-sm" type="button" data-location-search><x-icon name="search" :size="17" />Buscar ubicación</button>
    <small id="{{ $field }}-help" class="muted">{{ $context === 'offer' ? 'Busca y selecciona un lugar de trabajo público. Puedes conservar el texto manualmente. Para trabajo remoto no se necesita una dirección exacta.' : 'Confirma la búsqueda y selecciona una coincidencia de Perú para filtrar por zona y ordenar por distancia.' }} No ingreses domicilios personales ni datos privados.</small>
    <div class="location-results" data-location-results hidden aria-label="Ubicaciones encontradas"></div>
    <small class="location-status muted" role="status" aria-live="polite" data-location-status></small>
    <small class="location-attribution">Ubicaciones: <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">© OpenStreetMap contributors</a> · Nominatim</small>
    @error($field)<small class="field-error">{{ $message }}</small>@enderror
    @if($context === 'offer')
        <div class="location-preview" data-location-preview @if($latitude === null || $longitude === null) hidden @endif>
            <small class="muted" data-location-coordinates>@if($latitude !== null && $longitude !== null)Coordenadas del lugar seleccionado: {{ $latitude }}, {{ $longitude }}@endif</small>
            <div class="location-map location-map-preview" data-location-preview-map data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" data-tile-url="{{ config('geocoding.tile_url') }}" data-tile-attribution="{{ config('geocoding.tile_attribution') }}" role="region" aria-label="Vista previa de la ubicación del trabajo"></div>
        </div>
    @endif
</div>
