<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('created_at', 'desc')->get();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'slug' => 'nullable|string|unique:sliders,slug',
            'activo' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['nombre']);
        $data['activo'] = $request->has('activo');

        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider creado correctamente.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'slug' => 'required|string|unique:sliders,slug,' . $slider->id,
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->has('activo');

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider actualizado correctamente.');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('admin.sliders.index')->with('success', 'Slider eliminado.');
    }

    public function images(Slider $slider)
    {
        $images = $slider->images()->orderBy('orden')->get();
        return view('admin.sliders.images', compact('slider', 'images'));
    }
}