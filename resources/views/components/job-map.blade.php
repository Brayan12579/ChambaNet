@props(['markers'])
<div class="job-map-block">
    <div class="location-map" data-job-map data-markers="{{ $markers->toJson() }}" data-tile-url="{{ config('geocoding.tile_url') }}" data-tile-attribution="{{ config('geocoding.tile_attribution') }}" role="region" aria-label="Mapa de ofertas laborales con ubicación pública"></div>
    <p class="map-status muted" data-map-status role="status">{{ $markers->count() ? 'El mapa muestra hasta 200 ofertas que coinciden con tus filtros y tienen coordenadas verificadas. Las ofertas sin coordenadas y las remotas siguen disponibles en el listado.' : 'No hay ofertas con coordenadas para mostrar en esta búsqueda. Revisa el listado o cambia los filtros.' }}</p>
    <noscript><p class="muted">Activa JavaScript para ver el mapa interactivo. Puedes consultar las ofertas en el listado.</p></noscript>
</div>
