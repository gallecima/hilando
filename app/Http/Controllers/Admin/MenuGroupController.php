<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuGroup;
use Illuminate\Http\Request;

class MenuGroupController extends Controller
{
    public function index()
    {
        $menuGroups = MenuGroup::orderBy('orden')->get();
        return view('admin.menu-groups.index', compact('menuGroups'));
    }

    public function create()
    {
        return view('admin.menu-groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'orden' => 'nullable|integer',
        ]);

        MenuGroup::create($data);

        return redirect()->route('admin.menu-groups.index')->with('success', 'Grupo creado.');
    }

    public function edit(MenuGroup $menuGroup)
    {
        return view('admin.menu-groups.edit', compact('menuGroup'));
    }

    public function update(Request $request, MenuGroup $menuGroup)
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'orden' => 'nullable|integer',
        ]);

        $menuGroup->update($data);

        return redirect()->route('admin.menu-groups.index')->with('success', 'Grupo actualizado.');
    }

    public function destroy(MenuGroup $menuGroup)
    {
        if ($menuGroup->menus()->count()) {
            return redirect()->route('admin.menu-groups.index')->with('error', 'No se puede eliminar: hay menús usando este grupo.');
        }

        $menuGroup->delete();
        return redirect()->route('admin.menu-groups.index')->with('success', 'Grupo eliminado.');
    }
}