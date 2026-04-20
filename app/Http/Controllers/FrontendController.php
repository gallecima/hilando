<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteInfo;
use App\Models\Slider;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Plugin as PluginModel;
use Plugins\SMTP\Services\PluginMailer;

class FrontendController extends Controller
{

    public function boot()
    {
        View::composer('layouts.front', function ($view) {
            $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->get();
            $view->with('menuCategories', $categories);
        });
    }
        
    public function index()
    {
        $heroProductsQuery = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['categories', 'attributeValues.attribute']);
        $this->applyProductOrder($heroProductsQuery, [['created_at', 'desc']]);
        $heroProducts = $heroProductsQuery->take(6)->get();

        if ($heroProducts->count() < 6) {
            $missing = 6 - $heroProducts->count();
            $fallbackHeroQuery = Product::where('is_active', true)
                ->whereNotIn('id', $heroProducts->pluck('id')->all())
                ->with(['categories', 'attributeValues.attribute']);
            $this->applyProductOrder($fallbackHeroQuery, [['created_at', 'desc']]);
            $heroProducts = $heroProducts->concat($fallbackHeroQuery->take($missing)->get());
        }

        $homeCategories = Category::query()
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $prioritizedCategories = $homeCategories
            ->filter(fn (Category $category) => filled($category->image))
            ->values();

        if ($prioritizedCategories->count() < 6) {
            $missing = 6 - $prioritizedCategories->count();
            $fallbackCategories = $homeCategories
                ->reject(fn (Category $category) => $prioritizedCategories->contains('id', $category->id))
                ->take($missing);

            $prioritizedCategories = $prioritizedCategories->concat($fallbackCategories)->values();
        }

        $homeCategories = $prioritizedCategories->take(6)->values();
        $sliderPrincipal = $this->getHomeSlider();
        $plugins = PluginModel::all();

        return view('front.index', compact('heroProducts', 'homeCategories', 'sliderPrincipal', 'plugins'));
    }

    public function aboutHilando()
    {
        $aboutDefinitions = [
            'quienes-somos' => [
                'slugs' => ['quienes-somos'],
                'prefixes' => ['quienes-somos'],
                'titles' => ['quienes somos'],
            ],
            'proposito' => [
                'slugs' => ['proposito', 'propostio'],
                'prefixes' => ['proposito', 'propostio'],
                'titles' => ['proposito'],
            ],
            'manifesto' => [
                'slugs' => ['manifesto'],
                'prefixes' => ['manifesto'],
                'titles' => ['manifesto'],
            ],
            'faqs' => [
                'slugs' => ['faqs'],
                'prefixes' => ['faqs', 'faq'],
                'titles' => ['faqs', 'preguntas frecuentes'],
            ],
        ];

        $aboutPostsRaw = BlogPost::query()
            ->where('activo', true)
            ->whereHas('categoria', fn ($query) => $query->where('slug', 'sobre-hilando'))
            ->orderByDesc('updated_at')
            ->get()
            ->values();

        $aboutPosts = collect();
        $aboutPostImages = collect();

        foreach ($aboutDefinitions as $key => $definition) {
            $candidates = $this->findAboutSectionCandidates($aboutPostsRaw, $definition);

            if ($candidates->isEmpty()) {
                continue;
            }

            $aboutPosts->put($key, $candidates->first());
            $aboutPostImages->put($key, $candidates->first(fn (BlogPost $post) => filled($post->imagen_destacada)));
        }

        $aboutSlider = $this->getHomeSlider();
        $aboutHeroSlides = collect();
        $aboutHeroBackgroundImage = null;

        if ($aboutSlider && $aboutSlider->images->isNotEmpty()) {
            $aboutHeroSlides = $aboutSlider->images
                ->sortBy('orden')
                ->map(function ($image) {
                    return [
                        'src' => asset('storage/' . ltrim((string) $image->imagen, '/')),
                        'alt' => config('app.name', 'Tienda'),
                    ];
                })
                ->filter(fn ($slide) => filled($slide['src'] ?? null))
                ->values();

            $aboutHeroBackgroundImage = $aboutHeroSlides->first()['src'] ?? null;
        }

        return view('front.about-hilando', compact('aboutPosts', 'aboutPostImages', 'aboutHeroSlides', 'aboutHeroBackgroundImage'));
    }

    public function contact()
    {
        $contactSlider = $this->getContactSlider();
        $contactHeroSlides = $this->buildSliderImageSlides($contactSlider);
        $contactHeroBackgroundImage = $contactHeroSlides->first()['src'] ?? null;

        return view('front.contact', compact('contactHeroSlides', 'contactHeroBackgroundImage'));
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $site = SiteInfo::query()->first();
        $recipientEmail = trim((string) ($site?->support_email ?? 'info@hilandoculturas.com'));

        if (!class_exists(PluginMailer::class)) {
            return back()
                ->withInput()
                ->with('error', 'No hay un servicio de envío configurado para recibir consultas en este momento.');
        }

        $messageBody = implode('<br>', [
            'Nueva solicitud de contacto desde el sitio.',
            '',
            'Email: ' . e((string) $data['email']),
            'Fecha: ' . e(now()->toDateTimeString()),
            'IP: ' . e((string) $request->ip()),
            '',
            'Mensaje:',
            nl2br(e((string) $data['message'])),
        ]);

        try {
            app(PluginMailer::class)->send(
                $recipientEmail,
                'Solicitud de contacto',
                $messageBody,
                [],
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'No se pudo enviar el mensaje. Intentá nuevamente más tarde.');
        }

        return back()->with('success', 'Recibimos tu mensaje. Te responderemos a la brevedad.');
    }

    public function category($slug, Request $request)
    {
        /**
         * MODO ESPECIAL: slug "todas"
         * → Mostrar TODOS los productos activos, sin agrupar por categoría.
         */
        if ($slug === 'todas') {
            // "categoría virtual" para reutilizar la vista
            $category = (object)[
                'id'        => null,
                'name'      => 'Todos los productos',
                'slug'      => 'todas',
                'image'     => null,
                'parent_id' => null,
                'parent'    => null,
            ];

            // Podés seguir mostrando categorías raíz en el sidebar si querés
            $subcategories = Category::query()
                ->whereNull('parent_id')
                ->when(schema_has_column('categories','is_active'), fn($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get();

            // TODOS los productos activos (sin filtrar por categoría)
            $productsQuery = Product::query()
                ->when(schema_has_column('products', 'is_active'), fn($q) => $q->where('is_active', true));
            $this->excludeFreeProducts($productsQuery);

            // Orden básico por nombre (si después querés, acá se puede enganchar ?sort=)
            $this->applyProductOrder($productsQuery, [['name', 'asc']]);
            $products = $productsQuery
                ->paginate(12)
                ->appends($request->query());

            // Para este modo no usamos atributos ni hermanas
            $attributes = collect();
            $siblings   = collect();

            return view('front.category', compact(
                'category',
                'subcategories',
                'attributes',
                'siblings',
                'products'
            ));
        }

        // ========= LÓGICA NORMAL DE CATEGORÍA (igual que ya la tenías) =========
            
        // Resolver categoría
        $parts = explode('/', $slug);
        $finalSlug = end($parts);
        $category = Category::where('slug', $finalSlug)->firstOrFail();

        // Subcategorías inmediatas
        $subcategories = $category->children()
            ->when(schema_has_column('categories','is_active'), fn($q)=>$q->where('is_active', true))
            ->orderBy('name')->get();

        // Atributos de la categoría (base)
        $attributes = $category->attributes()
            ->when(schema_has_column('attributes','is_active'), fn($q)=>$q->where('is_active', true))
            ->with(['values' => fn($q)=>$q->when(schema_has_column('attribute_values','is_active'), fn($qq)=>$qq->where('is_active', true))
                                        ->orderBy('value')])
            ->orderBy('name')
            ->get();

        // --- FILTROS por atributos ---
        $rawAttrFilters = (array) $request->input('attrs', []);
        $normalizedFilters = collect($rawAttrFilters)->map(function($csv){
            return collect(explode(',', (string)$csv))
                    ->map(fn($s)=>trim($s))
                    ->filter()
                    ->values();
        })->filter();

        $attrBySlug = $attributes->keyBy('slug');
        $filtersByAttributeId = $normalizedFilters->mapWithKeys(function($valueSlugs, $attrSlug) use ($attrBySlug) {
            $attribute = $attrBySlug->get($attrSlug);
            if (!$attribute) return [];
            $valueIds = $attribute->values->whereIn('slug', $valueSlugs->all())->pluck('id')->values();
            return $valueIds->isNotEmpty() ? [$attribute->id => $valueIds] : [];
        });

        // --- CONSULTA de productos: PRIMERO solo la categoría actual ---
        $productsQuery = Product::query()
            ->whereHas('categories', fn($q)=>$q->where('categories.id', $category->id))
            ->when(schema_has_column('products','is_active'), fn($q)=>$q->where('is_active', true))
            ->with(['categories', 'attributeValues.attribute']);
        $this->excludeFreeProducts($productsQuery);

        foreach ($filtersByAttributeId as $attributeId => $valueIds) {
            $productsQuery->whereHas('attributeValues', function($q) use ($attributeId, $valueIds){
                $q->whereIn('attribute_values.id', $valueIds)
                ->where('attribute_values.attribute_id', $attributeId);
            });
        }

        $this->applyProductOrder($productsQuery, [['name', 'asc']]);
        $products = $productsQuery->paginate(12)->appends($request->query());

        // --- SI NO HAY PRODUCTOS DIRECTOS: INCLUIR DESCENDIENTES ---
        if (method_exists($products, 'total') && $products->total() === 0) {
            $descendantIds = $this->getDescendantCategoryIds($category);

            if (!empty($descendantIds)) {
                // Recalcular productos incluyendo hijas (y la actual, por si acaso)
                $ids = array_values(array_unique(array_merge([$category->id], $descendantIds)));

                $productsQuery = Product::query()
                    ->whereHas('categories', fn($q)=>$q->whereIn('categories.id', $ids))
                    ->when(schema_has_column('products','is_active'), fn($q)=>$q->where('is_active', true))
                    ->with(['categories', 'attributeValues.attribute']);
                $this->excludeFreeProducts($productsQuery);

                foreach ($filtersByAttributeId as $attributeId => $valueIds) {
                    $productsQuery->whereHas('attributeValues', function($q) use ($attributeId, $valueIds){
                        $q->whereIn('attribute_values.id', $valueIds)
                        ->where('attribute_values.attribute_id', $attributeId);
                    });
                }

                $this->applyProductOrder($productsQuery, [['name', 'asc']]);
                $products = $productsQuery->paginate(12)->appends($request->query());

                // Si la categoría no tenía atributos, ampliamos a la unión de categorías incluidas
                if ($attributes->isEmpty()) {
                    $attributes = Attribute::query()
                        ->whereHas('categories', fn($q)=>$q->whereIn('categories.id', $ids))
                        ->when(schema_has_column('attributes','is_active'), fn($q)=>$q->where('is_active', true))
                        ->with(['values' => fn($q)=>$q->when(schema_has_column('attribute_values','is_active'), fn($qq)=>$qq->where('is_active', true))
                                                    ->orderBy('value')])
                        ->orderBy('name')
                        ->get();
                }
            }
        }

        // Hermanas
        $siblings = Category::query()
            ->when(isset($category->parent_id), fn($q)=>$q->where('parent_id', $category->parent_id),
                                            fn($q)=>$q->whereNull('parent_id'))
            ->when(schema_has_column('categories','is_active'), fn($q)=>$q->where('is_active', true))
            ->where('id', '!=', $category->id)
            ->orderBy('name')->get();

        return view('front.category', compact(
            'category','products','subcategories','attributes','siblings'
        ));
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['attributeValues' => function ($q) {
                $q->withPivot(['price', 'stock']);
            }, 'categories', 'images', 'attributeValues.attribute'])
            ->firstOrFail();

        $otherProductsQuery = Product::where('is_active', true)
            ->where('id','<>', $product->id)
            ->with(['categories' => function($q) use ($product) {
                $q->whereIn('category_id', $product->categories->pluck('id'));
            }, 'attributeValues.attribute']);

        $this->applyProductOrder($otherProductsQuery, [['created_at', 'desc']]);
        $otherProducts = $otherProductsQuery->take(4)->get();

        return view('front.product', compact('product','otherProducts'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $category = null;
        $subcategories = collect();
        $siblings = collect();
        $attributes = collect();

        // Si viene ?category=slug, resolvemos la categoría y preparamos el sidebar
        if ($catSlug = $request->input('category')) {
            $category = Category::where('slug', $catSlug)->first();
            if ($category) {
                $subcategories = $category->children()
                    ->when(schema_has_column('categories','is_active'), fn($q)=>$q->where('is_active', true))
                    ->orderBy('name')->get();

                $attributes = $category->attributes()
                    ->when(schema_has_column('attributes','is_active'), fn($q)=>$q->where('is_active', true))
                    ->with(['values' => fn($q)=>$q->orderBy('value')])
                    ->orderBy('name')->get();

                $siblings = Category::query()
                    ->when(isset($category->parent_id),
                        fn($q)=>$q->where('parent_id', $category->parent_id),
                        fn($q)=>$q->whereNull('parent_id'))
                    ->when(schema_has_column('categories','is_active'), fn($q)=>$q->where('is_active', true))
                    ->where('id', '!=', $category->id)
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // Búsqueda global: cargamos atributos globales filtrables (si tenés flag is_filterable)
            $attributes = Attribute::query()
                ->when(schema_has_column('attributes','is_filterable'), fn($q)=>$q->where('is_filterable', true))
                ->with(['values' => fn($q)=>$q->orderBy('value')])
                ->orderBy('name')
                ->get();
        }

        // Construir query de productos por término
        $productsQuery = Product::query()
            ->with(['categories', 'attributeValues.attribute'])
            ->when($q !== '', function($query) use ($q){
                $query->where(function($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
                });
            });

        // Si hay categoría elegida, concentrar resultados allí
        if ($category) {
            $productsQuery->whereHas('categories', fn($c)=>$c->where('categories.id', $category->id));
        }

        // Aplicar filtros por atributos (?attrs[slug]=v1,v2)
        $rawAttrFilters = (array) $request->input('attrs', []);
        if (!empty($rawAttrFilters)) {
            $attrBySlug = $attributes->keyBy('slug');
            $filtersByAttributeId = collect($rawAttrFilters)->map(function($csv){ 
                    return collect(explode(',', (string)$csv))
                            ->map(fn($s)=>trim($s))
                            ->filter()
                            ->values();
                })
                ->filter()
                ->mapWithKeys(function($valueSlugs, $attrSlug) use ($attrBySlug) {
                    $attribute = $attrBySlug->get($attrSlug);
                    if (!$attribute) return [];
                    $valueIds = $attribute->values->whereIn('slug', $valueSlugs->all())->pluck('id');
                    return $valueIds->isNotEmpty() ? [$attribute->id => $valueIds] : [];
                });

            foreach ($filtersByAttributeId as $attributeId => $valueIds) {
                $productsQuery->whereHas('attributeValues', function($q) use ($attributeId, $valueIds){
                    $q->whereIn('attribute_values.id', $valueIds)
                    ->where('attribute_values.attribute_id', $attributeId);
                });
            }
        }

        $productsQuery
            ->when(schema_has_column('products','is_active'), fn($q)=>$q->where('is_active', true));
        $this->applyProductOrder($productsQuery, [['name', 'asc']]);

        $products = $productsQuery
            ->paginate(12)
            ->appends($request->query());

        $rootCategories = \App\Models\Category::query()
            ->whereNull('parent_id')
            ->when(schema_has_column('categories','is_active'), fn($q)=>$q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $searchSlider = $this->getHomeSlider();
        $searchHeroSlides = $this->buildSliderImageSlides($searchSlider);
        $searchHeroBackgroundImage = $searchHeroSlides->first()['src'] ?? null;

        return view('front.search', compact(
            'q',
            'products',
            'category',
            'subcategories',
            'attributes',
            'siblings',
            'rootCategories',
            'searchHeroSlides',
            'searchHeroBackgroundImage'
        ));
    }

    public function freeResources(Request $request)
    {
        $category = (object)[
            'id'        => null,
            'name'      => 'Recursos Gratuitos',
            'slug'      => 'gratuitos',
            'image'     => null,
            'parent_id' => null,
            'parent'    => null,
        ];

        $subcategories = Category::query()
            ->whereNull('parent_id')
            ->when(schema_has_column('categories', 'is_active'), fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $productsQuery = Product::query()
            ->where('price', '<=', 0)
            ->when(schema_has_column('products', 'is_active'), fn($q) => $q->where('is_active', true))
            ->with(['categories', 'attributeValues.attribute']);

        $this->applyProductOrder($productsQuery, [['name', 'asc']]);
        $products = $productsQuery
            ->paginate(12)
            ->appends($request->query());

        $attributes = collect();
        $siblings   = collect();

        return view('front.category', compact(
            'category',
            'subcategories',
            'attributes',
            'siblings',
            'products'
        ))->with('uncategorizedProducts', $products);
    }

    private function excludeFreeProducts($query): void
    {
        if (schema_has_column('products', 'price')) {
            $query->where('price', '>', 0);
        }
    }

    private function applyProductOrder($query, array $fallbackOrders = []): void
    {
        if (schema_has_column('products', 'order')) {
            $query->orderByRaw('`order` IS NULL')->orderBy('order');
        }

        foreach ($fallbackOrders as $fallback) {
            $column = $fallback[0] ?? null;
            if (!$column) {
                continue;
            }
            $direction = $fallback[1] ?? 'asc';
            $query->orderBy($column, $direction);
        }
    }

    private function getSliderBySlug($slug)
    {
        return Slider::where('slug', $slug)->with('images')->first();
    }

    private function getHomeSlider()
    {
        return $this->getSliderByPreferredSlugs(['principal', 'home']);
    }

    private function getContactSlider()
    {
        return $this->getSliderByPreferredSlugs(['contacto', 'principal', 'home']);
    }

    private function findAboutSectionCandidates($posts, array $definition)
    {
        $posts = collect($posts);

        $matches = $posts->filter(function (BlogPost $post) use ($definition) {
            if (in_array($post->slug, $definition['slugs'], true)) {
                return true;
            }

            foreach ($definition['prefixes'] as $prefix) {
                if (Str::startsWith($post->slug, $prefix)) {
                    return true;
                }
            }

            $normalizedTitle = Str::of((string) $post->titulo)->lower()->ascii()->value();
            foreach ($definition['titles'] as $title) {
                if (str_contains($normalizedTitle, Str::of($title)->lower()->ascii()->value())) {
                    return true;
                }
            }

            return false;
        });

        return $matches->sortByDesc(function (BlogPost $post) use ($definition) {
            $score = 0;

            if (in_array($post->slug, $definition['slugs'], true)) {
                $score += 100;
            }

            foreach ($definition['prefixes'] as $prefix) {
                if (Str::startsWith($post->slug, $prefix)) {
                    $score += 40;
                    break;
                }
            }

            $normalizedTitle = Str::of((string) $post->titulo)->lower()->ascii()->value();
            foreach ($definition['titles'] as $title) {
                if (str_contains($normalizedTitle, Str::of($title)->lower()->ascii()->value())) {
                    $score += 20;
                    break;
                }
            }

            if (filled($post->imagen_destacada)) {
                $score += 10;
            }

            if (filled($post->descripcion)) {
                $score += 5;
            }

            return $score;
        })->values();
    }

    private function getSliderByPreferredSlugs(array $preferredSlugs)
    {
        foreach ($preferredSlugs as $slug) {
            $slider = Slider::where('slug', $slug)
                ->where('activo', true)
                ->with('images')
                ->first();

            if ($slider && $slider->images->isNotEmpty()) {
                return $slider;
            }
        }

        return Slider::where('activo', true)
            ->with('images')
            ->get()
            ->first(fn (Slider $slider) => $slider->images->isNotEmpty());
    }

    private function buildSliderImageSlides($slider)
    {
        if (!$slider || $slider->images->isEmpty()) {
            return collect();
        }

        return $slider->images
            ->sortBy('orden')
            ->map(function ($image) {
                return [
                    'src' => asset('storage/' . ltrim((string) $image->imagen, '/')),
                    'alt' => config('app.name', 'Tienda'),
                ];
            })
            ->filter(fn ($slide) => filled($slide['src'] ?? null))
            ->values();
    }

    public function allProducts(Request $request)
    {
        $query = Product::where('is_active', true)->with(['categories', 'attributeValues.attribute']);

        switch ($request->input('sort')) {
            case '1':
                // Más nuevos → por fecha de creación (created_at DESC)
                $query->orderBy('created_at', 'desc');
                break;

            case '2':
                // Más relevantes → destacados primero, luego por id DESC
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('id', 'desc');
                break;

            case '3':
                // Menor a mayor precio
                $query->orderBy('price', 'asc');
                break;

            case '4':
                // Mayor a menor precio
                $query->orderBy('price', 'desc');
                break;

            default:
                // Ninguno → ID mayor a menor
                $this->applyProductOrder($query, [['id', 'desc']]);
                break;
        }

        $products = $query->paginate(12);

        return view('front.products', compact('products'));
    }    

    protected function getDescendantCategoryIds(Category $category): array
    {
        // Traemos solo id/parent_id para minimizar carga
        $all = Category::select('id', 'parent_id')
            ->when(schema_has_column('categories', 'is_active'), fn($q) => $q->where('is_active', true))
            ->get();

        $map = $all->groupBy('parent_id');  // parent_id => [categorías]

        $result = [];
        $queue = $map->get($category->id, collect())->pluck('id')->all();

        while (!empty($queue)) {
            $result = array_merge($result, $queue);
            // próximos niveles
            $next = collect($queue)->flatMap(fn($pid) => $map->get($pid, collect())->pluck('id'))->all();
            $queue = $next;
        }

        // únicos y ordenados
        return array_values(array_unique($result));
    }    
}

if (!function_exists('schema_has_column')) {
    function schema_has_column($table, $column){
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
