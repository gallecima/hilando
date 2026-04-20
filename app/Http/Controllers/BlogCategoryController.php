<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    /**
     * Muestra el listado de categorías.
     */
    public function index()
    {
        $categories = BlogCategory::orderBy('nombre')->get();
        return view('admin.blog.categories.index', compact('categories'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('admin.blog.categories.create');
    }

    /**
     * Guarda una nueva categoría.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        BlogCategory::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(BlogCategory $category)
    {
        return view('admin.blog.categories.edit', compact('category'));
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, BlogCategory $category)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $category->update([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Elimina una categoría.
     */
    public function destroy(BlogCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}