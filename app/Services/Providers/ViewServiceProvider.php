<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Registrar view composers cuando el binding 'view' ya está listo
        $this->app->afterResolving('view', function ($factory, $app) {
            $factory->composer('layouts.front', function ($view) {
                $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->get();

                /** @var CartService $cart */
                $cart = app(CartService::class);
                $cartItemCount = $cart->getCartItems()->sum('quantity');

                $view->with('menuCategories', $categories)
                     ->with('cartItemCount', $cartItemCount);
            });
        });
    }
}