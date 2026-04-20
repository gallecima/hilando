<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('perfiles')->insert([
            ['nombre' => 'Master', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Administrador', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Administrativo', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Reportes', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}