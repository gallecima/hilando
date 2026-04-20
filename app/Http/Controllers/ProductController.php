<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index()
    {
        $productsQuery = Product::with(['categories']);

        if (Schema::hasColumn('products', 'order')) {
            $productsQuery->orderByRaw('`order` IS NULL')->orderBy('order');
        }

        $products = $productsQuery->orderBy('name')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::orderBy('name')->get();
        $attributeValues = AttributeValue::orderBy('value')->get();

        return view('admin.products.create', compact('categories', 'attributes', 'attributeValues'));
    }

    public function store(Request $request)
    {
        $hasNewDownloadableFiles = $request->hasFile('downloadable_file') || $request->hasFile('downloadable_files');
        $isDigitalRequested = $request->boolean('is_digital') || $hasNewDownloadableFiles;

        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'nullable|string|unique:products,slug',
            'sku' => 'nullable|string|unique:products,sku',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_min_quantity' => 'nullable|integer|min:1',
            'stock' => $isDigitalRequested ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'is_active' => 'boolean',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'is_digital' => 'boolean',
            'meta_title' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
            'featured_image' => 'nullable|image|max:8192',
            'downloadable_file' => 'nullable|file|mimes:pdf,zip',
            'downloadable_files' => 'nullable|array',
            'downloadable_files.*' => 'file|mimes:pdf,zip',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->generateSlug($request->name);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_digital'] = $isDigitalRequested ? 1 : 0;
        $data['is_new'] = $request->has('is_new') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['base_price'] = $this->normalizeBasePrice($data['base_price'] ?? null, (float) $data['price']);
        $data['wholesale_price'] = $this->normalizeWholesalePrice($data['wholesale_price'] ?? null);
        $data['wholesale_min_quantity'] = $this->normalizeWholesaleMinQuantity($data['wholesale_min_quantity'] ?? null);

        if ($data['is_digital']) {
            $data['stock'] = max((int) ($data['stock'] ?? 0), 1);
            $data['height'] = null;
            $data['width'] = null;
            $data['length'] = null;
            $data['weight'] = null;
        } else {
            $data['stock'] = (int) ($data['stock'] ?? 0);
        }

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('uploads/products', 'public');
            $data['featured_image'] = $path;
        }

        $downloadableFiles = $this->storeDownloadableFiles($request);
        if (!empty($downloadableFiles)) {
            $data['downloadable_files_json'] = $downloadableFiles;
            $data['downloadable_file_path'] = $downloadableFiles[0];
            $data['is_digital'] = 1;
        } else {
            $data['downloadable_files_json'] = null;
            $data['downloadable_file_path'] = null;
        }

        $product = Product::create($data);
        $product->categories()->sync($request->input('categories', []));

        // Sincronizar atributos con stock y precio si aplica
        // $product->attributeValues()->sync(
        //     collect($request->input('attribute_values', []))->mapWithKeys(function ($valueId) use ($request) {
        //         return [$valueId => [
        //             'stock' => $request->input("attribute_meta.$valueId.stock", null),
        //             'price' => $request->input("attribute_meta.$valueId.price", null),
        //         ]];
        //     })->toArray()
        // );

        $syncData = [];

        foreach ($request->input('attribute_values', []) as $valueId) {
            $imageKey = "attribute_meta.$valueId.image";

            // Si se subió imagen para esta variante, guardarla
            $imagePath = null;
            if ($request->hasFile($imageKey)) {
                $imagePath = $request->file($imageKey)->store("uploads/products/variants", 'public');
            }

            $syncData[$valueId] = [
                'stock' => $request->input("attribute_meta.$valueId.stock", null),
                'price' => $request->input("attribute_meta.$valueId.price", null),
                'image' => $imagePath,
            ];
        }

        $product->attributeValues()->sync($syncData);


        $galleryRaw = $request->input('gallery_images');
        $gallery = json_decode(is_string($galleryRaw) ? $galleryRaw : '[]', true);

        if (!is_array($gallery)) {
            $gallery = [];
        }

        foreach ($gallery as $imagePath) {
            if (!is_string($imagePath) || trim($imagePath) === '') {
                continue;
            }

            $normalizedPath = ltrim($imagePath, '/');
            $finalPath = null;

            if (str_starts_with($normalizedPath, 'uploads/products/gallery/')) {
                $finalPath = $normalizedPath;
            } else {
                $fileName = basename($normalizedPath);
                if (Storage::disk('public')->exists("temp/{$fileName}")) {
                    $targetPath = "uploads/products/gallery/{$fileName}";
                    Storage::disk('public')->move("temp/{$fileName}", $targetPath);
                    $finalPath = $targetPath;
                }
            }

            if ($finalPath && !$product->images()->where('path', $finalPath)->exists()) {
                $product->images()->create(['path' => $finalPath]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        $product->load(['categories', 'attributeValues']);

        $categories = Category::orderBy('name')->get();
        $categoryIds = $product->categories->pluck('id');
        $categoriesWithAttributes = Category::with('attributes.values')->whereIn('id', $categoryIds)->get();

        $attributes = collect();
        foreach ($categoriesWithAttributes as $category) {
            foreach ($category->attributes as $attribute) {
                if (!$attributes->contains('id', $attribute->id)) {
                    $attributes->push($attribute);
                }
            }
        }

        $attributeMeta = [];
        foreach ($product->attributeValues as $value) {
            $attributeMeta[$value->id] = [
                'stock' => $value->pivot->stock,
                'price' => $value->pivot->price,
                'image' => $value->pivot->image,
            ];
        }

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'attributes',
            'attributeMeta'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $existingDownloadableFiles = $product->downloadable_files;
        $removeAllDownloadableFiles = $request->boolean('remove_downloadable_file');
        $removeDownloadableFiles = collect((array) $request->input('remove_downloadable_files', []))
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn (string $path) => ltrim(trim($path), '/'))
            ->unique()
            ->values()
            ->all();
        $existingFilesAfterRemoval = $removeAllDownloadableFiles
            ? []
            : array_values(array_diff($existingDownloadableFiles, $removeDownloadableFiles));
        $hasNewDownloadableFiles = $request->hasFile('downloadable_file') || $request->hasFile('downloadable_files');

        $isDigitalRequested = $request->boolean('is_digital')
            || $hasNewDownloadableFiles
            || !empty($existingFilesAfterRemoval);

        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_min_quantity' => 'nullable|integer|min:1',
            'stock' => $isDigitalRequested ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'is_active' => 'boolean',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'is_digital' => 'boolean',
            'meta_title' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_new' => 'boolean',
            'is_featured' => 'boolean',
            'featured_image' => 'nullable|image|max:8192',
            'downloadable_file' => 'nullable|file|mimes:pdf,zip',
            'downloadable_files' => 'nullable|array',
            'downloadable_files.*' => 'file|mimes:pdf,zip',
            'remove_downloadable_file' => 'nullable|boolean',
            'remove_downloadable_files' => 'nullable|array',
            'remove_downloadable_files.*' => 'string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        if ($product->slug !== Str::slug($request->name)) {
            // Solo actualiza el slug si cambió el nombre (y por lo tanto cambiaría el slug)
            $newSlug = $this->generateSlug($request->name);

            // Evitar slug duplicado
            if (Product::where('slug', $newSlug)->where('id', '!=', $product->id)->exists()) {
                return back()->withErrors(['name' => 'Ya existe otro producto con un slug similar.'])->withInput();
            }

            $data['slug'] = $newSlug;
        } else {
            // Conserva el slug existente
            $data['slug'] = $product->slug;
        }

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_digital'] = $isDigitalRequested ? 1 : 0;
        $data['is_new'] = $request->has('is_new') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['base_price'] = $this->normalizeBasePrice($data['base_price'] ?? null, (float) $data['price']);
        $data['wholesale_price'] = $this->normalizeWholesalePrice($data['wholesale_price'] ?? null);
        $data['wholesale_min_quantity'] = $this->normalizeWholesaleMinQuantity($data['wholesale_min_quantity'] ?? null);

        if ($data['is_digital']) {
            $resolvedStock = $data['stock'] ?? $product->stock;
            $data['stock'] = max((int) $resolvedStock, 1);
            $data['height'] = null;
            $data['width'] = null;
            $data['length'] = null;
            $data['weight'] = null;
        } else {
            $data['stock'] = (int) ($data['stock'] ?? 0);
        }

        if ($request->hasFile('featured_image')) {
            if ($product->featured_image) {
                Storage::disk('public')->delete($product->featured_image);
            }
            $path = $request->file('featured_image')->store('uploads/products', 'public');
            $data['featured_image'] = $path;

            \Log::info('[NUEVA FOTO] Intentando agregar producto', [
                'path'         => $path
            ]);
            
        }

        $newDownloadableFiles = $this->storeDownloadableFiles($request);
        $filesToDelete = $removeAllDownloadableFiles
            ? $existingDownloadableFiles
            : array_values(array_intersect($existingDownloadableFiles, $removeDownloadableFiles));
        foreach ($filesToDelete as $pathToDelete) {
            Storage::disk('public')->delete($pathToDelete);
        }

        $finalDownloadableFiles = array_values(array_unique(array_merge($existingFilesAfterRemoval, $newDownloadableFiles)));
        if (!empty($finalDownloadableFiles)) {
            $data['downloadable_files_json'] = $finalDownloadableFiles;
            $data['downloadable_file_path'] = $finalDownloadableFiles[0];
            $data['is_digital'] = 1;
        } else {
            $data['downloadable_files_json'] = null;
            $data['downloadable_file_path'] = null;
        }

        $product->update($data);
        $product->categories()->sync($request->input('categories', []));

        // $product->attributeValues()->sync(
        //     collect($request->input('attribute_values', []))->mapWithKeys(function ($valueId) use ($request) {
        //         return [$valueId => [
        //             'stock' => $request->input("attribute_meta.$valueId.stock", null),
        //             'price' => $request->input("attribute_meta.$valueId.price", null),
        //         ]];
        //     })->toArray()
        // );

        // Obtener valores actuales desde la relación
        $currentMeta = $product->attributeValues()->withPivot('stock', 'price', 'image')->get()->keyBy('id');

        $syncData = [];

        foreach ($request->input('attribute_values', []) as $valueId) {
            $imageKey = "attribute_meta.$valueId.image";

            // Si se subió nueva imagen
            if ($request->hasFile($imageKey)) {
                $imagePath = $request->file($imageKey)->store("uploads/products/variants", 'public');
            } else {
                // Mantener imagen anterior (si existe)
                $imagePath = $currentMeta[$valueId]->pivot->image ?? null;
            }

            $syncData[$valueId] = [
                'stock' => $request->input("attribute_meta.$valueId.stock", null),
                'price' => $request->input("attribute_meta.$valueId.price", null),
                'image' => $imagePath,
            ];
        }

        $product->attributeValues()->sync($syncData);   

        $galleryRaw = $request->input('gallery_images');
        $gallery = json_decode(is_string($galleryRaw) ? $galleryRaw : '[]', true);

        if (!is_array($gallery)) {
            $gallery = [];
        }

        $existingImageIds = array_values(array_map('intval', array_filter($gallery, fn($v) => is_numeric($v))));

        if ($request->has('gallery_images')) {
            $imagesToDelete = $product->images();
            if (!empty($existingImageIds)) {
                $imagesToDelete->whereNotIn('id', $existingImageIds);
            }
            $imagesToDelete->get()->each(function ($img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            });
        }

        foreach ($gallery as $imageIdOrPath) {
            if (is_numeric($imageIdOrPath)) {
                continue;
            }

            if (!is_string($imageIdOrPath) || trim($imageIdOrPath) === '') {
                continue;
            }

            $normalizedPath = ltrim($imageIdOrPath, '/');
            $finalPath = null;

            if (str_starts_with($normalizedPath, 'uploads/products/gallery/')) {
                $finalPath = $normalizedPath;
            } else {
                $fileName = basename($normalizedPath);
                if (Storage::disk('public')->exists("temp/{$fileName}")) {
                    $targetPath = "uploads/products/gallery/{$fileName}";
                    Storage::disk('public')->move("temp/{$fileName}", $targetPath);
                    $finalPath = $targetPath;
                }
            }

            if ($finalPath && !$product->images()->where('path', $finalPath)->exists()) {
                $product->images()->create(['path' => $finalPath]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
        }
        foreach ($product->downloadable_files as $downloadablePath) {
            Storage::disk('public')->delete($downloadablePath);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['orders'] as $productId => $order) {
            if (!is_numeric($productId)) {
                continue;
            }

            Product::whereKey((int) $productId)->update(['order' => $order]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Orden de productos actualizado.');
    }

    public function getAttributesByCategories(Request $request)
    {
        $categoryIds = $request->input('categories', []);

        if (empty($categoryIds)) {
            return response()->json(['html' => '']);
        }

        $categories = Category::with(['attributes.values'])
            ->whereIn('id', $categoryIds)
            ->get();

        $attributes = collect();
        foreach ($categories as $category) {
            foreach ($category->attributes as $attribute) {
                if (!$attributes->contains('id', $attribute->id)) {
                    $attributes->push($attribute);
                }
            }
        }

        if ($attributes->isEmpty()) {
            return response()->json(['html' => '<p class="text-muted">No hay atributos para estas categorías.</p>']);
        }

        $html = view('admin.products.partials.attributes-checkboxes', compact('attributes'))->render();
        return response()->json(['html' => $html]);
    }

    public function uploadTempImage(Request $request)
    {
        $request->validate(['file' => 'required|image|max:8192']);
        $path = $request->file('file')->store('temp', 'public');
        return response()->json(['path' => $path]);
    }

    private function normalizeBasePrice(mixed $basePrice, float $price): ?float
    {
        if ($basePrice === null || $basePrice === '') {
            return null;
        }

        $base = (float) $basePrice;
        if ($base <= $price) {
            return null;
        }

        return round($base, 2);
    }

    private function normalizeWholesalePrice(mixed $wholesalePrice): ?float
    {
        if ($wholesalePrice === null || $wholesalePrice === '') {
            return null;
        }

        return round((float) $wholesalePrice, 2);
    }

    private function normalizeWholesaleMinQuantity(mixed $minQuantity): ?int
    {
        if ($minQuantity === null || $minQuantity === '') {
            return null;
        }

        return max(1, (int) $minQuantity);
    }

    /**
     * @return array<int, string>
     */
    private function storeDownloadableFiles(Request $request): array
    {
        $paths = [];

        if ($request->hasFile('downloadable_file')) {
            $paths[] = $request->file('downloadable_file')->store('uploads/products/downloads', 'public');
        }

        if ($request->hasFile('downloadable_files')) {
            foreach ((array) $request->file('downloadable_files') as $file) {
                if (!$file) {
                    continue;
                }

                $paths[] = $file->store('uploads/products/downloads', 'public');
            }
        }

        return collect($paths)
            ->map(fn (string $path) => ltrim(trim($path), '/'))
            ->filter(fn (string $path) => $path !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function generateSlug($name)
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
