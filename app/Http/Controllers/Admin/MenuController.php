<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('grupo')->orderBy('nombre')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $menuGroups = \App\Models\MenuGroup::orderBy('orden')->get();
        return view('admin.menus.create', compact('menuGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'grupo' => 'nullable|string',
            'ruta' => 'nullable|string',
            'icono' => 'nullable|string',
            'activo' => 'boolean',
            'menu_group_id' => 'nullable|exists:menu_groups,id',
            'orden' => 'nullable|integer',
        ]);

        $data['activo'] = $request->has('activo');
        $data['grupo'] = \App\Models\MenuGroup::find($data['menu_group_id'])->nombre ?? null;

        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menú creado correctamente.');
    }

    public function edit(Menu $menu)
    {
        $menuGroups = \App\Models\MenuGroup::orderBy('orden')->get();
        return view('admin.menus.edit', compact('menu', 'menuGroups'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'grupo' => 'nullable|string',
            'ruta' => 'nullable|string',
            'icono' => 'nullable|string',
            'activo' => 'boolean',
            'menu_group_id' => 'nullable|exists:menu_groups,id',
            'orden' => 'nullable|integer',
        ]);

        $data['activo'] = $request->has('activo');
        $data['grupo'] = \App\Models\MenuGroup::find($data['menu_group_id'])->nombre ?? null;
        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menú actualizado correctamente.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menú eliminado correctamente.');
    }
}