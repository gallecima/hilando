<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;

class PluginAdminController extends Controller
{
    public function __construct(protected PluginManager $manager) {}

    public function index()
    {
        $catalog   = $this->manager->catalog();              // lo encontrado en /plugins
        $installed = Plugin::all()->keyBy('slug');           // estado en DB
        return view('admin.plugins.index', compact('catalog','installed'));
    }

    public function install(string $slug)
    {
        $catalog = $this->manager->catalog();
        abort_unless(isset($catalog[$slug]), 404);

        $meta = $catalog[$slug];

        $row = Plugin::firstOrCreate(
            ['slug' => $slug],
            [
                'name'         => $meta['name'],
                'version'      => $meta['version'],
                'is_installed' => true,
                'is_active'    => true,
                'installed_at' => now(),
                'config'       => $meta['defaults'] ?? [],
            ]
        );

        if (! $row->wasRecentlyCreated) {
            $row->update([
                'is_installed' => true,
                'is_active'    => true,
                'installed_at' => $row->installed_at ?: now(),
                // No pisamos config previa del usuario
            ]);
        }

        return back()->with('success', 'Plugin instalado/activado.');
    }

    public function toggle(string $slug)
    {
        $row = Plugin::where('slug',$slug)->firstOrFail();
        $row->update(['is_active' => ! $row->is_active]);
        return back()->with('success', 'Plugin '.($row->is_active ? 'activado' : 'desactivado').'.');
    }

    // Pantalla “genérica” por si el plugin no trae settings propio
    public function edit(string $slug)
    {
        $plugin = Plugin::where('slug',$slug)->firstOrFail();
        return view('admin.plugins.edit-generic', compact('plugin'));
    }

    public function update(Request $request, string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->firstOrFail();

        // Config existente (de la vista)
        $base = $request->input('config', []);

        // Nuevas claves desde la vista (arrays paralelos)
        $newK = $request->input('config_keys_new', []);
        $newV = $request->input('config_new_values', []);

        // Mergea las nuevas claves al array base
        foreach ($newK as $i => $k) {
            $k = trim((string)$k);
            if ($k === '') continue; // ignora vacíos
            $base[$k] = $newV[$i] ?? null;
        }

        // Guarda la configuración final en JSON o array según tu modelo
        $plugin->update([
            'config' => $base
        ]);

        return redirect()
            ->route('admin.plugins.index')
            ->with('success', 'Configuración guardada.');
    }
}