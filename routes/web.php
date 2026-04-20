<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuGroupController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\SiteInfoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SliderImageController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DiscountCouponController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShipmentMethodController;
use App\Http\Controllers\ShippingBoxController;
use App\Http\Controllers\ShippingPointController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PublicOrderController;
use \App\Http\Controllers\CustomerAccountController;
use \App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PluginAdminController;
use App\Http\Controllers\PluginAssetController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FavoriteController;
use App\Services\CartService;
use App\Http\Controllers\Auth\CustomerAuthController;

// Página principal y navegación pública
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/producto/{product}', [FrontendController::class, 'product'])->name('product.show');
Route::get('/productos', [FrontendController::class, 'allProducts'])->name('products.all');
Route::get('/recursos-gratuitos', [FrontendController::class, 'freeResources'])->name('products.free');
Route::get('/sobre-hilando', [FrontendController::class, 'aboutHilando'])->name('about.show');
Route::get('/contacto', [FrontendController::class, 'contact'])->name('contact.show');
Route::post('/contacto', [FrontendController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');
Route::get('/categoria/{slug}', [FrontendController::class, 'category'])
    ->where('slug', '.*')
    ->name('category.show');
Route::get('/buscar', [FrontendController::class, 'search'])->name('product.search');
Route::get('blog/{slug}', [BlogPostController::class, 'showPost'])->name('post.show');
Route::get('blog/category/{slug}', [BlogPostController::class, 'category'])->name('post.category');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('front.newsletter.subscribe');

// Registro y login de clientes
Route::get('/cliente/registro', [CustomerAuthController::class, 'showRegistrationForm'])->name('customer.register');
Route::post('/cliente/registro', [CustomerAuthController::class, 'register'])->name('customer.register.submit');
Route::get('/cliente/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/cliente/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
Route::get('/catalogo/acceso/{mode}', [CustomerAuthController::class, 'switchCatalogAccess'])->name('catalog.access');
// Route::post('/cliente/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
Route::get('/clientes/recuperar-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('customer.password.request');
Route::post('/clientes/recuperar-password',[CustomerAuthController::class, 'sendResetLinkEmail'])->name('customer.password.email');

// Mostrar formulario de nueva contraseña
Route::get('/clientes/reset-password/{token}', [CustomerAuthController::class, 'showResetForm'])
    ->name('customer.password.reset');

// Procesar nueva contraseña
Route::post('/clientes/reset-password', [CustomerAuthController::class, 'resetPassword'])
    ->name('customer.password.update');

Route::get('/pedido/{token}', [PublicOrderController::class, 'show'])->name('orders.track');
Route::get('/pedido/{token}/descarga/{product}/{file?}', [PublicOrderController::class, 'download'])
    ->where('file', '[A-Za-z0-9]+')
    ->name('orders.download');

// Route::post('/cliente/logout', function (Request $request) {
     
//     Auth::guard('customer')->logout();
//     $request->session()->invalidate();
//     $request->session()->regenerateToken();

//     return redirect('/'); // o route('home')
// })->name('front.logout');

Route::post('/cliente/logout', [CustomerAuthController::class, 'logout'])->name('front.logout');

// Carrito
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', function () {
    session()->forget('discount_coupon');
    return back()->with('success', 'Cupón eliminado correctamente.');
})->name('cart.remove-coupon');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/partial', [CartController::class, 'partial'])->name('cart.partial');
Route::get('/cart/count', function (CartService $cart) {
    return response()->json(['count' => $cart->getCount()]); 
})->name('cart.count');
Route::get('/favorites/partial', [FavoriteController::class, 'partial'])->name('favorites.partial');
Route::get('/favorites/count', [FavoriteController::class, 'count'])->name('favorites.count');
Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

Route::prefix('checkout')->name('front.checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');

    Route::post('/guest', [CheckoutController::class, 'storeGuest'])->name('guest');
    Route::get('/login', [CustomerAuthController::class, 'checkoutLoginForm'])->name('login');

    Route::get('/personal-data', [CheckoutController::class, 'showPersonalData'])->name('personal_data');
    Route::post('/personal-data', [CheckoutController::class, 'storePersonalData'])->name('personal_data.store');

    Route::get('/shipment', [CheckoutController::class, 'showShipment'])->name('shipment');
    Route::post('/shipment', [CheckoutController::class, 'storeShipment'])->name('shipment.store');

    Route::get('/payment', [CheckoutController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [CheckoutController::class, 'storePayment'])->name('payment.store');
    Route::post('/payment/choose', [CheckoutController::class, 'handlePaymentMethod'])->name('payment.choose');
    Route::get('/payment/process', [CheckoutController::class, 'processPayment'])->name('payment.process');

    Route::post('/finalize', [CheckoutController::class, 'finalizeOrder'])->name('finalize');
    
    Route::get('/complete', [CheckoutController::class, 'thankYou'])->name('complete');
});

Route::prefix('mi-cuenta')->middleware(['auth:customer'])->name('front.mi-cuenta.')->group(function () {
    
    Route::get('/', [CustomerAccountController::class, 'index'])->name('index');
    Route::put('/', [CustomerAccountController::class, 'update'])->name('actualizar-datos');
    Route::get('pedidos', [CustomerOrderController::class, 'index'])->name('pedidos');
    Route::get('pedidos/{order}', [CustomerOrderController::class, 'show'])->name('pedido');
    Route::get('pedidos/{order}/descargas/{product}/{file?}', [CustomerOrderController::class, 'download'])
        ->where('file', '[A-Za-z0-9]+')
        ->name('pedido.download');
    
    // Actualizar datos personales
    Route::post('/actualizar', [CustomerAccountController::class, 'update'])->name('update');

    // Formulario de cambio de contraseña
    Route::get('/password', [CustomerAccountController::class, 'editPassword'])->name('password');

    // Actualizar contraseña
    Route::post('/password', [CustomerAccountController::class, 'updatePassword'])->name('password.update');
});

Route::get('/plugin-assets/{slug}/{path}', [PluginAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('plugin.assets');


// Dashboard (requiere login y verificación)
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth','verified'])->name('dashboard');

// Perfil de usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/api/provinces/{country}', [LocationController::class, 'getProvinces'])->name('api.provinces');
Route::get('/api/localities/{province}', [LocationController::class, 'getLocalities'])->name('api.localities');
Route::get('/api/location/resolve', [LocationController::class, 'resolve'])->name('api.location.resolve');
Route::get('/api/checkout/shipment-methods', [CheckoutController::class, 'apiShipmentMethods'])->name('api.checkout.shipment_methods');
Route::post('/api/tribuno/subscription/sync', [CheckoutController::class, 'apiTribunoSubscriptionSync'])->name('api.tribuno.subscription.sync');

// Panel de administración
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('plugins')->name('plugins.')->group(function () {
        Route::get('/',                 [PluginAdminController::class,'index'])->name('index');
        Route::post('/install/{slug}',  [PluginAdminController::class,'install'])->name('install');
        Route::post('/toggle/{slug}',   [PluginAdminController::class,'toggle'])->name('toggle');
        // genéricas (fallback si el plugin no trae settings propios):
        Route::get('/settings/{slug}',  [PluginAdminController::class,'edit'])->name('settings');
        Route::post('/settings/{slug}', [PluginAdminController::class,'update'])->name('settings.update');
    });

    // Menú y usuarios
    Route::resource('menu-groups', MenuGroupController::class)->parameters(['menu-groups' => 'menuGroup']);
    Route::resource('menus', MenuController::class);
    Route::resource('perfiles', PerfilController::class)->parameters(['perfiles' => 'perfil'])->except(['show']);
    Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario'])->except(['show']);
    Route::post('usuarios/{usuario}/send-reset', [UsuarioController::class, 'sendResetEmail'])->name('usuarios.send-reset');

    // Productos, categorías y atributos
    Route::patch('products/order', [ProductController::class, 'updateOrder'])->name('products.order');
    Route::resource('products', ProductController::class)->parameters(['products' => 'product']);
    Route::post('products/get-attributes', [ProductController::class, 'getAttributesByCategories'])->name('products.getAttributesByCategories');
    Route::post('products/upload-temp-image', [ProductController::class, 'uploadTempImage'])->name('products.uploadTempImage');

    Route::resource('categories', App\Http\Controllers\CategoryController::class)->parameters(['categories' => 'category']);
    Route::post('categories/{category}/crop', [App\Http\Controllers\CategoryController::class, 'crop'])->name('categories.crop');
    Route::resource('product-images', App\Http\Controllers\ProductImageController::class)->parameters(['product-images' => 'productImage']);

    // Clientes
    Route::get('customers/export', [App\Http\Controllers\CustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', App\Http\Controllers\CustomerController::class)->parameters(['customers' => 'customer']);
    Route::resource('customer-addresses', App\Http\Controllers\CustomerAddressController::class)->parameters(['customer-addresses' => 'customerAddress']);
    Route::resource('customer-billing-data', App\Http\Controllers\CustomerBillingDataController::class)->parameters(['customer-billing-data' => 'customerBillingData']);    

    // Pedidos y ventas
    Route::resource('orders', App\Http\Controllers\OrderController::class)->parameters(['orders' => 'order']);
    Route::resource('order-items', App\Http\Controllers\OrderItemController::class)->parameters(['order-items' => 'orderItem']);
    Route::get('orders/{order}/label', [\App\Http\Controllers\OrderController::class, 'label'])->name('orders.label');
    Route::post('orders/{order}/invoices', [\App\Http\Controllers\OrderInvoiceController::class, 'store'])->name('orders.invoices.store');
    Route::delete('orders/{order}/invoices/{invoice}', [\App\Http\Controllers\OrderInvoiceController::class, 'destroy'])->name('orders.invoices.destroy');
    Route::resource('payments', App\Http\Controllers\PaymentController::class)->parameters(['payments' => 'payment']);
    Route::resource('shipments', App\Http\Controllers\ShipmentController::class)->parameters(['shipments' => 'shipment']);
    // routes/web.php (dentro del grupo admin)
    Route::resource('shipping-points', ShippingPointController::class)
        ->parameters(['shipping-points' => 'shippingPoint'])
        ->names('shipping-points')
        ->except(['show']);
    Route::resource('shipping-boxes', ShippingBoxController::class)
        ->parameters(['shipping-boxes' => 'shippingBox'])
        ->names('shipping-boxes')
        ->except(['show']);

    // Carrito y cupones
    Route::resource('discount-coupons', DiscountCouponController::class)->parameters(['discount-coupons' => 'discountCoupon']);
    Route::resource('carts', App\Http\Controllers\CartController::class)->parameters(['carts' => 'cart']);
    Route::resource('cart-items', App\Http\Controllers\CartItemController::class)->parameters(['cart-items' => 'cartItem']);

    // Atributos
    Route::resource('attributes', App\Http\Controllers\AttributeController::class)->parameters(['attributes' => 'attribute']);
    Route::resource('attribute-values', App\Http\Controllers\AttributeValueController::class)->parameters(['attribute-values' => 'attributeValue']);

    // Facturación electrónica
    Route::resource('facturacion-electronica', App\Http\Controllers\FacturacionElectronicaController::class)->parameters(['facturacion-electronica' => 'facturacionElectronica']);

    Route::get('log-actividades', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('logs.index');


    Route::resource('payment-methods', PaymentMethodController::class);

    // Métodos de envío (Shipment Methods)
    Route::resource('shipmentmethod', ShipmentMethodController::class)->names('shipmentmethod');

    // Sliders
    Route::resource('sliders', SliderController::class)->parameters(['sliders' => 'slider']);
    Route::get('sliders/{slider}/imagenes', [SliderController::class, 'images'])->name('sliders.images');
    Route::post('sliders/{slider}/imagenes', [SliderImageController::class, 'store'])->name('sliders.images.store');
    Route::match(['put', 'patch'], 'sliders/{slider}/imagenes/{image}', [SliderImageController::class, 'update'])->name('sliders.images.update');
    Route::delete('sliders/{slider}/imagenes/{image}', [SliderImageController::class, 'destroy'])->name('sliders.images.destroy');
    Route::post('sliders/{slider}/images/sort', [SliderImageController::class, 'sort'])->name('sliders.images.sort');

    // Blog
    Route::prefix('blog')->group(function () {
        Route::resource('categories', BlogCategoryController::class)->names('blog.categories');
        Route::resource('posts', BlogPostController::class)->names('blog.posts');
    });

    // Admin > Emails (gestión de plantillas)
    Route::prefix('emails')->name('emails.')->group(function () {
        Route::get('/',               [EmailTemplateController::class, 'index'])->name('index');
        Route::get('/{key}/edit',     [EmailTemplateController::class, 'edit'])->name('edit');
        Route::put('/{key}',          [EmailTemplateController::class, 'update'])->name('update');
        Route::post('/{key}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{key}/test',    [EmailTemplateController::class, 'testSend'])->name('test');
    });    


    Route::prefix('info')->name('info.')->group(function () {
        Route::get('/',            [SiteInfoController::class, 'index'])->name('index');
        Route::get('/create',      [SiteInfoController::class, 'create'])->name('create');
        Route::post('/',           [SiteInfoController::class, 'store'])->name('store');
        Route::get('/{info}/edit', [SiteInfoController::class, 'edit'])->name('edit');
        Route::put('/{info}',      [SiteInfoController::class, 'update'])->name('update');
    });    
  
});

// Rutas de auth (Laravel Breeze / Fortify / etc.)
require __DIR__.'/auth.php';
