<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuPerfilSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs
        $menus = DB::table('menus')->pluck('id', 'nombre');
        $perfiles = DB::table('perfiles')->pluck('id', 'nombre');

        $vinculaciones = [];

        // Master: acceso a todos
        foreach ($menus as $menuId) {
            $vinculaciones[] = [
                'perfil_id' => $perfiles['Master'],
                'menu_id' => $menuId,
            ];
        }

        // Administrador
        foreach ([
            'Dashboard',
            'Usuarios',
            'Mi Perfil',
            'Empresas',
            'Clientes',
            'Comprobantes',
            'Configuración ARCA',
        ] as $menu) {
            $vinculaciones[] = [
                'perfil_id' => $perfiles['Administrador'],
                'menu_id' => $menus[$menu],
            ];
        }

        // Administrativo
        foreach ([
            'Dashboard',
            'Mi Perfil',
            'Empresas',
            'Clientes',
            'Comprobantes',
        ] as $menu) {
            $vinculaciones[] = [
                'perfil_id' => $perfiles['Administrativo'],
                'menu_id' => $menus[$menu],
            ];
        }

        // Reportes
        foreach ([
            'Dashboard',
            'Comprobantes',
        ] as $menu) {
            $vinculaciones[] = [
                'perfil_id' => $perfiles['Reportes'],
                'menu_id' => $menus[$menu],
            ];
        }

        DB::table('menu_perfil')->insert($vinculaciones);
    }
}