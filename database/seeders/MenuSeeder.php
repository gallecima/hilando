<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('menus')->insert([
            // Grupo: sin grupo (dashboard)
            [
                'nombre' => 'Dashboard',
                'grupo' => null,
                'ruta' => 'dashboard',
                'icono' => 'si si-speedometer',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Grupo: Configuración
            [
                'nombre' => 'Usuarios',
                'grupo' => 'Configuración',
                'ruta' => 'usuarios.index',
                'icono' => 'si si-users',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Perfiles',
                'grupo' => 'Configuración',
                'ruta' => 'perfiles.index',
                'icono' => 'si si-key',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Mi Perfil',
                'grupo' => 'Configuración',
                'ruta' => 'profile.edit',
                'icono' => 'si si-user',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Grupo: Trabajo
            [
                'nombre' => 'Empresas',
                'grupo' => 'Trabajo',
                'ruta' => 'empresas.index',
                'icono' => 'si si-briefcase',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Clientes',
                'grupo' => 'Trabajo',
                'ruta' => 'clientes.index',
                'icono' => 'si si-people',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Comprobantes',
                'grupo' => 'Trabajo',
                'ruta' => 'comprobantes.index',
                'icono' => 'si si-docs',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Configuración ARCA',
                'grupo' => 'Trabajo',
                'ruta' => 'arca.configuracion',
                'icono' => 'si si-settings',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}