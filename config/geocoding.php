<?php

return [
    'search_url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org/search'),
    'user_agent' => env('NOMINATIM_USER_AGENT', 'ChambaNet/1.0 (+'.env('APP_URL', 'http://localhost').'; job-location search)'),
    // A shared database store keeps the provider limit global across PHP workers.
    'cache_store' => env('NOMINATIM_CACHE_STORE', 'database'),
    'cache_seconds' => 604800,
    'tile_url' => env('OSM_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'tile_attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
];
