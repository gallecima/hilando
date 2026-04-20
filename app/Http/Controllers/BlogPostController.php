<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    protected function blogPostProductPivotExists(): bool
    {
        try {
            return Schema::hasTable('blog_post_product');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = BlogPost::with(['categoria', 'autor'])->orderBy('fecha', 'desc')->get();
        return view('admin.blog.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = BlogCategory::all();
        $canLinkProducts = $this->blogPostProductPivotExists();
        $products   = $canLinkProducts ? Product::orderBy('name')->get() : collect();
        return view('admin.blog.posts.create', compact('categorias', 'products', 'canLinkProducts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'bajada' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'imagen_destacada' => 'nullable|image|max:2048',
            'activo' => 'nullable|boolean',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        if ($request->hasFile('imagen_destacada')) {
            $validated['imagen_destacada'] = $request->file('imagen_destacada')->store('blog', 'public');
        }
        

        $validated['user_id'] = auth()->id();

        if (!$validated['user_id']) {
            abort(403, 'No autorizado. Usuario no autenticado.');
        }
        $validated['activo'] = $request->has('activo');

        $slugBase = Str::slug($validated['titulo']);
        $slug = $slugBase;
        $counter = 1;

        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter++;
        }

        $validated['slug'] = $slug;

        $productIds = $validated['products'] ?? [];
        unset($validated['products']);

        $post = BlogPost::create($validated);
        if ($this->blogPostProductPivotExists()) {
            $post->products()->sync($productIds);
        }

        return redirect()->route('admin.blog.posts.index')->with('success', 'Post creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::all();
        $canLinkProducts = $this->blogPostProductPivotExists();
        $products   = $canLinkProducts ? Product::orderBy('name')->get() : collect();
        $selectedProductIds = [];
        if ($canLinkProducts) {
            $post->load('products');
            $selectedProductIds = $post->products->pluck('id')->all();
        }
        return view('admin.blog.posts.edit', [
            'post' => $post,
            'categories' => $categories,
            'products' => $products,
            'selectedProductIds' => $selectedProductIds,
            'canLinkProducts' => $canLinkProducts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'bajada' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'imagen_destacada' => 'nullable|image|max:2048',
            'activo' => 'nullable|boolean',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        if ($request->hasFile('imagen_destacada')) {
            if ($post->imagen_destacada) {
                Storage::disk('public')->delete($post->imagen_destacada);
            }

            $validated['imagen_destacada'] = $request->file('imagen_destacada')->store('blog', 'public');
        }

        $validated['activo'] = $request->has('activo');

        if (blank($post->slug)) {
            $slugBase = Str::slug($validated['titulo']);
            $slug = $slugBase;
            $counter = 1;

            while (BlogPost::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $slugBase . '-' . $counter++;
            }

            $validated['slug'] = $slug;
        }

        $productIds = $validated['products'] ?? [];
        unset($validated['products']);

        $post->update($validated);
        if ($this->blogPostProductPivotExists()) {
            $post->products()->sync($productIds);
        }

        return redirect()->route('admin.blog.posts.index')->with('success', 'Post actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogPost $post)
    {
        if ($post->imagen_destacada) {
            Storage::disk('public')->delete($post->imagen_destacada);
        }

        if ($this->blogPostProductPivotExists()) {
            $post->products()->detach();
        }
        $post->delete();

        return redirect()->route('admin.blog.posts.index')->with('success', 'Post eliminado correctamente.');
    }


    public function showPost($slug)
    {
        $post = BlogPost::with(['categoria', 'autor'])->where('slug', $slug)->firstOrFail();

        abort_unless($post->activo, 404);

        $pivotExists = $this->blogPostProductPivotExists();

        $hasIsActiveColumn = false;
        try {
            $hasIsActiveColumn = Schema::hasColumn('products', 'is_active');
        } catch (\Throwable $e) {
            $hasIsActiveColumn = false;
        }

        $linkedProducts = $pivotExists
            ? $post->products()
                ->when($hasIsActiveColumn, fn ($q) => $q->where('is_active', true))
                ->with(['attributeValues.attribute'])
                ->orderBy('name')
                ->get()
            : collect();

        $otrosPosts = BlogPost::where('activo', true)
            ->where('id', '!=', $post->id)
            ->when($post->categoria, function ($query) use ($post) {
                return $query->where('blog_category_id', $post->blog_category_id);
            })
            ->orderByDesc('fecha')
            ->take(4)
            ->get();

        return view('front.post-show', compact('post', 'otrosPosts', 'linkedProducts'));
    }

    public function category($slug)
    {
        $categoria = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::where('blog_category_id', $categoria->id)
                    ->where('activo', true)
                    ->latest('fecha')
                    ->paginate(9);

        return view('front.posts-category', compact('categoria', 'posts'));
    }    
}
