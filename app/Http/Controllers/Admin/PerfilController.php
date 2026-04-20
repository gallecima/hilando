<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Menu;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        $perfiles = Perfil::with('menus')->get();
        return view('admin.perfiles.index', compact('perfiles'));
    }

    public function create()
    {
        $menus = Menu::where('activo', 1)->orderBy('grupo')->orderBy('nombre')->get();
        return view('admin.perfiles.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id',
        ]);

        $perfil = Perfil::create([
            'nombre' => $data['nombre']
        ]);

        $perfil->menus()->sync($data['menus'] ?? []);

        return redirect()->route('admin.perfiles.index')->with('success', 'Perfil creado correctamente.');
    }


    public function edit(Perfil $perfil)
    {
        $menus = Menu::orderBy('grupo')->orderBy('nombre')->get();
        $menusSeleccionados = $perfil->menus->pluck('id')->toArray();

        return view('admin.perfiles.edit', compact('perfil', 'menus', 'menusSeleccionados'));
    }

    public function update(Request $request, Perfil $perfil)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'menus' => 'array|exists:menus,id',
        ]);

        $perfil->update([
            'nombre' => $data['nombre']
        ]);

        $perfil->menus()->sync($data['menus'] ?? []);

        return redirect()->route('admin.perfiles.index')->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(Perfil $perfil)
    {
        if ($perfil->users()->exists()) {
            return redirect()->route('admin.perfiles.index')->with('error', 'No se puede eliminar un perfil con usuarios asignados.');
        }

        $perfil->menus()->detach();
        $perfil->delete();

        return redirect()->route('admin.perfiles.index')->with('success', 'Perfil eliminado correctamente.');
    }
}