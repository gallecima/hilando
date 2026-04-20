<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\SiteInfo;
use App\Models\Slider;
use App\Support\CatalogAccess;
use App\Services\CartService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('layouts.*', function ($view) {
            $siteInfo = null;
            $siteTitle = config('app.name', 'Tienda');
            $siteThemeVars = SiteInfo::THEME_VAR_DEFAULTS;

            if (Schema::hasTable('site_infos')) {
                $siteInfo = SiteInfo::query()->first();
                $candidate = trim((string) ($siteInfo?->site_title ?? ''));
                if ($candidate !== '') {
                    $siteTitle = $candidate;
                }

                if (Schema::hasColumn('site_infos', 'theme_vars') && $siteInfo) {
                    $siteThemeVars = $siteInfo->resolvedThemeVars();
                }
            }

            $view->with('siteInfo', $siteInfo)
                ->with('siteTitle', $siteTitle)
                ->with('siteThemeVars', $siteThemeVars);
        });

        View::composer(['layouts.front', 'layouts.simple'], function ($view) {
            $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->get();
            $customer = auth('customer')->user();
            $hasWholesaleAccess = CatalogAccess::canUseWholesale($customer);
            $catalogAccessMode = CatalogAccess::mode($customer);

            /** @var CartService $cart */
            $cart = app(CartService::class);
            $cartItemCount = $cart->getCartItems()->sum('quantity');

            $view->with('menuCategories', $categories)
                ->with('cartItemCount', $cartItemCount)
                ->with('currentCustomer', $customer)
                ->with('hasWholesaleAccess', $hasWholesaleAccess)
                ->with('isWholesaleCustomer', $catalogAccessMode === CatalogAccess::WHOLESALE)
                ->with('catalogAccessMode', $catalogAccessMode);
        });

        View::composer([
            'front.cart.index',
            'front.auth.customer-login',
            'front.checkout.index',
            'front.checkout.personal-data',
            'front.checkout.shipment',
            'front.checkout.payment',
            'front.checkout.payment-process',
            'front.checkout.complete',
            'front.mi-cuenta.index',
            'front.mi-cuenta.pedidos',
            'front.mi-cuenta.password',
            'front.mi-cuenta.pedido-detalle',
        ], function ($view) {
            $heroSlides = collect();
            $heroBackgroundImage = null;

            if (Schema::hasTable('sliders') && Schema::hasTable('slider_images')) {
                $slider = $this->resolveCheckoutSlider();

                if ($slider && $slider->images->isNotEmpty()) {
                    $heroSlides = $slider->images
                        ->sortBy('orden')
                        ->map(function ($image) {
                            $path = ltrim((string) $image->imagen, '/');

                            return [
                                'src' => asset('storage/' . $path),
                                'alt' => config('app.name', 'Tienda'),
                            ];
                        })
                        ->filter(fn ($slide) => trim((string) ($slide['src'] ?? '')) !== '')
                        ->values();

                    $heroBackgroundImage = $heroSlides->first()['src'] ?? null;
                }
            }

            $view->with('checkoutHeroSlides', $heroSlides)
                ->with('checkoutHeroBackgroundImage', $heroBackgroundImage);
        });
    }

    private function resolveCheckoutSlider(): ?Slider
    {
        $checkoutSlider = $this->resolveSliderBySlug('checkout');
        if ($checkoutSlider) {
            return $checkoutSlider;
        }

        foreach (['principal', 'home'] as $slug) {
            $slider = $this->resolveSliderBySlug($slug);
            if ($slider) {
                return $slider;
            }
        }

        return Slider::query()
            ->where('activo', true)
            ->with('images')
            ->get()
            ->first(fn (Slider $slider) => $slider->images->isNotEmpty());
    }

    private function resolveSliderBySlug(string $slug): ?Slider
    {
        $slider = Slider::query()
            ->where('slug', $slug)
            ->where('activo', true)
            ->with('images')
            ->first();

        if (!$slider || $slider->images->isEmpty()) {
            return null;
        }

        return $slider;
    }
}
