<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LocationSearch
{
    public function cache()
    {
        return Cache::store(config('geocoding.cache_store'));
    }

    public function search(string $query): array
    {
        $query = preg_replace('/\s+/u', ' ', trim($query));
        $cache = $this->cache();
        $key = 'geocoding:pe:'.hash('sha256', config('geocoding.search_url').'|'.mb_strtolower($query));
        $results = $cache->get($key);
        $cached = is_array($results);
        if (! $cached) {
            $lock = $cache->lock('geocoding:provider-lock', 20);
            if (! $lock->get()) {
                $this->unavailable('Hay otra búsqueda de ubicación en curso. Vuelve a intentarlo en unos segundos.', 429, 2);
            }

            try {
                $results = $cache->get($key);
                if (! is_array($results)) {
                    $remaining = (float) $cache->get('geocoding:next-request-at', 0) - microtime(true);
                    if ($remaining > 0) {
                        $this->unavailable('Espera unos segundos antes de buscar otra ubicación.', 429, (int) ceil($remaining));
                    }

                    $cache->put('geocoding:next-request-at', microtime(true) + 1.1, 86400);
                    try {
                        $response = Http::acceptJson()->withUserAgent(config('geocoding.user_agent'))
                            ->connectTimeout(3)->timeout(8)->withoutRedirecting()
                            ->get(config('geocoding.search_url'), [
                                'q' => $query, 'format' => 'jsonv2', 'countrycodes' => 'pe',
                                'addressdetails' => 1, 'accept-language' => 'es', 'limit' => 6,
                            ]);
                    } catch (ConnectionException) {
                        $this->unavailable('No pudimos conectar con el servicio de ubicaciones. Puedes escribir una ubicación manualmente.', 503, 10);
                    } finally {
                        $cache->put('geocoding:next-request-at', microtime(true) + 1.1, 86400);
                    }

                    if (! $response->successful()) {
                        $header = $response->header('Retry-After');
                        $retry = max(60, is_numeric($header) ? (int) $header : (int) strtotime($header ?? '') - time());
                        $cache->put('geocoding:next-request-at', microtime(true) + $retry, 86400);
                        $this->unavailable('El servicio de ubicaciones no está disponible por el momento. Puedes continuar manualmente.', 503, $retry);
                    }
                    $json = $response->json();
                    if (! is_array($json) || ! array_is_list($json)) {
                        $this->unavailable('El servicio devolvió una respuesta no válida. Inténtalo más tarde o escribe la ubicación manualmente.', 503, 10);
                    }

                    $results = collect($json)->map(fn ($row) => $this->normalize($row))->filter()->values()->all();
                    $cache->put($key, $results, config('geocoding.cache_seconds'));
                } else {
                    $cached = true;
                }
            } finally {
                $lock->release();
            }
        }

        return [
            'results' => array_map(fn (array $place) => [...$place, 'selection' => $this->encode($place)], $results),
            'cached' => $cached, 'source' => 'Nominatim / OpenStreetMap',
        ];
    }

    private function normalize(mixed $row): ?array
    {
        if (! is_array($row) || ! is_array($row['address'] ?? null)
            || ! is_string($row['address']['country_code'] ?? null) || strtolower($row['address']['country_code']) !== 'pe'
            || ! is_numeric($row['lat'] ?? null) || ! is_numeric($row['lon'] ?? null)
            || ! $this->isPeruvianCoordinate((float) $row['lat'], (float) $row['lon'])
            || ! is_string($row['display_name'] ?? null)) {
            return null;
        }

        $bounds = $row['boundingbox'] ?? null;
        if (! is_array($bounds) || count($bounds) !== 4 || count(array_filter($bounds, 'is_numeric')) !== 4) {
            return null;
        }
        $bounds = array_map('floatval', array_values($bounds));
        if ($bounds[0] > $bounds[1] || $bounds[2] > $bounds[3]
            || $bounds[0] < -90 || $bounds[1] > 90 || $bounds[2] < -180 || $bounds[3] > 180) {
            return null;
        }

        return [
            'label' => Str::limit($row['display_name'], 250, ''),
            'name' => Str::limit(is_string($row['name'] ?? null) ? $row['name'] : explode(',', $row['display_name'])[0], 160, ''),
            'latitude' => (float) $row['lat'], 'longitude' => (float) $row['lon'],
            'bounds' => $bounds,
        ];
    }

    public function isPeruvianCoordinate(float $latitude, float $longitude): bool
    {
        return $latitude >= -19 && $latitude <= 1 && $longitude >= -84 && $longitude <= -68;
    }

    public function encode(array $place): string
    {
        return Crypt::encryptString(json_encode([...$place, 'expires' => now()->addDays(7)->timestamp], JSON_THROW_ON_ERROR));
    }

    public function decode(string $selection): array
    {
        try {
            $place = json_decode(Crypt::decryptString($selection), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($place) || ($place['expires'] ?? 0) < now()->timestamp
                || ! isset($place['label'], $place['name'], $place['latitude'], $place['longitude'], $place['bounds'])) {
                throw new \RuntimeException('Invalid selection');
            }

            return $place;
        } catch (Throwable) {
            throw ValidationException::withMessages(['location_name' => 'La selección de ubicación venció o no es válida. Busca y selecciona nuevamente.']);
        }
    }

    public function offerAttributes(array $input): array
    {
        $mode = $input['work_mode'] ?? 'onsite';
        $attributes = ['work_mode' => $mode, 'location_name' => $input['location_name'] ?? null, 'latitude' => null, 'longitude' => null];
        if ($mode === 'remote') {
            $attributes['district_id'] = null;
        }
        if ($mode !== 'remote' && filled($input['location_selection'] ?? null)) {
            if (! is_string($input['location_selection']) || strlen($input['location_selection']) > 4096) {
                throw ValidationException::withMessages(['location_name' => 'Selecciona una ubicación válida o escríbela manualmente.']);
            }
            $place = $this->decode($input['location_selection']);
            $attributes = [...$attributes, 'location_name' => $place['label'], 'latitude' => $place['latitude'], 'longitude' => $place['longitude']];
            $attributes['district_id'] = District::where('active', true)->where('name', $place['name'])->value('id');
        }

        return $attributes;
    }

    private function unavailable(string $message, int $status, int $retry): never
    {
        throw new HttpResponseException(response()->json(['message' => $message], $status, ['Retry-After' => (string) $retry]));
    }
}
