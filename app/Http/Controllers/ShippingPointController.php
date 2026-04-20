<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Locality;
use App\Models\Province;
use App\Models\ShippingPoint;
use App\Services\LocationResolveService;
use Illuminate\Http\Request;

class ShippingPointController extends Controller
{
    public function index()
    {
        $shippingPoints = ShippingPoint::with(['country', 'province', 'locality'])
            ->orderBy('name')
            ->get();

        return view('admin.shipping-points.index', compact('shippingPoints'));
    }

    public function create()
    {
        return view('admin.shipping-points.create');
    }

    public function store(Request $request, LocationResolveService $resolver)
    {
        $validated = $this->validateData($request);
        $payload = $this->buildPayload($validated, $resolver);

        ShippingPoint::create($payload);

        return redirect()->route('admin.shipping-points.index')->with('success', 'Punto de envío creado correctamente.');
    }

    public function edit(ShippingPoint $shippingPoint)
    {
        return view('admin.shipping-points.edit', compact('shippingPoint'));
    }

    public function update(Request $request, ShippingPoint $shippingPoint, LocationResolveService $resolver)
    {
        $validated = $this->validateData($request);
        $payload = $this->buildPayload($validated, $resolver);

        $shippingPoint->update($payload);

        return redirect()->route('admin.shipping-points.index')->with('success', 'Punto de envío actualizado correctamente.');
    }

    public function destroy(ShippingPoint $shippingPoint)
    {
        if ($shippingPoint->shipmentMethods()->exists()) {
            return redirect()->route('admin.shipping-points.index')->with('error', 'No se puede eliminar un punto asociado a métodos de envío.');
        }

        $shippingPoint->delete();

        return redirect()->route('admin.shipping-points.index')->with('success', 'Punto de envío eliminado correctamente.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:120',
            'address_line' => 'nullable|string|max:255',
            'country_name' => 'nullable|string|max:120',
            'province_name' => 'nullable|string|max:120',
            'locality_name' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'service_radius_km' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function buildPayload(array $validated, LocationResolveService $resolver): array
    {
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        foreach (['provider', 'address_line', 'country_name', 'province_name', 'locality_name', 'postal_code', 'notes'] as $field) {
            $value = trim((string) ($validated[$field] ?? ''));
            $validated[$field] = $value !== '' ? $value : null;
        }

        $countryName = $validated['country_name'] ?? 'Argentina';
        $provinceName = $validated['province_name'] ?? null;
        $localityName = $validated['locality_name'] ?? null;
        $postalCode = $validated['postal_code'] ?? null;
        $latitude = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $longitude = isset($validated['longitude']) ? (float) $validated['longitude'] : null;

        if (($latitude === null || $longitude === null) && $postalCode) {
            $resolved = $resolver->resolve($postalCode, null, null, 'AR');
            if (($resolved['ok'] ?? false) === true) {
                $countryName = $countryName ?: (($resolved['country'] ?? 'AR') === 'AR' ? 'Argentina' : (string) $resolved['country']);
                $provinceName = $provinceName ?: trim((string) ($resolved['province'] ?? ''));
                $localityName = $localityName ?: trim((string) ($resolved['city'] ?? ''));
                $latitude = $latitude ?? (isset($resolved['lat']) ? (float) $resolved['lat'] : null);
                $longitude = $longitude ?? (isset($resolved['lon']) ? (float) $resolved['lon'] : null);
            }
        }

        $countryId = null;
        $provinceId = null;
        $localityId = null;

        if ($countryName) {
            $countryId = Country::whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)])->value('id');
        }

        if ($provinceName) {
            $provinceId = Province::query()
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($provinceName)])
                ->value('id');
        }

        if ($localityName && $provinceId) {
            $localityId = Locality::where('province_id', $provinceId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($localityName)])
                ->value('id');
        }

        return array_merge($validated, [
            'country_id' => $countryId,
            'province_id' => $provinceId,
            'locality_id' => $localityId,
            'country_name' => $countryName,
            'province_name' => $provinceName,
            'locality_name' => $localityName,
            'postal_code' => $postalCode,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
