<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Services\CartService;
use App\Services\PluginManager;
use App\Support\Hooks;
use App\Observers\OrderObserver;
use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\Customer;
use App\Observers\CustomerObserver;

use App\Models\User;
use App\Observers\UserObserver;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Hooks como singleton
        $this->app->singleton(Hooks::class, fn() => new Hooks());

        // PluginManager
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager($app->make(\Illuminate\Filesystem\Filesystem::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
            // URL::forceScheme('https');
            // descomentar para ngrok
        }

        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        Customer::observe(CustomerObserver::class);
        User::observe(UserObserver::class);

        // Inyecta categorías de menú y contador del carrito en el layout principal
        View::composer('front.*', function ($view) {
            $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->get();
            $view->with('menuCategories', $categories);
   
            // ¡Usar SIEMPRE el service para que respete sesión/cliente!
            $cartService = app(CartService::class);
            $view->with('cartCount', $cartService->getCount());
        });

        // Inyecta categorías de menú y contador del carrito en el layout principal
        View::composer('cart.*', function ($view) {
            $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->get();
            $view->with('menuCategories', $categories);
   
            // ¡Usar SIEMPRE el service para que respete sesión/cliente!
            $cartService = app(CartService::class);
            $view->with('cartCount', $cartService->getCount());
        });

        // Registrar providers de plugins en TODAS las requests
        $manager = app(PluginManager::class);
        foreach ($manager->catalog() as $info) {
            if (!empty($info['provider']) && class_exists($info['provider'])) {
                $this->app->register($info['provider']);
            }
        }

        // Directiva Blade @hook
        \Illuminate\Support\Facades\Blade::directive('hook', function ($expression) {
            return "<?php echo app(\\App\\Support\\Hooks::class)->render($expression); ?>";
        });
    }
}