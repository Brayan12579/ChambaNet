<?php

namespace App\Http\Controllers;

use App\Services\LocationSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function search(Request $request, LocationSearch $locations): JsonResponse
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:160', 'not_regex:/[\x00-\x1F]/']]);

        return response()->json($locations->search($data['q']));
    }

    public function nearby(Request $request, LocationSearch $locations): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $token = Str::random(40);
        // Approximate coordinates only, transient and bound to the browser session.
        $locations->cache()->put('geocoding:origin:'.$token, [
            'latitude' => round((float) $data['latitude'], 2),
            'longitude' => round((float) $data['longitude'], 2),
            'session' => hash('sha256', $request->session()->getId()),
        ], 600);

        return response()->json(['nearby' => $token], 200, ['Cache-Control' => 'private, no-store']);
    }
}
