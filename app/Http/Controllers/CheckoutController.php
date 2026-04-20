<?php

namespace App\Http\Controllers;

use App\Models\ShipmentMethod;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Country;
use App\Models\Province;
use App\Models\Locality;
use App\Services\CartService;
use App\Services\EmailTemplateSender;
use App\Services\LocationResolveService;
use App\Services\OrderDownloadService;
use App\Services\ShippingPackagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\EnviaCom\Services\EnviaClient;

class CheckoutController extends Controller
{
    private const DIGITAL_ONLY_CHECKOUT = false;

    private function normalizeDniDigits(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;

        $digits = (string) preg_replace('/\D+/', '', $value);
        $digits = trim($digits);

        return $digits !== '' ? $digits : null;
    }

    private function normalizeLocalityName(string $name): string
    {
        $name = trim((string) preg_replace('/\s+/', ' ', $name));
        if ($name === '') return '';

        // Limpieza típica de OpenStreetMap: "Municipio de X", "Partido de X", etc.
        $name = (string) preg_replace('/^(municipio|partido|departamento|distrito)\s+de\s+/iu', '', $name);
        $name = trim((string) preg_replace('/\s+/', ' ', $name));

        // Evitar casos raros tipo "1"
        if ($name === '' || preg_match('/^\d+$/', $name)) return '';

        return mb_substr($name, 0, 255);
    }

    private function normalizeLocationText(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        if ($value === '') return '';

        $value = (string) preg_replace('/\s+/', ' ', $value);
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = (string) preg_replace('/[^a-z0-9 ]/', '', $value);
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

        return $value;
    }

    private function textDestinationMatches(?string $methodValue, ?string $destinationValue): bool
    {
        $methodNorm = $this->normalizeLocationText($methodValue);
        if ($methodNorm === '') return true;

        $destinationNorm = $this->normalizeLocationText($destinationValue);
        if ($destinationNorm === '') return false;

        return $methodNorm === $destinationNorm
            || str_contains($destinationNorm, $methodNorm)
            || str_contains($methodNorm, $destinationNorm);
    }

    private function postalCodeMatches(?string $methodPostalCode, ?string $destinationPostalCode): bool
    {
        $methodPc = strtoupper(trim((string) preg_replace('/\s+/', '', (string) $methodPostalCode)));
        if ($methodPc === '') return true;

        $destinationPc = strtoupper(trim((string) preg_replace('/\s+/', '', (string) $destinationPostalCode)));
        if ($destinationPc === '') return false;

        return $methodPc === $destinationPc;
    }

    private function resolveCountryName(?int $countryId, ?string $countryCode = 'AR'): string
    {
        if ($countryId) {
            $name = Country::where('id', $countryId)->value('name');
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        $code = strtoupper(trim((string) $countryCode));
        if ($code === '' || $code === 'AR') {
            return 'Argentina';
        }

        return $code;
    }

    private function methodMatchesTextDestination(
        ShipmentMethod $method,
        ?string $countryName,
        ?string $provinceName,
        ?string $localityName,
        ?string $postalCode
    ): bool {
        return $this->textDestinationMatches($method->country_name ?? null, $countryName)
            && $this->textDestinationMatches($method->province_name ?? null, $provinceName)
            && $this->textDestinationMatches($method->locality_name ?? null, $localityName)
            && $this->postalCodeMatches($method->postal_code ?? null, $postalCode);
    }

    private function resolveDestinationCoordinates(
        LocationResolveService $resolver,
        ?string $postcode,
        ?float $lat = null,
        ?float $lon = null,
        string $countryCode = 'AR'
    ): array {
        if ($lat !== null && $lon !== null) {
            return ['lat' => $lat, 'lon' => $lon];
        }

        $postcode = preg_replace('/\s+/', '', (string) $postcode);
        if ($postcode === '') {
            return ['lat' => null, 'lon' => null];
        }

        $resolved = $resolver->resolve($postcode, null, null, $countryCode);
        if (($resolved['ok'] ?? false) !== true) {
            return ['lat' => null, 'lon' => null];
        }

        return [
            'lat' => isset($resolved['lat']) ? (float) $resolved['lat'] : null,
            'lon' => isset($resolved['lon']) ? (float) $resolved['lon'] : null,
        ];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    private function evaluateMethodDestinationMatch(ShipmentMethod $method, array $destination): array
    {
        $exact = $this->methodMatchesTextDestination(
            $method,
            $destination['country_name'] ?? null,
            $destination['province_name'] ?? null,
            $destination['city_name'] ?? null,
            $destination['postcode'] ?? null
        );

        if ($exact) {
            return [
                'ok' => true,
                'type' => 'exact',
                'distance_km' => null,
                'point_name' => null,
            ];
        }

        $point = $method->shippingPoint;
        if (!$method->allow_nearby_match || !$point || !(bool) ($point->is_active ?? false)) {
            return ['ok' => false, 'type' => null, 'distance_km' => null, 'point_name' => null];
        }

        $destLat = $destination['lat'] ?? null;
        $destLon = $destination['lon'] ?? null;
        $pointLat = $point->latitude ?? null;
        $pointLon = $point->longitude ?? null;

        if ($destLat === null || $destLon === null || $pointLat === null || $pointLon === null) {
            return ['ok' => false, 'type' => null, 'distance_km' => null, 'point_name' => null];
        }

        $radius = (float) ($method->nearby_radius_km ?: $point->service_radius_km ?: 0);
        if ($radius <= 0) {
            return ['ok' => false, 'type' => null, 'distance_km' => null, 'point_name' => null];
        }

        $distance = $this->haversineKm((float) $destLat, (float) $destLon, (float) $pointLat, (float) $pointLon);
        if ($distance > $radius) {
            return ['ok' => false, 'type' => null, 'distance_km' => $distance, 'point_name' => $point->name];
        }

        return [
            'ok' => true,
            'type' => 'nearby',
            'distance_km' => $distance,
            'point_name' => $point->name,
        ];
    }

    private function sanitizePackagePlanValue($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $sanitized = [];
        foreach ($value as $key => $item) {
            if ($key === 'box_model') {
                continue;
            }
            $sanitized[$key] = $this->sanitizePackagePlanValue($item);
        }

        return $sanitized;
    }

    private function buildPackagePlanForMethod(
        ShipmentMethod $method,
        CartService $cart,
        ShippingPackagingService $packagingService
    ): ?array {
        $method->loadMissing('shippingBoxes');

        if ($method->shippingBoxes->where('is_active', true)->isEmpty()) {
            return [
                'requires_boxing' => false,
                'package_count' => 0,
                'packages' => [],
                'total_weight' => 0.0,
                'total_box_weight' => 0.0,
                'unpacked_items' => [],
            ];
        }

        $plan = $packagingService->packageCartForMethod($cart, $method);
        if (!$plan) {
            return null;
        }

        $plan['requires_boxing'] = true;

        return $this->sanitizePackagePlanValue($plan);
    }

    private function matchAndPackageShipmentMethods(
        $methods,
        array $destination,
        CartService $cart,
        ShippingPackagingService $packagingService
    ) {
        return collect($methods)
            ->map(function (ShipmentMethod $method) use ($destination, $cart, $packagingService) {
                $match = $this->evaluateMethodDestinationMatch($method, $destination);
                if (!($match['ok'] ?? false)) {
                    return null;
                }

                $packagePlan = $this->buildPackagePlanForMethod($method, $cart, $packagingService);
                if ($packagePlan === null) {
                    return null;
                }

                $method->setAttribute('destination_match_type', $match['type'] ?? 'exact');
                $method->setAttribute('destination_distance_km', $match['distance_km'] ?? null);
                $method->setAttribute('destination_point_name', $match['point_name'] ?? null);
                $method->setAttribute('shipping_package_plan', $packagePlan);

                return $method;
            })
            ->filter()
            ->sort(function (ShipmentMethod $left, ShipmentMethod $right) {
                $leftPriority = ($left->destination_match_type ?? 'exact') === 'exact' ? 0 : 1;
                $rightPriority = ($right->destination_match_type ?? 'exact') === 'exact' ? 0 : 1;
                if ($leftPriority !== $rightPriority) {
                    return $leftPriority <=> $rightPriority;
                }

                $leftDistance = (float) ($left->destination_distance_km ?? 999999);
                $rightDistance = (float) ($right->destination_distance_km ?? 999999);
                if ($leftDistance !== $rightDistance) {
                    return $leftDistance <=> $rightDistance;
                }

                return strcasecmp((string) $left->name, (string) $right->name);
            })
            ->values();
    }

    private function forgetShipmentSelectionState(bool $includePaymentState = false): void
    {
        $keys = [
            'checkout.shipment_method_id',
            'shipping.override',
            'shipping.package_plan',
        ];

        if ($includePaymentState) {
            $keys = array_merge($keys, [
                'payment_method_id',
                'checkout.order_id',
                'checkout.amount',
            ]);
        }

        session()->forget($keys);
    }

    private function makeCheckoutLocationSignature(?string $postalCode, ?int $provinceId, ?int $localityId): string
    {
        $postalCode = preg_replace('/\s+/', '', (string) $postalCode);

        return implode('|', [
            strtoupper(trim((string) $postalCode)),
            (int) ($provinceId ?? 0),
            (int) ($localityId ?? 0),
        ]);
    }

    private function currentCheckoutLocationSignature(): string
    {
        $customer = auth('customer')->user();
        if ($customer?->address) {
            return $this->makeCheckoutLocationSignature(
                $customer->address->postal_code ?? null,
                optional(optional($customer->address)->locality)->province_id,
                $customer->address->locality_id ?? null
            );
        }

        $shipping = (array) session('guest_checkout.shipping', []);

        return $this->makeCheckoutLocationSignature(
            $shipping['postal_code'] ?? null,
            isset($shipping['province_id']) ? (int) $shipping['province_id'] : null,
            isset($shipping['locality_id']) ? (int) $shipping['locality_id'] : null
        );
    }

    private function hasCheckoutDestinationData(): bool
    {
        $customer = auth('customer')->user();
        if ($customer?->address) {
            return filled($customer->address->postal_code) && filled($customer->address->locality_id);
        }

        $shipping = (array) session('guest_checkout.shipping', []);

        return filled($shipping['postal_code'] ?? null) && filled($shipping['locality_id'] ?? null);
    }

    private function resolveCheckoutDestinationContext(
        ?CartService $cart = null,
        ?LocationResolveService $resolver = null,
        array $override = []
    ): array {
        $customer = auth('customer')->user();
        $guestShipping = (array) session('guest_checkout.shipping', []);

        $postcode = preg_replace('/\s+/', '', (string) (
            $override['postcode']
            ?? ($customer?->address?->postal_code ?? ($guestShipping['postal_code'] ?? ''))
        ));
        $localityId = isset($override['locality_id'])
            ? (int) $override['locality_id']
            : (int) ($customer?->address?->locality_id ?? ($guestShipping['locality_id'] ?? 0));
        $provinceId = isset($override['province_id'])
            ? (int) $override['province_id']
            : (int) ($guestShipping['province_id'] ?? 0);
        $countryId = isset($override['country_id']) ? (int) $override['country_id'] : null;

        $cityName = trim((string) ($override['city_name'] ?? ($customer?->address?->city ?? ($guestShipping['city'] ?? ''))));
        $provinceName = trim((string) ($override['province_name'] ?? ($customer?->address?->province ?? ($guestShipping['province'] ?? ''))));
        $countryCode = strtoupper(trim((string) ($override['country_code'] ?? 'AR'))) ?: 'AR';

        $lat = array_key_exists('lat', $override)
            ? ($override['lat'] !== null ? (float) $override['lat'] : null)
            : (!$customer && isset($guestShipping['lat']) && $guestShipping['lat'] !== '' ? (float) $guestShipping['lat'] : null);
        $lon = array_key_exists('lon', $override)
            ? ($override['lon'] !== null ? (float) $override['lon'] : null)
            : (!$customer && isset($guestShipping['lon']) && $guestShipping['lon'] !== '' ? (float) $guestShipping['lon'] : null);

        if ($localityId > 0) {
            $loc = Locality::with('province.country')->find($localityId);
            if ($loc) {
                $cityName = (string) ($loc->name ?? $cityName);
                $provinceId = (int) ($loc->province_id ?? $provinceId);
                $provinceName = (string) ($loc->province?->name ?? $provinceName);
                $countryId = (int) ($loc->province?->country_id ?? $countryId);
            }
        }

        if ($provinceId > 0 && (!$countryId || $provinceName === '')) {
            $province = Province::with('country')->find($provinceId);
            if ($province) {
                $provinceName = (string) ($province->name ?? $provinceName);
                $countryId = (int) ($province->country_id ?? $countryId);
            }
        }

        $countryName = trim((string) ($override['country_name'] ?? $this->resolveCountryName($countryId, $countryCode)));

        return [
            'country_id' => $countryId ?: null,
            'province_id' => $provinceId > 0 ? $provinceId : null,
            'locality_id' => $localityId > 0 ? $localityId : null,
            'country_code' => $countryCode,
            'country_name' => $countryName !== '' ? $countryName : $this->resolveCountryName($countryId, $countryCode),
            'province_name' => $provinceName !== '' ? $provinceName : null,
            'city_name' => $cityName !== '' ? $cityName : null,
            'postcode' => $postcode !== '' ? $postcode : null,
            'lat' => $lat,
            'lon' => $lon,
        ];
    }

    private function resolveShipmentMethodsForDestination(
        CartService $cart,
        array $destinationContext,
        bool $excludeEnviacom = false,
        ?LocationResolveService $resolver = null,
        ?ShippingPackagingService $packagingService = null
    ) {
        $productsTotal = (float) $cart->getSubtotal();
        $countryId = $destinationContext['country_id'] ?? null;
        $provinceId = $destinationContext['province_id'] ?? null;
        $localityId = $destinationContext['locality_id'] ?? null;

        $shipmentMethods = ShipmentMethod::available()
            ->with(['shippingPoint', 'shippingBoxes'])
            ->when(
                $countryId,
                fn ($q) => $q->where(fn ($w) => $w->whereNull('country_id')->orWhere('country_id', $countryId)),
                fn ($q) => $q->whereNull('country_id')
            )
            ->when(
                $provinceId,
                fn ($q) => $q->where(fn ($w) => $w->whereNull('province_id')->orWhere('province_id', $provinceId)),
                fn ($q) => $q->whereNull('province_id')
            )
            ->when(
                $localityId,
                fn ($q) => $q->where(fn ($w) => $w->whereNull('locality_id')->orWhere('locality_id', $localityId)),
                fn ($q) => $q->whereNull('locality_id')
            )
            ->where(function ($q) use ($productsTotal) {
                $q->whereNull('min_cart_amount')
                    ->orWhereRaw('CAST(min_cart_amount AS DECIMAL(10,2)) <= ?', [$productsTotal]);
            })
            ->get()
            ->filter(fn ($method) => $this->pluginAllowsMethod($method->plugin_key ?? null));

        if ($excludeEnviacom) {
            $shipmentMethods = $shipmentMethods
                ->reject(fn ($method) => ($method->plugin_key ?? null) === 'enviacom')
                ->values();
        }

        $destination = [
            'country_name' => $destinationContext['country_name'] ?? null,
            'province_name' => $destinationContext['province_name'] ?? null,
            'city_name' => $destinationContext['city_name'] ?? null,
            'postcode' => $destinationContext['postcode'] ?? null,
            'lat' => $destinationContext['lat'] ?? null,
            'lon' => $destinationContext['lon'] ?? null,
        ];

        $needsCoords = $shipmentMethods->contains(function (ShipmentMethod $method) {
            $point = $method->shippingPoint;

            return (bool) ($method->allow_nearby_match ?? false)
                && $point
                && (bool) ($point->is_active ?? false)
                && $point->latitude !== null
                && $point->longitude !== null;
        });

        if (
            $needsCoords
            && ($destination['lat'] === null || $destination['lon'] === null)
            && filled($destination['postcode'] ?? null)
        ) {
            $coords = $this->resolveDestinationCoordinates(
                $resolver ?: app(LocationResolveService::class),
                $destination['postcode'] ?? null,
                $destination['lat'],
                $destination['lon'],
                (string) ($destinationContext['country_code'] ?? 'AR')
            );

            $destination['lat'] = $coords['lat'];
            $destination['lon'] = $coords['lon'];
        }

        return $this->matchAndPackageShipmentMethods(
            $shipmentMethods,
            $destination,
            $cart,
            $packagingService ?: app(ShippingPackagingService::class)
        );
    }

    private function extractEnviaRateError($raw): ?array
    {
        if (!is_array($raw)) return null;

        // Respuesta OK típica: { meta:"rate", data:[...] }
        if (($raw['meta'] ?? null) === 'rate') return null;

        // Envia puede devolver el error anidado: { meta:"error", error:{code,message,description} }
        $err  = (isset($raw['error']) && is_array($raw['error'])) ? $raw['error'] : $raw;

        $code = $err['code'] ?? null;
        $msg  = is_string($err['message'] ?? null) ? trim((string) $err['message']) : '';
        $desc = is_string($err['description'] ?? null) ? trim((string) $err['description']) : '';

        // Si no parece un error de Envia, no inventar.
        if ($code === null && $msg === '' && $desc === '') return null;

        $supportId = null;
        if ($desc !== '' && preg_match('/\\bep-[a-z0-9]+\\b/i', $desc, $m)) {
            $supportId = $m[0];
        }

        $public = 'Envia.com no pudo cotizar en este momento.';
        if ($supportId) {
            $public .= ' ID soporte: ' . $supportId . '.';
        }

        $detailsParts = [];
        if ($msg !== '')  $detailsParts[] = $msg;
        if ($desc !== '') $detailsParts[] = $desc;
        $details = $detailsParts ? implode(' — ', $detailsParts) : null;

        return [
            'code'           => is_numeric($code) ? (int) $code : $code,
            'message'        => $msg ?: null,
            'description'    => $desc ?: null,
            'support_id'     => $supportId,
            'public_message' => $public,
            'details'        => $details,
        ];
    }

    public function index(CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $this->clearShippingStateForDigitalCheckout($cart);
        $isDigitalCheckout = $this->isDigitalOnlyCheckout($cart);

        $customer = auth('customer')->user();
        if ($customer) {
            $customer->loadMissing(['address.locality.province', 'billingData']);
        }
        $guestShipping = (array) session('guest_checkout.shipping', []);
        $argentinaCountryId = Country::where('name', 'Argentina')->value('id');
        $isFreeCheckout = round((float) $cart->getTotalWithDiscount(), 2) <= 0.0;

        return view('front.checkout.index', compact('customer', 'guestShipping', 'argentinaCountryId', 'isFreeCheckout', 'isDigitalCheckout'));
    }

    private function pluginAllowsMethod(?string $pluginKey): bool
    {
        // Métodos “core” (sin plugin) siempre habilitados
        if (empty($pluginKey)) return true;

        // Si no existe la tabla de plugins, no bloqueamos
        if (!Schema::hasTable('plugins')) return true;

        $q = DB::table('plugins')
            ->where('slug', $pluginKey)   // ← antes decía 'key'
            ->where('is_active', 1);

        // Solo si existe la columna 'type', filtramos por shipping
        if (Schema::hasColumn('plugins', 'type')) {
            $q->where('type', 'shipping');
        }

        return $q->exists();
    }

    private function ensureValidCheckoutSession(CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }
        $hasGuest = session()->has('guest_checkout') || session()->has('guest_checkout.shipping');
        if (!auth('customer')->check() && !$hasGuest) {
            return redirect()->route('cart.index')->with('error', 'Debés iniciar sesión o completar tus datos para continuar.');
        }
        return null;
    }

    private function ensureBillingDataCompleted()
    {
        $errorMsg = 'Debés completar tus datos fiscales para continuar.';

        if (auth('customer')->check()) {
            $customer = auth('customer')->user();

            $billing = $customer?->billingData()
                ->where('is_default', 1)
                ->first() ?? $customer?->billingData;

            if ($billing &&
                filled($billing->business_name) &&
                filled($billing->document_number) &&
                filled($billing->tax_status)) {
                return null;
            }

            return redirect()->route('front.checkout.index')->with('error', $errorMsg);
        }

        $billing = (array) session('guest_checkout.billing', []);
        if (filled($billing['business_name'] ?? null) &&
            filled($billing['document_number'] ?? null) &&
            filled($billing['tax_status'] ?? null)) {
            return null;
        }

        return redirect()->route('front.checkout.index')->with('error', $errorMsg);
    }

    private function ensureShipmentSelected(): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->isDigitalOnlyCheckout()) {
            $this->clearShippingStateForDigitalCheckout();
            return null;
        }

        $shipmentMethodId = (int) session('checkout.shipment_method_id');
        if (!$shipmentMethodId) {
            return redirect()->route('front.checkout.index')
                ->with('error', 'Seleccioná un método de envío para continuar.');
        }

        $method = ShipmentMethod::available()->find($shipmentMethodId);
        if (!$method) {
            $this->forgetShipmentSelectionState();
            return redirect()->route('front.checkout.index')
                ->with('error', 'Seleccioná un método de envío para continuar.');
        }

        if (!$this->pluginAllowsMethod($method->plugin_key ?? null)) {
            $this->forgetShipmentSelectionState();
            return redirect()->route('front.checkout.index')
                ->with('error', 'El método de envío seleccionado ya no está disponible. Elegí otro.');
        }

        if ($this->hasCheckoutDestinationData()) {
            $cart = app(CartService::class);
            if (!$cart->isEmpty()) {
                $availableMethods = $this->resolveShipmentMethodsForDestination(
                    $cart,
                    $this->resolveCheckoutDestinationContext($cart)
                );

                $selectedMethod = $availableMethods->firstWhere('id', $shipmentMethodId);
                if (!$selectedMethod) {
                    $this->forgetShipmentSelectionState();

                    return redirect()->route('front.checkout.shipment')
                        ->with('error', 'El método de envío seleccionado ya no aplica para este destino. Elegí otro.');
                }

                session([
                    'shipping.package_plan' => (array) ($selectedMethod->shipping_package_plan ?? []),
                ]);
            }
        }

        return null;
    }

    private function ensureShippingDataCompleted(): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->isDigitalOnlyCheckout()) {
            return null;
        }

        $errorMsg = 'Debés completar tus datos de envío para continuar.';

        if (auth('customer')->check()) {
            $customer = auth('customer')->user();
            $addr = $customer?->address;

            if ($addr &&
                filled($addr->address_line) &&
                filled($addr->postal_code) &&
                filled($addr->locality_id)) {
                return null;
            }

            return redirect()->route('front.checkout.index')->with('error', $errorMsg);
        }

        $shipping = (array) session('guest_checkout.shipping', []);
        if (filled($shipping['name'] ?? null) &&
            filled($shipping['email'] ?? null) &&
            filled($shipping['address'] ?? null) &&
            filled($shipping['postal_code'] ?? null) &&
            filled($shipping['locality_id'] ?? null)) {
            return null;
        }

        return redirect()->route('front.checkout.index')->with('error', $errorMsg);
    }

    private function isDigitalOnlyCheckout(?CartService $cart = null): bool
    {
        if (self::DIGITAL_ONLY_CHECKOUT) {
            return true;
        }

        $cart = $cart ?: app(CartService::class);

        if ($cart->isEmpty()) {
            return true;
        }

        foreach ($cart->getCartItems() as $item) {
            $product = $item->product;
            if (!$product) continue;

            $isDigitalProduct = (bool) ($product->is_digital ?? false);
            $hasDownloadFile  = (bool) ($product->has_downloadable_files ?? false);

            if (!$isDigitalProduct && !$hasDownloadFile) {
                return false;
            }
        }

        return true;
    }

    private function clearShippingStateForDigitalCheckout(?CartService $cart = null): void
    {
        if (!$this->isDigitalOnlyCheckout($cart)) {
            return;
        }

        $this->forgetShipmentSelectionState();
    }

    private function provisionGuestCustomerFromCheckout(array $guestShipping, array $billingData = []): ?Customer
    {
        $email = trim((string) ($guestShipping['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $name = trim((string) ($guestShipping['name'] ?? ''));
        if ($name === '') {
            $name = 'Cliente';
        }

        $phone = trim((string) ($guestShipping['phone'] ?? ''));
        $phone = $phone !== '' ? $phone : null;

        $dniDigits = $this->normalizeDniDigits((string) ($guestShipping['document_number'] ?? ''));

        $customer = Customer::where('email', $email)->first();
        if ($customer) {
            $update = [
                'name' => $name,
                'phone' => $phone,
                'is_active' => true,
            ];

            if ($dniDigits) {
                $update['document'] = $dniDigits;
                $update['password'] = Hash::make($dniDigits);
            }

            $customer->update($update);
        } else {
            $customer = Customer::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($dniDigits ?: Str::random(20)),
                'phone' => $phone,
                'document' => $dniDigits,
                'is_active' => true,
            ]);
        }

        $taxStatus = trim((string) ($billingData['tax_status'] ?? 'Consumidor Final'));
        $allowedTaxStatuses = [
            'Responsable Inscripto',
            'Monotributista',
            'Consumidor Final',
            'Exento',
        ];
        if (!in_array($taxStatus, $allowedTaxStatuses, true)) {
            $taxStatus = 'Consumidor Final';
        }

        $billingName = trim((string) ($billingData['business_name'] ?? $name));
        $billingDoc = trim((string) ($billingData['document_number'] ?? ($dniDigits ?? '')));

        if ($billingName === '') {
            $billingName = $name;
        }

        if ($billingDoc !== '' || $billingName !== '') {
            $customer->billingData()->updateOrCreate(
                ['is_default' => 1],
                [
                    'business_name' => $billingName,
                    'document_number' => $billingDoc,
                    'tax_status' => $taxStatus,
                    'address_line' => (string) ($billingData['address_line'] ?? ''),
                    'city' => (string) ($billingData['city'] ?? ''),
                    'province' => (string) ($billingData['province'] ?? ''),
                    'postal_code' => (string) ($billingData['postal_code'] ?? ''),
                    'country' => (string) ($billingData['country'] ?? 'Argentina'),
                    'is_default' => 1,
                ]
            );
        }

        return $customer;
    }

    protected function cartSignature(CartService $cart): string
    {
        $rows = [];
        foreach ($cart->getCartItems() as $it) {
            $rows[] = [
                'product_id' => (int) $it->product_id,
                'attr'       => $it->attribute_value_id ?? null,
                'qty'        => (int) $it->quantity,
                'price'      => (float) $it->price,
            ];
        }
        usort($rows, fn($a, $b) => [$a['product_id'], $a['attr']] <=> [$b['product_id'], $b['attr']]);

        return md5(json_encode([
            'items'  => $rows,
            'total'  => (float) $cart->getTotalWithDiscount(),
            'shipId' => session('checkout.shipment_method_id'),
        ]));
    }

    protected function ensureCartSigFresh(CartService $cart): void
    {
        $sig = $this->cartSignature($cart);
        if (session('checkout.cart_sig') !== $sig) {
            session()->forget(['checkout.order_id', 'checkout.amount']);
            session(['checkout.cart_sig' => $sig]);
        }
    }

    public function showPersonalData(CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }
        $this->ensureCartSigFresh($cart);
        if ($this->isDigitalOnlyCheckout($cart)) {
            return redirect()->route('front.checkout.index');
        }

        $customer = auth('customer')->user();
        $guest    = session()->has('guest_checkout') || session()->has('guest_checkout.shipping');

        if ($customer) $customer->load(['address.locality.province', 'billingData']);

        return view('front.checkout.personal-data', compact('customer', 'guest'));
    }

    public function storePersonalData(Request $request, CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }
        $this->ensureCartSigFresh($cart);

        if ($this->isDigitalOnlyCheckout($cart)) {
            $this->persistDigitalCheckoutData($request);
            $this->clearShippingStateForDigitalCheckout($cart);
            session()->forget(['payment_method_id', 'checkout.order_id', 'checkout.amount']);
            return redirect()->route('front.checkout.payment');
        }

        $previousLocationSignature = $this->currentCheckoutLocationSignature();
        $this->persistCheckoutPersonalData($request);
        $currentLocationSignature = $this->currentCheckoutLocationSignature();

        if ($previousLocationSignature !== $currentLocationSignature) {
            $this->forgetShipmentSelectionState(true);
        }

        return redirect()->route('front.checkout.shipment');
    }

    protected function persistCheckoutPersonalData(Request $request): void
    {
        $rules = [
            // Datos personales
            'name'            => 'required|string|max:255',
            'email'           => 'required|email',
            'phone'           => 'nullable|string|max:30',
            'document_number' => ['nullable', 'string', 'max:10', 'regex:/^(?:\d{8}|\d{2}\.\d{3}\.\d{3})$/'],

            // Dirección
            'address'      => 'required|string|max:255',
            'postal_code'  => 'required|string|max:10',
            'country'      => 'required|string|max:100',

            // Normalizados
            'province_id'  => 'required|exists:provinces,id',
            'locality_id'  => 'required|exists:localities,id',

            // Facturación
            'modify_billing' => 'nullable|boolean',
            'billing_name'   => 'nullable|string|max:255',
            'document'       => 'nullable|string|max:50',
            'tax_status'     => 'nullable|in:Consumidor Final,Monotributista,Responsable Inscripto,Exento',
        ];

        $messages = [
            'document_number.regex' => 'El DNI debe tener formato NN.NNN.NNN.',
            'province_id.required' => 'No pudimos identificar tu provincia. Verificá el código postal o reintentá detectar ubicación.',
            'province_id.exists'   => 'La provincia seleccionada no es válida.',
            'locality_id.required' => 'No pudimos identificar tu localidad. Verificá el código postal o reintentá detectar ubicación.',
            'locality_id.exists'   => 'La localidad seleccionada no es válida.',
        ];

        $validated = $request->validate($rules, $messages);
        if (array_key_exists('document_number', $validated)) {
            $validated['document_number'] = $this->normalizeDniDigits($validated['document_number'] ?? null);
        }

        $locality = Locality::with('province')->findOrFail($validated['locality_id']);

        $cityName     = $locality->name;
        $provinceName = $locality->province ? $locality->province->name : '';

        $validated['city']     = $cityName;
        $validated['province'] = $provinceName;

        $modifyBilling = $request->boolean('modify_billing');

        $fallbackBillingName = trim((string) ($validated['name'] ?? ''));
        $fallbackDocument    = trim((string) ($validated['document_number'] ?? ''));
        if ($fallbackDocument === '' && auth('customer')->check()) {
            $fallbackDocument = trim((string) (auth('customer')->user()->document ?? ''));
        }

        $billingRow = auth('customer')->check() ? auth('customer')->user()->billingData : null;
        $billingComplete = $billingRow &&
            filled($billingRow->business_name) &&
            filled($billingRow->document_number) &&
            filled($billingRow->tax_status);

        $billingName = trim((string) ($validated['billing_name'] ?? ''));
        $billingDoc  = trim((string) ($validated['document'] ?? ''));
        $taxStatus   = trim((string) ($validated['tax_status'] ?? ''));

        if (!$modifyBilling && $billingComplete) {
            $billingName = (string) $billingRow->business_name;
            $billingDoc  = (string) $billingRow->document_number;
            $taxStatus   = (string) $billingRow->tax_status;
        }

        if ($billingName === '') $billingName = $fallbackBillingName;
        if ($billingDoc === '')  $billingDoc  = $fallbackDocument;
        if ($taxStatus === '')   $taxStatus   = 'Consumidor Final';

        if (!auth('customer')->check()) {
            session(['guest_checkout' => true]);
            session([
                'guest_checkout.shipping' => [
                    'name'            => $validated['name'],
                    'email'           => $validated['email'],
                    'document_number' => $validated['document_number'] ?? '',
                    'phone'           => $validated['phone'] ?? null,
                    'address'         => $validated['address'],
                    'postal_code'     => $validated['postal_code'],
                    'city'            => $validated['city'],
                    'province'        => $validated['province'],
                    'country'         => $validated['country'],
                    'province_id'     => $locality->province_id,
                    'locality_id'     => $validated['locality_id'],
                ],
            ]);

            session([
                'guest_checkout.billing' => [
                    'business_name'   => $billingName,
                    'document_number' => $billingDoc,
                    'tax_status'      => $taxStatus,
                    'address_line'    => $validated['address'],
                    'city'            => $validated['city'],
                    'province'        => $validated['province'],
                    'postal_code'     => $validated['postal_code'],
                    'country'         => $validated['country'],
                    'is_default'      => 1,
                ],
            ]);
        } else {
            $customer = auth('customer')->user();

            $customerUpdate = [
                'name'  => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ];
            if (!empty($validated['document_number'])) {
                $customerUpdate['document'] = $validated['document_number'];
            }
            $customer->update($customerUpdate);

            $customer->address()->updateOrCreate(
                ['is_default' => 1],
                [
                    'title'        => 'Domicilio principal',
                    'address_line' => $validated['address'],
                    'postal_code'  => $validated['postal_code'],
                    'city'         => $validated['city'],
                    'province'     => $validated['province'],
                    'country'      => $validated['country'],
                    'locality_id'  => $validated['locality_id'],
                    'is_default'   => 1,
                ]
            );

            if ($modifyBilling || !$billingComplete) {
                $customer->billingData()->updateOrCreate(
                    ['is_default' => 1],
                    [
                        'business_name'   => $billingName,
                        'document_number' => $billingDoc,
                        'tax_status'      => $taxStatus,
                        'address_line'    => $validated['address'],
                        'city'            => $validated['city'],
                        'province'        => $validated['province'],
                        'postal_code'     => $validated['postal_code'],
                        'country'         => $validated['country'],
                        'is_default'      => 1,
                    ]
                );
            }
        }
    }

    protected function persistDigitalCheckoutData(Request $request): void
    {
        $rules = [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'document_number' => ['required', 'string', 'max:10', 'regex:/^(?:\d{8}|\d{2}\.\d{3}\.\d{3})$/'],
            'modify_billing'  => 'nullable|boolean',
            'billing_name'    => 'nullable|string|max:255',
            'document'        => 'nullable|string|max:50',
            'tax_status'      => 'nullable|in:Consumidor Final,Monotributista,Responsable Inscripto,Exento',
        ];

        $messages = [
            'document_number.regex' => 'El DNI debe tener formato NN.NNN.NNN.',
        ];

        $validated = $request->validate($rules, $messages);
        $dniDigits = $this->normalizeDniDigits($validated['document_number'] ?? null);

        $modifyBilling = $request->boolean('modify_billing');
        $fallbackBillingName = trim((string) ($validated['name'] ?? ''));
        $fallbackDocument    = trim((string) ($dniDigits ?? ''));

        if ($fallbackDocument === '' && auth('customer')->check()) {
            $fallbackDocument = trim((string) (auth('customer')->user()->document ?? ''));
        }

        $billingRow = auth('customer')->check() ? auth('customer')->user()->billingData : null;
        $billingComplete = $billingRow &&
            filled($billingRow->business_name) &&
            filled($billingRow->document_number) &&
            filled($billingRow->tax_status);

        $billingName = trim((string) ($validated['billing_name'] ?? ''));
        $billingDoc  = trim((string) ($validated['document'] ?? ''));
        $taxStatus   = trim((string) ($validated['tax_status'] ?? ''));

        if (!$modifyBilling && $billingComplete) {
            $billingName = (string) $billingRow->business_name;
            $billingDoc  = (string) $billingRow->document_number;
            $taxStatus   = (string) $billingRow->tax_status;
        }

        if ($billingName === '') $billingName = $fallbackBillingName;
        if ($billingDoc === '')  $billingDoc  = $fallbackDocument;
        if ($taxStatus === '')   $taxStatus   = 'Consumidor Final';

        if (!auth('customer')->check()) {
            session(['guest_checkout' => true]);
            session([
                'guest_checkout.shipping' => [
                    'name'            => $validated['name'],
                    'email'           => $validated['email'],
                    'document_number' => $dniDigits ?? '',
                    'phone'           => $validated['phone'] ?? null,
                    // Campos vacíos por compatibilidad con lógica existente.
                    'address'         => '',
                    'postal_code'     => '',
                    'city'            => '',
                    'province'        => '',
                    'country'         => 'Argentina',
                    'province_id'     => null,
                    'locality_id'     => null,
                ],
            ]);

            session([
                'guest_checkout.billing' => [
                    'business_name'   => $billingName,
                    'document_number' => $billingDoc,
                    'tax_status'      => $taxStatus,
                    'address_line'    => '',
                    'city'            => '',
                    'province'        => '',
                    'postal_code'     => '',
                    'country'         => 'Argentina',
                    'is_default'      => 1,
                ],
            ]);

            return;
        }

        $customer = auth('customer')->user();
        $customerUpdate = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];
        if (!empty($dniDigits)) {
            $customerUpdate['document'] = $dniDigits;
        }
        $customer->update($customerUpdate);

        $currentBilling = $customer->billingData()
            ->where('is_default', 1)
            ->first() ?? $customer->billingData;

        if ($modifyBilling || !$billingComplete) {
            $customer->billingData()->updateOrCreate(
                ['is_default' => 1],
                [
                    'business_name'   => $billingName,
                    'document_number' => $billingDoc,
                    'tax_status'      => $taxStatus,
                    'address_line'    => $currentBilling->address_line ?? '',
                    'city'            => $currentBilling->city ?? '',
                    'province'        => $currentBilling->province ?? '',
                    'postal_code'     => $currentBilling->postal_code ?? '',
                    'country'         => $currentBilling->country ?? 'Argentina',
                    'is_default'      => 1,
                ]
            );
        }
    }

    public function storeGuest(Request $request, CartService $cart, LocationResolveService $resolver)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        if ($this->isDigitalOnlyCheckout($cart)) {
            $this->persistDigitalCheckoutData($request);
            $this->clearShippingStateForDigitalCheckout($cart);
            session()->forget(['payment_method_id', 'checkout.order_id', 'checkout.amount']);

            return redirect()->route('front.checkout.payment');
        }

        $data = $request->validate([
            'email'            => 'required|email|max:255',
            'document_number'  => ['required', 'string', 'max:10', 'regex:/^(?:\d{8}|\d{2}\.\d{3}\.\d{3})$/'],
            'postal_code'      => 'required|string|max:10',
            'province_id'      => 'nullable|exists:provinces,id',
            'locality_id'      => 'nullable|exists:localities,id',
            'shipment_method_id' => 'required|exists:shipment_methods,id',
            'enviacom_rate_id'   => 'nullable|string|max:120',
            'enviacom_amount'    => 'nullable|numeric|min:0',
            'enviacom_service'   => 'nullable|string|max:255',
            'enviacom_carrier'   => 'nullable|string|max:120',
            'enviacom_branchCode'=> 'nullable|string|max:120',
            'lat'               => 'nullable|numeric',
            'lon'               => 'nullable|numeric',
        ], [
            'document_number.regex' => 'El DNI debe tener formato NN.NNN.NNN.',
        ]);

        $postcode = preg_replace('/\\s+/', '', (string) ($data['postal_code'] ?? ''));

        // Tomamos ubicación previa para invalidar selección/orden si cambió el destino.
        $prevPostcode = null;
        $prevLocalityId = null;
        $prevProvinceId = null;

        if (auth('customer')->check()) {
            $addr = auth('customer')->user()?->address;
            $prevPostcode = $addr?->postal_code;
            $prevLocalityId = $addr?->locality_id;
            if ($prevLocalityId) {
                $prevProvinceId = Locality::where('id', $prevLocalityId)->value('province_id') ?: null;
            }
        } else {
            $prevPostcode   = session('guest_checkout.shipping.postal_code');
            $prevLocalityId = session('guest_checkout.shipping.locality_id');
            $prevProvinceId = session('guest_checkout.shipping.province_id');
        }

        // Resolver locality_id si no vino (robustez: CP/coords)
        $localityId = !empty($data['locality_id']) ? (int) $data['locality_id'] : null;
        $provinceId = !empty($data['province_id']) ? (int) $data['province_id'] : null;
        $countryId  = null;
        $resolvedCity = '';
        $resolvedProvince = '';
        $cityName = '';
        $provinceName = '';

        if (!$localityId) {
            $hasCoords = array_key_exists('lat', $data) && array_key_exists('lon', $data)
                && $data['lat'] !== null && $data['lon'] !== null;

            $resolved = $resolver->resolve(
                $postcode,
                $hasCoords ? (float) $data['lat'] : null,
                $hasCoords ? (float) $data['lon'] : null,
                'AR'
            );

            if (($resolved['ok'] ?? false) !== true) {
                return back()
                    ->withInput()
                    ->with('error', $resolved['error'] ?? 'No se pudo detectar la localidad.')
                    ->withErrors([
                        'postal_code' => 'No pudimos identificar tu ubicación. Probá detectar ubicación o verificá el código postal.',
                    ]);
            }

            $localityId = !empty($resolved['locality_id']) ? (int) $resolved['locality_id'] : null;
            $provinceId = !empty($resolved['province_id']) ? (int) $resolved['province_id'] : $provinceId;
            $countryId  = !empty($resolved['country_id']) ? (int) $resolved['country_id'] : null;
            $resolvedCity = trim((string) ($resolved['city'] ?? ''));
            $resolvedProvince = trim((string) ($resolved['province'] ?? ''));
            $resolvedCityNorm = $this->normalizeLocalityName($resolvedCity);
            if ($resolvedCityNorm !== '') {
                $resolvedCity = $resolvedCityNorm;
            }
            $cityName = $resolvedCity;
            $provinceName = $resolvedProvince;

            if ($localityId) {
                $request->merge([
                    'locality_id' => $localityId,
                    'province_id' => $provinceId,
                ]);
            }
        }

        if ($localityId) {
            $locality = Locality::with('province')->findOrFail($localityId);
            $provinceId = (int) ($locality->province_id ?? $provinceId);
            $countryId  = (int) ($locality->province?->country_id ?? $countryId) ?: $countryId;
            $cityName = (string) ($locality->name ?? $cityName);
            $provinceName = (string) ($locality->province?->name ?? $provinceName);
        } elseif ($provinceId && !$countryId) {
            $countryId = Province::where('id', $provinceId)->value('country_id') ?: null;
        }

        if ($provinceName === '' && $provinceId) {
            $provinceName = (string) (Province::where('id', $provinceId)->value('name') ?? '');
        }

        $countryName = $this->resolveCountryName($countryId, 'AR');

        // Si no pudimos mapear localidad_id (DB), intentamos un match simple por nombre dentro de la provincia.
        if (!$localityId && $provinceId && $resolvedCity !== '') {
            $localityId = Locality::where('province_id', $provinceId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($resolvedCity)])
                ->value('id');

            if (!$localityId) {
                $localityId = Locality::where('province_id', $provinceId)
                    ->where('name', 'like', '%' . $resolvedCity . '%')
                    ->value('id');
            }
        }

        // Último recurso: si la localidad no existe en DB, la creamos para poder avanzar.
        if (!$localityId && $provinceId && $resolvedCity !== '') {
            $created = Locality::firstOrCreate([
                'province_id' => $provinceId,
                'name'        => $resolvedCity,
            ]);
            $localityId = (int) $created->id;
        }

        if ($localityId) {
            $request->merge([
                'locality_id' => $localityId,
                'province_id' => $provinceId,
            ]);
        } else {
            return back()
                ->withInput()
                ->with('error', 'No pudimos identificar tu localidad. Probá buscar tu ubicación por código postal o reintentar con tu ubicación.')
                ->withErrors([
                    'postal_code' => 'No pudimos identificar tu localidad. Verificá el código postal o reintentá detectar ubicación.',
                ]);
        }

        $locationChanged = (string) ($prevPostcode ?? '') !== (string) $postcode
            || (int) ($prevLocalityId ?? 0) !== (int) ($localityId ?? 0)
            || (int) ($prevProvinceId ?? 0) !== (int) ($provinceId ?? 0);

        if ($locationChanged) {
            $this->forgetShipmentSelectionState(true);
        }

        $request->merge(['postal_code' => $postcode]);
        if (!$request->filled('country')) {
            $request->merge(['country' => 'Argentina']);
        }

        // Guardar datos de envío/facturación (guest en sesión o customer en DB)
        $this->persistCheckoutPersonalData($request);

        // Persistir coords (solo para guest; no se usan para validar, solo para recordar)
        if (!auth('customer')->check()) {
            $existingShipping = (array) session('guest_checkout.shipping', []);
            session([
                'guest_checkout.shipping' => array_merge($existingShipping, [
                    'lat' => array_key_exists('lat', $data) ? $data['lat'] : null,
                    'lon' => array_key_exists('lon', $data) ? $data['lon'] : null,
                ]),
            ]);
        }

        // Validar que el método elegido esté disponible para ese destino
        $chosenMethodId = (int) $data['shipment_method_id'];
        $availableMethods = $this->resolveShipmentMethodsForDestination(
            $cart,
            [
                'country_id' => $countryId,
                'province_id' => $provinceId,
                'locality_id' => $localityId,
                'country_code' => 'AR',
                'country_name' => $countryName,
                'province_name' => $provinceName,
                'city_name' => $cityName,
                'postcode' => $postcode,
                'lat' => array_key_exists('lat', $data) && $data['lat'] !== null ? (float) $data['lat'] : null,
                'lon' => array_key_exists('lon', $data) && $data['lon'] !== null ? (float) $data['lon'] : null,
            ],
            false,
            $resolver
        );

        $chosenMethod = $availableMethods->firstWhere('id', $chosenMethodId);
        if (!$chosenMethod) {
            return back()
                ->withInput()
                ->with('error', 'El método de envío seleccionado no está disponible para tu destino.')
                ->withErrors([
                    'shipment_method_id' => 'Seleccioná otro método de envío.',
                ]);
        }

        if (($chosenMethod->plugin_key ?? null) === 'enviacom') {
            $request->validate([
                'enviacom_rate_id'    => 'required|string|max:120',
                'enviacom_amount'     => 'required|numeric|min:0',
                'enviacom_service'    => 'required|string|max:255',
                'enviacom_carrier'    => 'required|string|max:120',
                'enviacom_branchCode' => 'nullable|string|max:120',
            ]);
        } else {
            // Si veníamos de Envia, limpiar override para que no quede un costo viejo.
            if (session('shipping.override.source') === 'enviacom') {
                session()->forget('shipping.override');
            }
        }

        session([
            'checkout.shipment_method_id' => $chosenMethodId,
            'shipping.package_plan' => (array) ($chosenMethod->shipping_package_plan ?? []),
        ]);

        // El plugin Envia escucha este evento
        Event::dispatch('checkout.shipment.selected', $request);

        return redirect()->route('front.checkout.payment');
    }

    /**
     * Resuelve country_id, province_id y locality_id del checkout
     * (tanto para cliente logueado como invitado).
     */
    protected function resolveCheckoutLocation(): array
    {
        $countryId  = null;
        $provinceId = null;
        $localityId = null;

        $customer = auth('customer')->user();

        if ($customer && $customer->address && $customer->address->locality) {
            $loc = $customer->address->locality()->with('province.country')->first();
            if ($loc) {
                $localityId = $loc->id;
                $provinceId = $loc->province_id;
                $countryId  = $loc->province->country_id ?? null;
            }
        } elseif ($shipping = session('guest_checkout.shipping')) {
            $shipping = (object) $shipping;
            if (!empty($shipping->locality_id)) {
                $loc = Locality::with('province.country')->find($shipping->locality_id);
                if ($loc) {
                    $localityId = $loc->id;
                    $provinceId = $loc->province_id;
                    $countryId  = $loc->province->country_id ?? null;
                }
            } elseif (!empty($shipping->province_id)) {
                $provinceId = (int) $shipping->province_id;
                $countryId = Province::where('id', $provinceId)->value('country_id') ?: null;
            }
        }

        return [$countryId, $provinceId, $localityId];
    }

    public function apiShipmentMethods(Request $request, CartService $cart, LocationResolveService $resolver)
    {
        if ($cart->isEmpty()) {
            return response()->json([
                'ok' => true,
                'methods' => [],
            ]);
        }

        if ($this->isDigitalOnlyCheckout($cart)) {
            $this->clearShippingStateForDigitalCheckout($cart);
            return response()->json([
                'ok' => true,
                'methods' => [],
                'enviacom' => [
                    'active' => false,
                    'method_id' => null,
                    'rates' => [],
                ],
            ]);
        }

        $data = $request->validate([
            'postcode'    => 'nullable|string|max:20',
            'locality_id' => 'nullable|exists:localities,id',
            'province_id' => 'nullable|exists:provinces,id',
            'lat'         => 'nullable|numeric',
            'lon'         => 'nullable|numeric',
            'country'     => 'nullable|string|size:2',
        ]);

        $postcode = isset($data['postcode']) ? preg_replace('/\\s+/', '', (string) $data['postcode']) : null;
        $countryCode = strtoupper(trim((string) ($data['country'] ?? 'AR'))) ?: 'AR';

        $localityId = !empty($data['locality_id']) ? (int) $data['locality_id'] : null;
        $provinceId = !empty($data['province_id']) ? (int) $data['province_id'] : null;
        $countryId  = null;

        $cityName = '';
        $provinceName = '';

        if ($localityId) {
            $loc = Locality::with('province.country')->find($localityId);
            if ($loc) {
                $cityName = (string) ($loc->name ?? '');
                $provinceName = (string) ($loc->province?->name ?? '');
                $provinceId = (int) ($loc->province_id ?? $provinceId);
                $countryId  = (int) ($loc->province?->country_id ?? $countryId);
            }
        }

        if (!$localityId && $provinceId) {
            $prov = Province::with('country')->find($provinceId);
            if ($prov) {
                $provinceName = (string) ($prov->name ?? '');
                $countryId = (int) ($prov->country_id ?? $countryId);
            }
        }

        if (!$localityId && !$provinceId) {
            $hasCoords = array_key_exists('lat', $data) && array_key_exists('lon', $data)
                && $data['lat'] !== null && $data['lon'] !== null;

            $resolved = $resolver->resolve(
                $postcode,
                $hasCoords ? (float) $data['lat'] : null,
                $hasCoords ? (float) $data['lon'] : null,
                $countryCode
            );

            if (($resolved['ok'] ?? false) !== true) {
                return response()->json([
                    'ok' => false,
                    'error' => $resolved['error'] ?? 'No se pudo resolver la ubicación.',
                ], 422);
            }

            $postcode      = preg_replace('/\\s+/', '', (string) ($resolved['postcode'] ?? $postcode ?? ''));
            $localityId    = !empty($resolved['locality_id']) ? (int) $resolved['locality_id'] : null;
            $provinceId    = !empty($resolved['province_id']) ? (int) $resolved['province_id'] : null;
            $countryId     = !empty($resolved['country_id']) ? (int) $resolved['country_id'] : null;
            $cityName      = (string) ($resolved['city'] ?? '');
            $provinceName  = (string) ($resolved['province'] ?? '');
        }

        if ($localityId && ($cityName === '' || $provinceName === '' || !$provinceId || !$countryId)) {
            $loc = Locality::with('province.country')->find($localityId);
            if ($loc) {
                if ($cityName === '') $cityName = (string) ($loc->name ?? '');
                if ($provinceName === '') $provinceName = (string) ($loc->province?->name ?? '');
                if (!$provinceId) $provinceId = (int) ($loc->province_id ?? $provinceId);
                if (!$countryId) $countryId  = (int) ($loc->province?->country_id ?? $countryId);
            }
        }

        if ($provinceId && !$countryId) {
            $countryId = Province::where('id', $provinceId)->value('country_id') ?: null;
        }

        $countryName = $this->resolveCountryName($countryId, $countryCode);
        $productsTotal = (float) $cart->getSubtotal();

        $shipmentMethods = $this->resolveShipmentMethodsForDestination(
            $cart,
            [
                'country_id' => $countryId,
                'province_id' => $provinceId,
                'locality_id' => $localityId,
                'country_code' => $countryCode,
                'country_name' => $countryName,
                'province_name' => $provinceName,
                'city_name' => $cityName,
                'postcode' => $postcode,
                'lat' => array_key_exists('lat', $data) && $data['lat'] !== null ? (float) $data['lat'] : null,
                'lon' => array_key_exists('lon', $data) && $data['lon'] !== null ? (float) $data['lon'] : null,
            ],
            true,
            $resolver
        );

        $methods = $shipmentMethods->map(function ($method) use ($productsTotal) {
            $shippingDiscount = 0.0;
            if (in_array((string) $method->discount_type, ['percent', 'percentage'], true)) {
                $shippingDiscount = round($productsTotal * ((float) $method->discount_value / 100), 2);
            } elseif (in_array((string) $method->discount_type, ['fixed', 'amount'], true)) {
                $shippingDiscount = round((float) $method->discount_value, 2);
            }

            return [
                'id' => (int) $method->id,
                'name' => (string) $method->name,
                'amount' => (float) $method->amount,
                'delay' => $method->delay ? (string) $method->delay : null,
                'shipping_discount' => (float) $shippingDiscount,
                'is_pickup' => (bool) ($method->is_pickup ?? false),
                'match_type' => (string) ($method->destination_match_type ?? 'exact'),
                'distance_km' => $method->destination_distance_km !== null ? (float) $method->destination_distance_km : null,
                'point_name' => $method->destination_point_name ? (string) $method->destination_point_name : null,
                'package_count' => (int) data_get($method, 'shipping_package_plan.package_count', 0),
                'requires_boxing' => (bool) data_get($method, 'shipping_package_plan.requires_boxing', false),
            ];
        });

        $enviacom = [
            'active' => false,
            'method_id' => null,
            'rates' => [],
        ];

        if ($this->pluginAllowsMethod('enviacom') && class_exists(EnviaClient::class)) {
            $enviacom['active'] = true;
            $enviacomMethodId = ShipmentMethod::where('plugin_key', 'enviacom')->orderBy('id')->value('id');
            $enviacom['method_id'] = $enviacomMethodId ? (int) $enviacomMethodId : null;

            $pc = trim((string) ($postcode ?? ''));
            $cityTrim = trim((string) $cityName);
            if (!$enviacom['method_id']) {
                $enviacom['active'] = false;
            } elseif ($pc === '' || $cityTrim === '') {
                $enviacom['error'] = 'Envia.com: faltan datos de destino (ciudad y/o código postal).';
            } else {
                try {
                    $client = app(EnviaClient::class);
                    $origin = $client->getConfiguredOrigin();
                    $cfg    = $client->config();
                    $d      = (array) ($cfg['defaults'] ?? []);

                    $items = [];
                    foreach ($cart->getCartItems() as $it) {
                        $p = $it->product;
                        if (!$p) continue;

                        $weight = (float) ($p->weight ?? 0);
                        if ($weight <= 0) $weight = (float) ($d['default_weight_kg'] ?? 0.7);

                        $length = (float) ($p->length ?? 0);
                        if ($length <= 0) $length = (float) ($d['default_length_cm'] ?? 20);

                        $width = (float) ($p->width ?? 0);
                        if ($width <= 0) $width = (float) ($d['default_width_cm'] ?? 10);

                        $height = (float) ($p->height ?? 0);
                        if ($height <= 0) $height = (float) ($d['default_height_cm'] ?? 10);

                        $items[] = [
                            'description' => (string) $p->name,
                            'quantity'    => (int) $it->quantity,
                            'weight'      => $weight,
                            'length'      => $length,
                            'width'       => $width,
                            'height'      => $height,
                        ];
                    }

                    if (!$items) {
                        $enviacom['error'] = 'Envia.com: el carrito está vacío.';
                    } else {
                        $dest = [
                            'country'     => 'AR',
                            'province'    => trim((string) $provinceName),
                            'city'        => $cityTrim,
                            'postal_code' => $pc,
                            'address'     => '',
                            'name'        => 'Cliente',
                            'email'       => '',
                            'phone'       => '',
                        ];

                        $raw = $client->quote($origin, $dest, $items);
                        $apiError = $this->extractEnviaRateError($raw);

                        $rates = $client->normalizeRates($raw);
                        usort($rates, fn ($a, $b) => ((float) ($a['amount'] ?? 0)) <=> ((float) ($b['amount'] ?? 0)));

                        $enviacom['rates'] = array_values(array_map(function ($r) {
                            return [
                                'id'       => (string) ($r['id'] ?? ''),
                                'carrier'  => (string) ($r['carrier'] ?? ''),
                                'service'  => (string) ($r['service'] ?? ''),
                                'service_slug' => (string) ($r['meta']['service'] ?? ($r['service'] ?? '')),
                                'amount'   => (float) ($r['amount'] ?? 0),
                                'dropOff'  => (int) ($r['dropOff'] ?? 0),
                                'branches' => (array) ($r['branches'] ?? []),
                                'deliveryEstimate'  => (string) ($r['deliveryEstimate'] ?? ''),
                            ];
                        }, $rates));

                        if (!$enviacom['rates'] && $apiError) {
                            $enviacom['error'] = $apiError['public_message'] ?? 'Envia.com: no se pudieron obtener tarifas.';
                            $enviacom['details'] = $apiError['details'] ?? null;
                        }
                    }
                } catch (\Throwable $e) {
                    $enviacom['error'] = 'Envia.com: error al cotizar.';
                }
            }
        } elseif ($this->pluginAllowsMethod('enviacom') && !class_exists(EnviaClient::class)) {
            $enviacom['error'] = 'Envia.com está marcado como activo, pero el cliente no está instalado en este entorno.';
        }

        return response()->json([
            'ok' => true,
            'location' => [
                'city' => trim($cityName),
                'province' => trim($provinceName),
                'postcode' => $postcode ? (string) $postcode : null,
            ],
            'products_total' => $productsTotal,
            'methods' => $methods,
            'enviacom' => $enviacom,
        ]);
    }

    public function apiTribunoSubscriptionSync(Request $request, CartService $cart)
    {
        $validated = $request->validate([
            'dni' => 'nullable|string|max:20',
        ]);

        $dniRaw = $validated['dni'] ?? null;
        $dniDigits = $this->normalizeDniDigits($dniRaw);
        if ($dniDigits !== null && strlen($dniDigits) !== 8) {
            $dniDigits = null;
        }

        // Para guest: guardamos el DNI en sesión aunque aún no haya submit del checkout.
        // Esto permite que el plugin de Tribuno calcule/aplique descuentos inmediatamente.
        if (!auth('customer')->check()) {
            $existingShipping = (array) session('guest_checkout.shipping', []);

            if ($dniDigits) {
                session([
                    'guest_checkout.shipping' => array_merge($existingShipping, [
                        'document_number' => $dniDigits,
                    ]),
                ]);
            } else {
                if (array_key_exists('document_number', $existingShipping)) {
                    unset($existingShipping['document_number']);
                    session(['guest_checkout.shipping' => $existingShipping]);
                }

                session()->forget('tribuno.subscription');
            }
        }

        // Dispara el sync (si el plugin está activo, actualiza precios y session('tribuno.subscription')).
        if (!$cart->isEmpty()) {
            $cart->getTotalWithDiscount();
        }

        $html = view('front.checkout.partials.cart-summary')->render();
        $tribuno = (array) session('tribuno.subscription', []);

        return response()->json([
            'ok' => true,
            'dni' => $dniDigits,
            'tribuno' => $tribuno,
            'html' => $html,
        ]);
    }

    // public function showShipment(CartService $cart)
    // {
    //     if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
    //     $this->ensureCartSigFresh($cart);

    //     $cartTotal = (float) $cart->getTotalWithDiscount();

    //     // 🔍 Obtenemos ubicación del checkout
    //     [$countryId, $provinceId, $localityId] = $this->resolveCheckoutLocation();

    //     $shipmentMethods = ShipmentMethod::available()
    //         // Filtrado por ubicación del método de envío
    //         ->when($countryId, function ($q) use ($countryId) {
    //             $q->where(function ($w) use ($countryId) {
    //                 $w->whereNull('country_id')->orWhere('country_id', $countryId);
    //             });
    //         })
    //         ->when($provinceId, function ($q) use ($provinceId) {
    //             $q->where(function ($w) use ($provinceId) {
    //                 $w->whereNull('province_id')->orWhere('province_id', $provinceId);
    //             });
    //         })
    //         ->when($localityId, function ($q) use ($localityId) {
    //             $q->where(function ($w) use ($localityId) {
    //                 $w->whereNull('locality_id')->orWhere('locality_id', $localityId);
    //             });
    //         })
    //         // Filtro por mínimo de carrito
    //         ->where(function ($q) use ($cartTotal) {
    //             $q->whereNull('min_cart_amount')
    //               ->orWhereRaw('CAST(min_cart_amount AS DECIMAL(10,2)) <= ?', [$cartTotal]);
    //         })
    //         ->get();

    //     return view('front.checkout.shipment', compact('shipmentMethods', 'cartTotal'));
    // }

    public function showShipment(CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);
        if ($this->isDigitalOnlyCheckout($cart)) {
            $this->clearShippingStateForDigitalCheckout($cart);
            return redirect()->route('front.checkout.payment');
        }

        $productsTotal = (float) $cart->getSubtotal();          // SOLO productos
        $cartTotal     = (float) $cart->getTotalWithDiscount(); // total actual (puede incluir envío)

        $customer = auth('customer')->user();
        if ($customer) {
            $customer->loadMissing(['address.locality.province', 'billingData']);
        }

        $destinationContext = $this->resolveCheckoutDestinationContext($cart, app(LocationResolveService::class));
        $shipmentMethods = $this->resolveShipmentMethodsForDestination($cart, $destinationContext);

        $selectedShipmentId = (int) session('checkout.shipment_method_id');
        if ($selectedShipmentId > 0) {
            $selectedMethod = $shipmentMethods->firstWhere('id', $selectedShipmentId);
            if ($selectedMethod) {
                session([
                    'shipping.package_plan' => (array) ($selectedMethod->shipping_package_plan ?? []),
                ]);
            } else {
                $this->forgetShipmentSelectionState(true);
            }
        } else {
            session()->forget('shipping.package_plan');
        }

        return view('front.checkout.shipment', compact('shipmentMethods', 'cartTotal', 'productsTotal', 'customer'));
    }

    public function storeShipment(Request $request, CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);

        if ($this->isDigitalOnlyCheckout($cart)) {
            $this->clearShippingStateForDigitalCheckout($cart);
            return redirect()->route('front.checkout.payment');
        }

        $validated = $request->validate([
            'shipment_method_id' => 'required|exists:shipment_methods,id',
        ]);

        $previousLocationSignature = $this->currentCheckoutLocationSignature();
        $this->persistCheckoutPersonalData($request);
        $currentLocationSignature = $this->currentCheckoutLocationSignature();

        if ($previousLocationSignature !== $currentLocationSignature) {
            $this->forgetShipmentSelectionState(true);
        }

        $availableMethods = $this->resolveShipmentMethodsForDestination(
            $cart,
            $this->resolveCheckoutDestinationContext($cart, app(LocationResolveService::class))
        );

        $method = $availableMethods->firstWhere('id', (int) $validated['shipment_method_id']);
        if (!$method) {
            return back()
                ->withInput()
                ->with('error', 'Este método de envío no está disponible para el destino configurado.');
        }

        if (($method->plugin_key ?? null) === 'enviacom') {
            $request->validate([
                'enviacom_rate_id'    => 'required|string|max:120',
                'enviacom_amount'     => 'required|numeric|min:0',
                'enviacom_service'    => 'required|string|max:255',
                'enviacom_carrier'    => 'required|string|max:120',
                'enviacom_branchCode' => 'nullable|string|max:120',
            ]);
        } else {
            if (session('shipping.override.source') === 'enviacom') {
                session()->forget('shipping.override');
            }
        }

        session([
            'checkout.shipment_method_id' => (int) $method->id,
            'shipping.package_plan' => (array) ($method->shipping_package_plan ?? []),
        ]);

        // El plugin Envia escucha este evento
        \Event::dispatch('checkout.shipment.selected', $request);

        return redirect()->route('front.checkout.payment');
    }

    public function showPayment(CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);
        $this->clearShippingStateForDigitalCheckout($cart);
        if ($redirect = $this->ensureShipmentSelected()) return $redirect;
        if ($redirect = $this->ensureShippingDataCompleted()) return $redirect;
        if ($redirect = $this->ensureBillingDataCompleted()) return $redirect;

        $order = $this->getOrCreatePendingOrder($cart);

        session([
            'checkout.order_id' => $order->id,
            'checkout.amount'   => $order->total,
        ]);

        \Log::info('[CHECKOUT] showPayment -> order pending asegurada', ['order_id' => $order->id]);

        $isFreeCheckout = round((float) $order->total, 2) <= 0.0;
        if ($isFreeCheckout) {
            session()->forget('payment_method_id');
            if ($order->payment_method_id !== null) {
                $order->update(['payment_method_id' => null]);
            }
        }

        if ($isFreeCheckout) {
            $paymentMethods = collect();
            $paymentMethodsCount = 0;
        } else {
            $paymentMethods = PaymentMethod::where('active', true)->get();
            $paymentMethodsCount = PaymentMethod::where('active', true)
                ->where('type', '!=', 'plugin')
                ->count();
        }

        $paymentBackRoute = $this->isDigitalOnlyCheckout($cart)
            ? route('front.checkout.index')
            : route('front.checkout.shipment');

        return view('front.checkout.payment', compact('paymentMethods', 'paymentMethodsCount', 'isFreeCheckout', 'paymentBackRoute'));
    }

    public function storePayment(Request $request, CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);
        $this->clearShippingStateForDigitalCheckout($cart);
        if ($redirect = $this->ensureShipmentSelected()) return $redirect;
        if ($redirect = $this->ensureShippingDataCompleted()) return $redirect;
        if ($redirect = $this->ensureBillingDataCompleted()) return $redirect;

        $order = $this->getOrCreatePendingOrder($cart);
        if (round((float) $order->total, 2) <= 0.0) {
            session()->forget('payment_method_id');
            return redirect()->route('front.checkout.payment');
        }

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        session(['payment_method_id' => (int) $request->payment_method_id]);

        if ($oid = session('checkout.order_id')) {
            Order::where('id', $oid)->where('status', 'pending')
                ->update(['payment_method_id' => (int) $request->payment_method_id]);
        }

        return redirect()->route('front.checkout.payment.process');
    }

    public function processPayment(CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);
        $this->clearShippingStateForDigitalCheckout($cart);
        if ($redirect = $this->ensureShipmentSelected()) return $redirect;
        if ($redirect = $this->ensureShippingDataCompleted()) return $redirect;
        if ($redirect = $this->ensureBillingDataCompleted()) return $redirect;

        $order = $this->getOrCreatePendingOrder($cart);
        if (round((float) $order->total, 2) <= 0.0) {
            session()->forget('payment_method_id');
            return redirect()->route('front.checkout.payment');
        }

        $paymentMethodId = session('payment_method_id');
        if (!$paymentMethodId) {
            return redirect()->route('front.checkout.payment')->with('error', 'Debe seleccionar un método de pago.');
        }

        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);

        return view('front.checkout.payment-process', [
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function handlePaymentMethod(Request $request, CartService $cart)
    {
        if ($redirect = $this->ensureValidCheckoutSession($cart)) return $redirect;
        $this->ensureCartSigFresh($cart);
        $this->clearShippingStateForDigitalCheckout($cart);
        if ($redirect = $this->ensureShipmentSelected()) return $redirect;
        if ($redirect = $this->ensureShippingDataCompleted()) return $redirect;
        if ($redirect = $this->ensureBillingDataCompleted()) return $redirect;

        $order = $this->getOrCreatePendingOrder($cart);
        if (round((float) $order->total, 2) <= 0.0) {
            session()->forget('payment_method_id');
            return redirect()->route('front.checkout.payment');
        }

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        session(['payment_method_id' => (int) $request->payment_method_id]);

        return redirect()->route('front.checkout.payment.process');
    }

    protected function resolveBillingData(): array
    {
        $customer = auth('customer')->user();

        if ($customer && $customer->billingData) {
            return $customer->billingData->toArray();
        }
        if (session()->has('guest_checkout.billing')) {
            return (array) session('guest_checkout.billing');
        }
        return [];
    }

    protected function buildShippingAddress($data): array
    {
        if (is_array($data)) $data = (object) $data;

        return [
            'title'        => $data->title        ?? 'Domicilio',
            'address_line' => $data->address_line ?? '',
            'city'         => $data->city         ?? '',
            'province'     => $data->province     ?? '',
            'postal_code'  => $data->postal_code  ?? '',
            'country'      => $data->country      ?? 'Argentina',
            'locality_id'  => $data->locality_id  ?? null,
        ];
    }

    protected function getOrCreatePendingOrder(CartService $cart): Order
    {
        $billingData = $this->resolveBillingData();
        $digitalOnly = $this->isDigitalOnlyCheckout($cart);

        if ($digitalOnly) {
            $this->clearShippingStateForDigitalCheckout($cart);
        }

        // Tomamos override si el plugin lo dejó en sesión (opcional)
        $shipOv       = (array) session('shipping.override', []);
        $overrideCost = (($shipOv['source'] ?? null) === 'enviacom' && isset($shipOv['amount'])) ? (float) $shipOv['amount'] : null;

        $baseSubtotal        = (float) $cart->getSubtotal();
        $baseDiscount        = (float) $cart->getDiscountTotal();
        $baseShippingDiscount= $digitalOnly ? 0.0 : (float) $cart->getShippingDiscount();
        $shippingCost        = $digitalOnly ? 0.0 : ($overrideCost ?? (float) $cart->getShippingCost());
        $shippingAddress     = $digitalOnly ? [] : $this->resolveShippingAddress();
        $shipmentMethodId    = $digitalOnly ? null : (int) (session('checkout.shipment_method_id') ?: 0);
        if ($shipmentMethodId <= 0) {
            $shipmentMethodId = null;
        }
        $computedTotal       = round($baseSubtotal - $baseDiscount + $shippingCost - $baseShippingDiscount, 2);

        if ($orderId = session('checkout.order_id')) {
            $o = Order::with('items')->find($orderId);
            if ($o && $o->status === 'pending') {
	                $o->update([
	                    'subtotal'          => $baseSubtotal,
	                    'discount'          => $baseDiscount,
	                    'shipping_cost'     => $shippingCost,
	                    'shipping_discount' => $baseShippingDiscount,
	                    'total'             => $computedTotal,
	                    'shipping_address'  => $shippingAddress,
	                    'shipment_method_id'=> $shipmentMethodId,
	                    'billing_data_json' => $billingData,
	                ]);
                $this->syncOrderItemsSnapshot($o, $cart);
                session(['checkout.order_id' => $o->id, 'checkout.amount' => $o->total]);
                return $o;
            }
            session()->forget(['checkout.order_id', 'checkout.amount']);
        }

        $customer          = auth('customer')->user();
        $guest             = (object) (session('guest_checkout.shipping') ?? []);
        $buyer             = $customer ?: $guest;

        $o = Order::create([
            'customer_id'        => $customer?->id,
            'name'               => $buyer->name  ?? null,
            'email'              => $buyer->email ?? null,
            'phone'              => $buyer->phone ?? null,
            'status'             => 'pending',
            'subtotal'           => $baseSubtotal,
            'discount'           => $baseDiscount,
            'shipping_cost'      => $shippingCost,
            'shipping_discount'  => $baseShippingDiscount,
            'total'              => $computedTotal,
            'shipping_address'   => $shippingAddress,
            'billing_data_json'  => $billingData,
            'shipment_method_id' => $shipmentMethodId,
        ]);

        $this->syncOrderItemsSnapshot($o, $cart);

        session(['checkout.order_id' => $o->id, 'checkout.amount' => $o->total]);
        \Log::info('[CHECKOUT] creada order pending', ['order_id' => $o->id]);

        return $o;
    }

    protected function syncOrderItemsSnapshot(Order $order, CartService $cart): void
    {
        $order->items()->delete();

        foreach ($cart->getCartItems() as $item) {
            $attributeValueId = null;

            if (!empty($item->attribute_values_json)) {
                $values = json_decode($item->attribute_values_json, true);
                if (!empty($values) && isset($values[0]['value_id'])) {
                    $attributeValueId = $values[0]['value_id'];
                }
            } elseif (!empty($item->attribute_value_id)) {
                $attributeValueId = $item->attribute_value_id;
            }

            $order->items()->create([
                'product_id'         => $item->product_id,
                'attribute_value_id' => $attributeValueId,
                'name'               => $item->name,
                'price'              => $item->price,
                'quantity'           => $item->quantity,
                'total'              => $item->price * $item->quantity,
            ]);
        }
    }

    protected function resolveShippingAddress(): array
    {
        $customer = auth('customer')->user();

        if ($customer && $customer->address) {
            return $this->buildShippingAddress($customer->address);
        }

        if ($guest = (object) (session('guest_checkout.shipping') ?? [])) {
            return $this->buildShippingAddress([
                'title'        => 'Domicilio',
                'address_line' => $guest->address     ?? '',
                'city'         => $guest->city        ?? '',
                'province'     => $guest->province    ?? '',
                'postal_code'  => $guest->postal_code ?? '',
                'country'      => 'Argentina',
                'locality_id'  => $guest->locality_id ?? null,
            ]);
        }

        return [
            'title'        => 'Domicilio',
            'address_line' => '',
            'city'         => '',
            'province'     => '',
            'postal_code'  => '',
            'country'      => 'Argentina',
            'locality_id'  => null,
        ];
    }

    /**
     * Crea/asegura el registro Shipment del pedido (idempotente).
     */
    protected function ensureShipment(Order $order, array $shippingAddr, ?int $shipmentMethodId, array $shipOv = []): Shipment
    {
        $packagePlan = (array) session('shipping.package_plan', []);
        $shippingData = array_merge(
            (array) ($shipOv['data'] ?? []),
            ['package_plan' => $packagePlan]
        );

        $shipment = Shipment::firstOrNew(['order_id' => $order->id]);
        $shipment->fill([
            'shipment_method_id' => $shipmentMethodId,
            'address' => $shippingAddr,
            'status' => $shipment->status ?: 'pending',
            'carrier' => $shipOv['data']['carrier'] ?? null,
            'tracking_number' => $shipment->tracking_number,
            'shipping_data_json' => $shippingData,
        ]);
        $shipment->save();

        \Log::info('[CHECKOUT] shipment ensured', [
            'order_id'    => $order->id,
            'shipment_id' => $shipment->id,
        ]);

        return $shipment;
    }

    public function finalizeOrder(Request $request, CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'El carrito está vacío.');
        }
        if ($redirect = $this->ensureValidCheckoutSession($cart)) {
            return $redirect;
        }
        $digitalOnly = $this->isDigitalOnlyCheckout($cart);
        if ($digitalOnly) {
            $this->clearShippingStateForDigitalCheckout($cart);
        }
        if ($redirect = $this->ensureShipmentSelected()) return $redirect;
        if ($redirect = $this->ensureShippingDataCompleted()) return $redirect;
        if ($redirect = $this->ensureBillingDataCompleted()) return $redirect;

        $isFreeCheckout = round((float) $cart->getTotalWithDiscount(), 2) <= 0.0;

        // 🔍 Resolver método de pago (sesión o hidden del form)
        $paymentMethodId = $isFreeCheckout ? null : (session('payment_method_id') ?? $request->input('payment_method_id'));
        $paymentMethod   = $paymentMethodId ? PaymentMethod::find($paymentMethodId) : null;
        if (!$isFreeCheckout && !$paymentMethod) {
            return redirect()->route('front.checkout.payment')
                ->with('error', 'Seleccioná un método de pago para continuar.');
        }

        // 📎 Manejo de comprobante (si el método lo requiere)
        $receiptPath = null;
        if ($paymentMethod && data_get($paymentMethod->config, 'file-upload') === "true") {
            $request->validate([
                'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            ]);

            if ($request->hasFile('comprobante')) {
                $receiptPath = $request->file('comprobante')
                    ->store('payments/comprobantes', 'public');
            }
        }

        DB::beginTransaction();
        try {
            $customer = auth('customer')->user();
            $guestData = (array) (session('guest_checkout.shipping') ?? []);
            $billingData = $this->resolveBillingData();

            if (!$customer && !empty($guestData)) {
                $customer = $this->provisionGuestCustomerFromCheckout($guestData, $billingData);
            }

            $guest = (object) $guestData;
            $buyerData = $customer ?: $guest;
            $shipmentMethodId = $digitalOnly ? null : (int) (session('checkout.shipment_method_id') ?: 0);
            if ($shipmentMethodId <= 0) {
                $shipmentMethodId = null;
            }
            $shippingAddr     = $digitalOnly ? [] : $this->resolveShippingAddress();
            $coupon           = $cart->getCoupon();
            $shipOv           = (array) session('shipping.override', []);

            // Costo de envío: si hay override del plugin, lo usamos; si no, el del carrito.
            $baseSubtotal         = (float) $cart->getSubtotal();
            $baseDiscount         = (float) $cart->getDiscountTotal();
            $baseShippingDiscount = $digitalOnly ? 0.0 : (float) $cart->getShippingDiscount();
            $shippingCost         = $digitalOnly
                                    ? 0.0
                                    : ((($shipOv['source'] ?? null) === 'enviacom' && isset($shipOv['amount']))
                                        ? (float) $shipOv['amount']
                                        : (float) $cart->getShippingCost());
            $computedTotal        = round($baseSubtotal - $baseDiscount + $shippingCost - $baseShippingDiscount, 2);
            $isFreeOrder          = $computedTotal <= 0.0;

            $order = null;
            if ($oid = session('checkout.order_id')) {
                $order = Order::where('id', $oid)->where('status', 'pending')->lockForUpdate()->first();
            }

            if ($order) {
                $order->update([
                    'customer_id'        => $customer?->id,
                    'name'               => $buyerData->name ?? null,
                    'email'              => $buyerData->email ?? null,
                    'phone'              => $buyerData->phone ?? null,
                    'shipment_method_id' => $shipmentMethodId,
                    'payment_method_id'  => $paymentMethodId,
                    'subtotal'           => $baseSubtotal,
                    'discount'           => $baseDiscount,
                    'shipping_cost'      => $shippingCost,
                    'shipping_discount'  => $baseShippingDiscount,
                    'coupon_id'          => $coupon['id'] ?? null,
                    'total'              => $computedTotal,
                    'shipping_address'   => $shippingAddr,
                    'billing_data_json'  => $billingData,
                ]);

                $order->items()->delete();
            } else {
                $order = Order::create([
                    'customer_id'        => $customer?->id,
                    'name'               => $buyerData->name ?? null,
                    'email'              => $buyerData->email ?? null,
                    'phone'              => $buyerData->phone ?? null,
                    'shipment_method_id' => $shipmentMethodId,
                    'payment_method_id'  => $paymentMethodId,
                    'status'             => 'pending',
                    'subtotal'           => $baseSubtotal,
                    'discount'           => $baseDiscount,
                    'shipping_cost'      => $shippingCost,
                    'shipping_discount'  => $baseShippingDiscount,
                    'coupon_id'          => $coupon['id'] ?? null,
                    'total'              => $computedTotal,
                    'shipping_address'   => $shippingAddr,
                    'billing_data_json'  => $billingData,
                ]);

                session(['checkout.order_id' => $order->id, 'checkout.amount' => $order->total]);
            }

            foreach ($cart->getCartItems() as $item) {
                $attributeValueId = null;

                if (!empty($item->attribute_values_json)) {
                    $values = json_decode($item->attribute_values_json, true);
                    if (!empty($values) && isset($values[0]['value_id'])) {
                        $attributeValueId = $values[0]['value_id'];
                    }
                } elseif (!empty($item->attribute_value_id)) {
                    $attributeValueId = $item->attribute_value_id;
                }

                // Control de stock
                if ($attributeValueId) {
                    $stock = DB::table('attribute_product')
                        ->where('product_id', $item->product_id)
                        ->where('attribute_value_id', $attributeValueId)
                        ->value('stock');
                } else {
                    $stock = \App\Models\Product::where('id', $item->product_id)->value('stock');
                }
                if ($stock === null || $stock < $item->quantity) {
                    DB::rollBack();
                    return redirect()->route('cart.index')
                        ->with('error', "El producto {$item->name} no tiene stock suficiente.");
                }

                $order->items()->create([
                    'product_id'         => $item->product_id,
                    'attribute_value_id' => $attributeValueId,
                    'name'               => $item->name,
                    'price'              => $item->price,
                    'quantity'           => $item->quantity,
                    'total'              => $item->price * $item->quantity,
                ]);

                if ($attributeValueId) {
                    DB::table('attribute_product')
                        ->where('product_id', $item->product_id)
                        ->where('attribute_value_id', $attributeValueId)
                        ->decrement('stock', $item->quantity);
                } else {
                    \App\Models\Product::where('id', $item->product_id)
                        ->decrement('stock', $item->quantity);
                }
            }

            // Datos extra del pago (incluyendo comprobante si existe)
            $paymentData = [];
            if ($receiptPath) {
                $paymentData['uploaded_receipt_path'] = $receiptPath;
            }
            if ($isFreeOrder) {
                $paymentData['free_checkout'] = true;
                $paymentData['reason'] = 'coupon_100';
            }

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'payment_method_id' => $paymentMethodId],
                [
                    'method'       => $isFreeOrder ? 'Sin cargo (cupón 100%)' : ($paymentMethod?->name ?? 'desconocido'),
                    'amount'       => $order->total,
                    'status'       => $isFreeOrder ? 'completed' : 'pending',
                    'payment_data' => $paymentData,
                ]
            );
            if ($isFreeOrder) {
                $order->update(['status' => 'paid']);
            }

            // Core: sólo creamos envío para pedidos con logística física.
            if (!$digitalOnly && $shipmentMethodId) {
                $this->ensureShipment($order, $shippingAddr, $shipmentMethodId, $shipOv);
            }

            // Limpiar sesión y carrito
            $cart->clear();
            session()->forget([
                'checkout.shipment_method_id',
                'shipping.override',
                'shipping.package_plan',
                'payment_method_id',
                'guest_checkout',
                'discount_coupon',
                'checkout.amount',
                'checkout.cart_sig',
                // mantenemos checkout.order_id para thankYou()
            ]);
            session(['last_order_id' => $order->id]);

            // Notificar a plugins luego de confirmar la transacción
            DB::afterCommit(function() use ($order, $isFreeOrder) {
                Event::dispatch('checkout.order.finalized', $order);

                if (!$isFreeOrder) {
                    return;
                }

                try {
                    /** @var OrderDownloadService $downloadService */
                    $downloadService = app(OrderDownloadService::class);
                    /** @var EmailTemplateSender $sender */
                    $sender = app(EmailTemplateSender::class);

                    $sender->send(
                        'order_confirmed',
                        $order,
                        [
                            '%payment_method%' => 'Sin cargo (cupón 100%)',
                            '%payment_amount%' => number_format((float) ($order->total ?? 0), 2, ',', '.'),
                            '%order_id%' => (string) $order->id,
                            '%acceso_plataforma%' => $downloadService->buildAccessPlatformHtml($order),
                            '%post_purchase_block%' => $downloadService->buildPostPurchaseHtml($order),
                        ],
                        $order->email ?: optional($order->customer)->email,
                        $downloadService->attachmentsForEmail($order)
                    );
                } catch (\Throwable $e) {
                    \Log::error('[CHECKOUT] error enviando order_confirmed en checkout sin cargo', [
                        'order_id' => $order->id ?? null,
                        'err' => $e->getMessage(),
                    ]);
                }
            });

            DB::commit();

            return redirect()->route('front.checkout.complete');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ERROR AL FINALIZAR ORDEN: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('front.checkout.payment')->with('error', 'Ocurrió un error al procesar el pedido.');
        }
    }

    public function thankYou(OrderDownloadService $downloadService)
    {
        $lastId = session('last_order_id');

        if ($lastId) {
            $order = Order::with([
                    'items.product',
                    'items.attributeValue',
                    'shipment.method',
                    'payments.method',
                    'customer',
                ])->find($lastId);
            session()->forget('last_order_id');
        } elseif (auth('customer')->check()) {
            $order = Order::with([
                    'items.product',
                    'items.attributeValue',
                    'shipment.method',
                    'payments.method',
                    'customer',
                ])
                ->where('customer_id', auth('customer')->id())
                ->latest()
                ->first();
        } else {
            $order = null;
        }

        if (!$order) {
            return redirect()->route('home')->with('error', 'No hay información del pedido para mostrar.');
        }

        // Fallback ultra defensivo: si por alguna razón no hay shipment, lo creamos.
        if (!$order->shipment && $order->shipment_method_id) {
            $addr = $order->shipping_address ?: $this->resolveShippingAddress();
            $shipOv = (array) session('shipping.override', []);
            $this->ensureShipment($order, is_array($addr) ? $addr : (array) $addr, $order->shipment_method_id, $shipOv);
            $order->load('shipment.method');
        }

        $downloads = $downloadService->downloadableItems($order);
        $orderPaid = $downloadService->isPaid($order);

        return view('front.checkout.complete', compact('order', 'downloads', 'orderPaid'));
    }
}
