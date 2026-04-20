<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('menu_groups')->where('nombre', 'Gestión')->value('id');

        $menus = [
            [
                'nombre' => 'Puntos de Envío',
                'ruta' => 'admin/shipping-points',
                'icono' => 'fa fa-location-dot',
                'orden' => 8,
            ],
            [
                'nombre' => 'Cajas de Envío',
                'ruta' => 'admin/shipping-boxes',
                'icono' => 'fa fa-box-open',
                'orden' => 9,
            ],
        ];

        $insertedIds = [];

        foreach ($menus as $menu) {
            $existingId = DB::table('menus')->where('ruta', $menu['ruta'])->value('id');
            if ($existingId) {
                DB::table('menus')->where('id', $existingId)->update([
                    'nombre' => $menu['nombre'],
                    'grupo' => $groupId ? 'Gestión' : null,
                    'icono' => $menu['icono'],
                    'activo' => 1,
                    'orden' => $menu['orden'],
                    'menu_group_id' => $groupId,
                    'updated_at' => now(),
                ]);
                $insertedIds[] = (int) $existingId;
                continue;
            }

            $insertedIds[] = (int) DB::table('menus')->insertGetId([
                'nombre' => $menu['nombre'],
                'ruta' => $menu['ruta'],
                'grupo' => $groupId ? 'Gestión' : null,
                'icono' => $menu['icono'],
                'activo' => 1,
                'orden' => $menu['orden'],
                'menu_group_id' => $groupId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $perfilIds = DB::table('perfiles')
            ->whereRaw('LOWER(nombre) = ?', ['master'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!$perfilIds) {
            $mainPerfilId = DB::table('perfiles')->orderBy('id')->value('id');
            if ($mainPerfilId) {
                $perfilIds = [(int) $mainPerfilId];
            }
        }

        foreach ($perfilIds as $perfilId) {
            foreach ($insertedIds as $menuId) {
                $exists = DB::table('menu_perfil')
                    ->where('perfil_id', $perfilId)
                    ->where('menu_id', $menuId)
                    ->exists();

                if (!$exists) {
                    DB::table('menu_perfil')->insert([
                        'perfil_id' => $perfilId,
                        'menu_id' => $menuId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $routes = ['admin/shipping-points', 'admin/shipping-boxes'];
        $menuIds = DB::table('menus')->whereIn('ruta', $routes)->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('menu_perfil')->whereIn('menu_id', $menuIds)->delete();
            DB::table('menus')->whereIn('id', $menuIds)->delete();
        }
    }
};
