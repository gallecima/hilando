<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        $attributes = Attribute::all();
        return view('admin.categories.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateSlug($data['name']);

        if ($this->slugExists($data['slug'])) {
            return back()->withInput()->withErrors(['name' => 'Ya existe una categoría con este slug.']);
        }

        $category = new Category($data);
        $category->save();

        $this->handleUploads($request, $category);

        $category->attributes()->sync($data['attributes'] ?? []);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->get();

        $attributes = Attribute::all();

        return view('admin.categories.edit', compact('category', 'categories', 'attributes'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $this->generateSlug($data['name'], $category->id);

        if ($this->slugExists($data['slug'], $category->id)) {
            return back()->withInput()->withErrors(['name' => 'Ya existe una categoría con este slug.']);
        }

        $category->fill($data);
        $this->handleUploads($request, $category);
        $category->save();

        $category->attributes()->sync($data['attributes'] ?? []);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'order' => 'integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:attributes,id',
        ]);
    }

    protected function generateSlug(string $name, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'categoria';
        $slug = $baseSlug;
        $count = 1;

        while ($this->slugExists($slug, $exceptId)) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $query = Category::where('slug', $slug);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    protected function handleUploads(Request $request, Category $category): void
    {
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $path = $request->file('image')->storeAs(
                'uploads/categories',
                Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension(),
                'public'
            );

            $category->image = $path;
        }

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $path = $request->file('icon')->storeAs(
                'uploads/categories',
                Str::uuid() . '.' . $request->file('icon')->getClientOriginalExtension(),
                'public'
            );

            $category->icon = $path;
        }
    }

    public function crop(Request $request, Category $category)
    {
        $request->validate([
            'cropped_image' => 'required|file|image',
            'target' => 'required|in:image,icon',
        ]);

        $file = $request->file('cropped_image');
        $path = $file->storeAs(
            'uploads/categories',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'public'
        );

        if ($request->target === 'image') {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->image = $path;
        } else {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $category->icon = $path;
        }

        $category->save();

        return response()->json(['success' => true, 'path' => $path]);
    }
}
