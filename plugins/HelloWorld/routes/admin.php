<?php

use Illuminate\Support\Facades\Route;
use App\Models\Plugin;

// Rutas de configuración propias del plugin
Route::middleware(['web','auth'])
    ->prefix('admin/plugins/helloworld')
    ->name('admin.plugins.helloworld.')
    ->group(function () {
        Route::get('/', function () {
            $plugin = Plugin::where('slug','helloworld')->firstOrFail();
            return view('helloworld::settings', compact('plugin'));
        })->name('edit');

        Route::post('/', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'message'  => 'required|string|max:1000',
                'hook'     => 'required|string',
                'contexts' => 'array',
                'style'    => 'nullable|string|max:255',
            ]);
            $plugin = Plugin::where('slug','helloworld')->firstOrFail();
            $plugin->update(['config' => $data]);

            return back()->with('success','Configuración guardada.');
        })->name('settings.save');
    });