<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Locality;
use Illuminate\Http\Request;
use App\Services\LocationResolveService;

class LocationController extends Controller
{
    public function getProvinces($countryId)
    {
        $provinces = Province::where('country_id', $countryId)->orderBy('name')->get();
        return response()->json($provinces);
    }

    public function getLocalities($provinceId)
    {
        $localities = Locality::where('province_id', $provinceId)->orderBy('name')->get();
        return response()->json($localities);
    }

    public function resolve(Request $request, LocationResolveService $resolver)
    {
        $data = $request->validate([
            'postcode' => 'nullable|string|max:20',
            'lat'      => 'nullable|numeric',
            'lon'      => 'nullable|numeric',
            'country'  => 'nullable|string|size:2',
        ]);

        $postcode = isset($data['postcode']) ? preg_replace('/\\s+/', '', (string) $data['postcode']) : null;
        $lat = array_key_exists('lat', $data) ? (float) $data['lat'] : null;
        $lon = array_key_exists('lon', $data) ? (float) $data['lon'] : null;

        $hasCoords = ($data['lat'] ?? null) !== null && ($data['lon'] ?? null) !== null;
        $countryCode = strtoupper(trim((string) ($data['country'] ?? 'AR'))) ?: 'AR';

        if (!$hasCoords && (!$postcode || $postcode === '')) {
            return response()->json([
                'ok' => false,
                'error' => 'Ingresá un código postal o permití detectar ubicación.',
            ], 422);
        }

        $result = $resolver->resolve($postcode, $hasCoords ? $lat : null, $hasCoords ? $lon : null, $countryCode);

        $status = ($result['ok'] ?? false) ? 200 : 422;
        return response()->json($result, $status);
    }
}
