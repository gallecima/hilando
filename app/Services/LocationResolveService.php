<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Locality;
use App\Models\Province;
use Illuminate\Support\Facades\Http;

class LocationResolveService
{
    public function resolve(?string $postcode, ?float $lat, ?float $lon, string $countryCode = 'AR'): array
    {
        $countryCode = strtoupper(trim($countryCode)) ?: 'AR';

        if ($lat !== null && $lon !== null) {
            $geo = $this->reverseByCoords($lat, $lon);
        } elseif ($postcode !== null && trim($postcode) !== '') {
            $geo = $this->searchByPostcode($postcode, $countryCode);
        } else {
            return [
                'ok' => false,
                'error' => 'Faltan datos para resolver la ubicación.',
            ];
        }

        if (($geo['ok'] ?? false) !== true) {
            return $geo;
        }

        $city     = trim((string) ($geo['city'] ?? ''));
        $province = trim((string) ($geo['province'] ?? ''));
        $pc       = preg_replace('/\s+/', '', (string) ($geo['postcode'] ?? $postcode ?? ''));

        [$countryId, $provinceId, $localityId] = $this->resolveDestinationIds($countryCode, $province, $city);

        return [
            'ok'          => true,
            'country'     => $countryCode,
            'country_id'  => $countryId,
            'province'    => $province,
            'province_id' => $provinceId,
            'city'        => $city,
            'locality_id' => $localityId,
            'postcode'    => $pc,
            'lat'         => isset($geo['lat']) ? (float) $geo['lat'] : null,
            'lon'         => isset($geo['lon']) ? (float) $geo['lon'] : null,
            'display'     => $geo['display'] ?? null,
        ];
    }

    protected function reverseByCoords(float $lat, float $lon): array
    {
        $url = 'https://nominatim.openstreetmap.org/reverse';

        $res = Http::timeout(10)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'pixelio-cart/1.0 (checkout)',
            ])
            ->get($url, [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lon,
                'addressdetails' => 1,
            ]);

        if (!$res->successful()) {
            return [
                'ok' => false,
                'error' => 'No se pudo obtener la ubicación (OpenStreetMap).',
                'details' => 'HTTP ' . $res->status(),
            ];
        }

        $data = $res->json();
        if (!is_array($data)) {
            return [
                'ok' => false,
                'error' => 'Respuesta inválida de OpenStreetMap.',
            ];
        }

        $addr = (array) ($data['address'] ?? []);

        $postcode = (string) ($addr['postcode'] ?? '');
        $city = (string) (
            $addr['city']
            ?? $addr['town']
            ?? $addr['village']
            ?? $addr['hamlet']
            ?? $addr['locality']
            ?? $addr['municipality']
            ?? $addr['county']
            ?? $addr['state_district']
            ?? $addr['city_district']
            ?? $addr['suburb']
            ?? ''
        );
        $province = (string) ($addr['state'] ?? '');

        return [
            'ok' => true,
            'postcode' => $postcode,
            'city' => $city,
            'province' => $province,
            'lat' => $lat,
            'lon' => $lon,
            'display' => $data['display_name'] ?? null,
        ];
    }

    protected function searchByPostcode(string $postcode, string $countryCode): array
    {
        $postcode = preg_replace('/\s+/', '', (string) $postcode);
        if ($postcode === '') {
            return [
                'ok' => false,
                'error' => 'Ingresá un código postal.',
            ];
        }

        $url = 'https://nominatim.openstreetmap.org/search';

        $res = Http::timeout(10)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'pixelio-cart/1.0 (checkout)',
            ])
            ->get($url, [
                'format' => 'jsonv2',
                'countrycodes' => strtolower($countryCode),
                'postalcode' => $postcode,
                'addressdetails' => 1,
                'limit' => 1,
            ]);

        if (!$res->successful()) {
            return [
                'ok' => false,
                'error' => 'No se pudo obtener la ubicación (OpenStreetMap).',
                'details' => 'HTTP ' . $res->status(),
            ];
        }

        $hits = $res->json();
        if (!is_array($hits) || empty($hits[0]) || !is_array($hits[0])) {
            return [
                'ok' => false,
                'error' => 'No se encontró una ubicación para ese código postal.',
            ];
        }

        $hit = $hits[0];
        $addr = (array) ($hit['address'] ?? []);

        $pc = (string) ($addr['postcode'] ?? $postcode);
        $city = (string) (
            $addr['city']
            ?? $addr['town']
            ?? $addr['village']
            ?? $addr['hamlet']
            ?? $addr['locality']
            ?? $addr['municipality']
            ?? $addr['county']
            ?? $addr['state_district']
            ?? $addr['city_district']
            ?? $addr['suburb']
            ?? ''
        );
        $province = (string) ($addr['state'] ?? '');

        return [
            'ok' => true,
            'postcode' => $pc,
            'city' => $city,
            'province' => $province,
            'lat' => isset($hit['lat']) ? (float) $hit['lat'] : null,
            'lon' => isset($hit['lon']) ? (float) $hit['lon'] : null,
            'display' => $hit['display_name'] ?? null,
        ];
    }

    protected function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = @iconv('UTF-8', 'ASCII//TRANSLIT', $s) ?: $s;
        return trim(preg_replace('/[^a-z0-9 ]/', '', $s));
    }

    protected function bestMatchId(string $needle, $rows, callable $nameOf): ?int
    {
        $n = $this->norm($needle);
        if ($n === '') return null;

        $bestId = null;
        $bestScore = 0;

        foreach ($rows as $row) {
            $cand = $this->norm((string) $nameOf($row));
            if ($cand === '') continue;
            if ($cand === $n) return (int) $row->id;

            if (str_contains($n, $cand) || str_contains($cand, $n)) {
                $score = min(strlen($cand), strlen($n));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestId = (int) $row->id;
                }
            }
        }

        return $bestId;
    }

    protected function resolveDestinationIds(string $countryCode, string $state, string $city): array
    {
        $countryId  = null;
        $provinceId = null;
        $localityId = null;

        $cc = strtoupper(trim($countryCode)) ?: 'AR';
        if ($cc === 'AR') {
            $countryId = Country::where('name', 'Argentina')->value('id');
            if (!$countryId) {
                $countries = Country::all(['id', 'name']);
                $countryId = $this->bestMatchId('Argentina', $countries, fn ($c) => $c->name);
            }
        }

        $provinces = Province::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->get(['id', 'name', 'country_id']);

        if (trim($state) !== '') {
            $provinceId = $this->bestMatchId($state, $provinces, fn ($p) => $p->name);

            $normState = $this->norm($state);
            if (!$provinceId && ($normState === 'caba' || $normState === 'capital federal')) {
                $provinceId = $this->bestMatchId('Ciudad Autónoma de Buenos Aires', $provinces, fn ($p) => $p->name)
                    ?? $this->bestMatchId('Capital Federal', $provinces, fn ($p) => $p->name)
                    ?? $this->bestMatchId('Buenos Aires', $provinces, fn ($p) => $p->name);
            }
        }

        $cityTrim = trim($city);
        if ($cityTrim !== '') {
            $stop = [
                'departamento', 'provincia', 'municipio', 'partido', 'comuna', 'distrito',
                'barrio', 'localidad', 'ciudad', 'region', 'estado',
                'general', 'san', 'santa', 'santo', 'de', 'del', 'la', 'el', 'los', 'las', 'y',
            ];

            if ($provinceId) {
                $q = Locality::where('province_id', $provinceId);

                $tokens = array_values(array_filter(explode(' ', $this->norm($cityTrim))));
                usort($tokens, fn ($a, $b) => strlen($b) <=> strlen($a));
                $token = '';
                foreach ($tokens as $t) {
                    if (strlen($t) < 4) continue;
                    if (in_array($t, $stop, true)) continue;
                    $token = $t;
                    break;
                }
                if ($token !== '') {
                    $q->where('name', 'like', '%' . $token . '%');
                }

                $localities = $q->limit(2000)->get(['id', 'name', 'province_id']);
                if ($localities->isEmpty()) {
                    $localities = Locality::where('province_id', $provinceId)
                        ->limit(2000)
                        ->get(['id', 'name', 'province_id']);
                }
                $localityId = $this->bestMatchId($cityTrim, $localities, fn ($l) => $l->name);
            }

            if (!$localityId) {
                $q = Locality::query();
                if ($countryId) {
                    $provinceIds = $provinces->pluck('id')->all();
                    $q->whereIn('province_id', $provinceIds);
                }

                $tokens = array_values(array_filter(explode(' ', $this->norm($cityTrim))));
                usort($tokens, fn ($a, $b) => strlen($b) <=> strlen($a));
                $token = '';
                foreach ($tokens as $t) {
                    if (strlen($t) < 4) continue;
                    if (in_array($t, $stop, true)) continue;
                    $token = $t;
                    break;
                }
                if ($token !== '') {
                    $q->where('name', 'like', '%' . $token . '%');
                }

                $localities = $q->limit(2000)->get(['id', 'name', 'province_id']);
                if ($localities->isEmpty() && $token !== '') {
                    $q2 = Locality::query();
                    if ($countryId) {
                        $provinceIds = $provinces->pluck('id')->all();
                        $q2->whereIn('province_id', $provinceIds);
                    }
                    $localities = $q2->limit(2000)->get(['id', 'name', 'province_id']);
                }
                $localityId = $this->bestMatchId($cityTrim, $localities, fn ($l) => $l->name);
            }

            if ($localityId) {
                $provinceId = Locality::where('id', $localityId)->value('province_id') ?: $provinceId;
            }
        }

        if (!$countryId && $provinceId) {
            $countryId = Province::where('id', $provinceId)->value('country_id');
        }

        return [$countryId ? (int) $countryId : null, $provinceId ? (int) $provinceId : null, $localityId ? (int) $localityId : null];
    }
}
