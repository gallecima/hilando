<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBillingData;
use App\Models\Cart;
use App\Support\CatalogAccess;

use Illuminate\Support\Str;
use Plugins\SMTP\Services\PluginMailer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;

class CustomerAuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('front.auth.customer-register');
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'document' => 'nullable|string|max:20',

            // Domicilio
            'address_line' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',

            // Facturación
            'business_name' => 'nullable|string',
            'document_number' => 'nullable|string',
            'tax_status' => 'nullable|string|in:Responsable Inscripto,Monotributista,Consumidor Final,Exento',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'document' => $request->document,
            'is_active' => true,
        ]);

        // Dirección principal
        if ($request->filled('address_line')) {
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'title' => 'Domicilio principal',
                'address_line' => $request->address_line,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'is_default' => true,
            ]);
        }

        // Datos fiscales
        if ($request->filled('business_name')) {
            CustomerBillingData::create([
                'customer_id' => $customer->id,
                'business_name' => $request->business_name,
                'document_number' => $request->document_number,
                'tax_status' => $request->tax_status,
                'address_line' => $request->address_line,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'is_default' => true,
            ]);
        }

        Auth::guard('customer')->login($customer);

        return redirect()->route('front.checkout.index')->with('success', 'Registro exitoso.');
    }

    public function showForgotPasswordForm()
    {
        return view('front.auth.customer-forgot-password');
    }    

    public function showLoginForm(Request $request)
    {
        $accessMode = $request->query('mode') === 'wholesale' ? 'wholesale' : 'retail';
        $redirectTo = $this->sanitizeFrontRedirect(
            $request->query('redirect_to'),
            route('front.mi-cuenta.pedidos')
        );

        return view('front.auth.customer-login', compact('accessMode', 'redirectTo'));
        // return redirect()->route('front.checkout.index');
    }

    public function switchCatalogAccess(Request $request, string $mode)
    {
        $redirectTo = $this->sanitizeFrontRedirect(
            $request->query('redirect_to', url()->previous() ?: route('home')),
            route('home')
        );
        $customer = auth('customer')->user();

        if ($mode === CatalogAccess::WHOLESALE && !CatalogAccess::canUseWholesale($customer)) {
            return redirect()->route('customer.login', [
                'mode' => 'wholesale',
                'redirect_to' => $redirectTo,
            ]);
        }

        CatalogAccess::setMode($mode, $customer);

        return redirect()->to($redirectTo);
    }

    public function checkoutLoginForm()
    {
        return redirect()->route('front.checkout.index');
    }

    private function normalizeDocumentDigits(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $digits = (string) preg_replace('/\D+/', '', $value);
        $digits = trim($digits);

        return $digits !== '' ? $digits : null;
    }

    private function sanitizeFrontRedirect(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        if (Str::startsWith(Str::lower($value), ['javascript:', 'data:'])) {
            return $fallback;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            $appHost = (string) parse_url(url('/'), PHP_URL_HOST);
            $targetHost = (string) parse_url($value, PHP_URL_HOST);

            if ($appHost === '' || $targetHost === '' || !hash_equals($appHost, $targetHost)) {
                return $fallback;
            }

            $path = (string) (parse_url($value, PHP_URL_PATH) ?? '/');
            $query = (string) (parse_url($value, PHP_URL_QUERY) ?? '');
            $fragment = (string) (parse_url($value, PHP_URL_FRAGMENT) ?? '');
            $value = $path . ($query !== '' ? '?' . $query : '') . ($fragment !== '' ? '#' . $fragment : '');
        }

        if (!Str::startsWith($value, '/')) {
            return $fallback;
        }

        $path = '/' . ltrim((string) (parse_url($value, PHP_URL_PATH) ?? '/'), '/');
        $blockedPrefixes = ['/admin', '/dashboard'];
        $blockedPaths = ['/login', '/register'];

        if (in_array($path, $blockedPaths, true)) {
            return $fallback;
        }

        foreach ($blockedPrefixes as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return $fallback;
            }
        }

        return $value;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'       => ['required', 'email'],
            'dni'         => ['nullable', 'string', 'max:20'],
            'password'    => ['nullable', 'string'],
            'redirect_to' => ['nullable', 'string'],
            'access_mode' => ['nullable', 'in:retail,wholesale'],
        ]);
        $requestedAccessMode = ($credentials['access_mode'] ?? 'retail') === 'wholesale' ? 'wholesale' : 'retail';

        $dniRaw = (string) ($request->input('dni') ?? $request->input('password') ?? '');
        $dniDigits = $this->normalizeDocumentDigits($dniRaw);
        if (!$dniDigits) {
            return back()->withErrors([
                'dni' => 'Ingresá tu DNI para iniciar sesión.',
            ])->onlyInput(['email', 'dni']);
        }

        $customer = Customer::where('email', $credentials['email'])->first();
        if (!$customer || !$customer->is_active) {
            return back()->withErrors([
                'email' => 'Las credenciales ingresadas no son válidas.',
            ])->onlyInput(['email', 'dni']);
        }

        // Guardamos el session_id del invitado antes de regenerar
        $guestSessionId = Session::getId();

        $customerDocument = $this->normalizeDocumentDigits((string) ($customer->document ?? ''));
        $authenticated = false;

        if ($customerDocument && hash_equals($customerDocument, $dniDigits)) {
            if (!Hash::check($dniDigits, (string) $customer->password)) {
                $customer->update(['password' => Hash::make($dniDigits)]);
            }

            Auth::guard('customer')->login($customer, $request->boolean('remember'));
            $authenticated = true;
        } else {
            $legacyPassword = (string) ($request->input('password') ?: $dniRaw);
            $authenticated = Auth::guard('customer')->attempt(
                ['email' => $credentials['email'], 'password' => $legacyPassword],
                $request->boolean('remember')
            );

            if ($authenticated) {
                $customer = Auth::guard('customer')->user();
            }
        }

        if ($authenticated) {
            if ($requestedAccessMode === 'wholesale' && !$customer->isWholesaler()) {
                Auth::guard('customer')->logout();

                return back()->withErrors([
                    'email' => 'Esta cuenta no tiene acceso mayorista habilitado.',
                ])->onlyInput(['email', 'dni']);
            }

            $guestCart = Cart::where('session_id', $guestSessionId)->first();

            if ($guestCart) {
                $existingCart = Cart::where('customer_id', $customer->id)->first();

                if ($existingCart) {
                    $existingCart->delete();
                }

                $guestCart->update([
                    'customer_id' => $customer->id,
                    'session_id' => null,
                ]);
            }

            $request->session()->regenerate();
            CatalogAccess::setMode(
                $requestedAccessMode === 'wholesale' ? CatalogAccess::WHOLESALE : CatalogAccess::RETAIL,
                $customer
            );

            $intended = $request->session()->pull('url.intended');
            $redirect = $this->sanitizeFrontRedirect(
                $request->input('redirect_to') ?: $intended,
                route('front.mi-cuenta.pedidos')
            );

            return redirect()->to($redirect);
        }

        return back()->withErrors([
            'email' => 'Las credenciales ingresadas no son válidas.',
        ])->onlyInput(['email', 'dni']);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }    


    public function sendResetLinkEmail(Request $request)
    {
        // 1) Validar email
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2) Buscar cliente por email
        $customer = Customer::where('email', $request->email)->first();

        // Seguridad: no exponemos si el mail existe o no
        if (! $customer) {
            return back()->with('status', __('passwords.sent'));
        }

        // 3) Generar token con el broker "customers"
        $token = Password::broker('customers')->createToken($customer);

        $resetUrl = route('customer.password.reset', [
            'token' => $token,
            'email' => $customer->email,   // 👈 texto plano, sin urlencode
        ]);

        // 5) Renderizar el HTML del mail usando la vista
        $html = view('front.emails.customer-reset-password', [
            'customer' => $customer,
            'resetUrl' => $resetUrl,
        ])->render();

        $subject = 'Restablecé tu contraseña';

        try {
            app(PluginMailer::class)->send($customer->email, $subject, $html, []);
        } catch (\Throwable $e) {
            \Log::error('[CustomerResetPassword] error: '.$e->getMessage());
            // devolvemos un error genérico al usuario
            return back()->withErrors([
                'email' => 'No se pudo enviar el email de recuperación. Intentá de nuevo en unos minutos.',
            ]);
        }

        return back()->with('status', __('passwords.sent'));
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('front.auth.customer-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer, $password) {
                /** @var \App\Models\Customer $customer */
                $customer->password = Hash::make($password);
                $customer->setRememberToken(Str::random(60));
                $customer->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Si querés, podés loguearlo automáticamente:
            // Auth::guard('customer')->login($customer);

            return redirect()
                ->route('customer.login')
                ->with('success', 'Tu contraseña fue restablecida correctamente. Ahora podés iniciar sesión.');
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }    
}
