<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
use App\Models\Locality;
use Illuminate\Database\Seeder;

class LocalitySeeder extends Seeder
{
    public function run(): void
    {
        $argentina = Country::where('name', 'Argentina')->firstOrFail();

        $localities = [

            // BUENOS AIRES
            'Buenos Aires' => [
                'La Plata',
                'Mar del Plata',
                'Bahía Blanca',
                'Tandil',
                'San Nicolás de los Arroyos',
            ],

            // CATAMARCA
            'Catamarca' => [
                'San Fernando del Valle de Catamarca',
                'Valle Viejo',
                'Andalgalá',
                'Belén',
                'Tinogasta',
            ],

            // CHACO
            'Chaco' => [
                'Resistencia',
                'Presidencia Roque Sáenz Peña',
                'Villa Ángela',
                'Charata',
                'General San Martín',
            ],

            // CHUBUT
            'Chubut' => [
                'Rawson',
                'Trelew',
                'Puerto Madryn',
                'Comodoro Rivadavia',
                'Esquel',
            ],

            // CÓRDOBA
            'Córdoba' => [
                'Córdoba',
                'Villa María',
                'Río Cuarto',
                'San Francisco',
                'Villa Carlos Paz',
            ],

            // CORRIENTES
            'Corrientes' => [
                'Corrientes',
                'Goya',
                'Paso de los Libres',
                'Mercedes',
                'Santo Tomé',
            ],

            // ENTRE RÍOS
            'Entre Ríos' => [
                'Paraná',
                'Concordia',
                'Gualeguaychú',
                'Gualeguay',
                'Villaguay',
            ],

            // FORMOSA
            'Formosa' => [
                'Formosa',
                'Clorinda',
                'El Colorado',
                'Pirané',
                'Las Lomitas',
            ],

            // JUJUY
            'Jujuy' => [
                'San Salvador de Jujuy',
                'Palpalá',
                'Libertador General San Martín',
                'San Pedro de Jujuy',
                'La Quiaca',
            ],

            // LA PAMPA
            'La Pampa' => [
                'Santa Rosa',
                'General Pico',
                'Toay',
                'Realicó',
                'Eduardo Castex',
            ],


            // LA RIOJA
            'La Rioja' => [
                'La Rioja',
                'Chilecito',
                'Aimogasta',
                'Chamical',
                'Chepes',
            ],

            // MENDOZA
            'Mendoza' => [
                'Mendoza',
                'San Rafael',
                'Godoy Cruz',
                'Guaymallén',
                'Maipú',
            ],

            // MISIONES
            'Misiones' => [
                'Posadas',
                'Eldorado',
                'Oberá',
                'Puerto Iguazú',
                'Apóstoles',
            ],

            // NEUQUÉN
            'Neuquén' => [
                'Neuquén',
                'Plottier',
                'Cutral Có',
                'Zapala',
                'San Martín de los Andes',
            ],

            // RÍO NEGRO
            'Río Negro' => [
                'Viedma',
                'San Carlos de Bariloche',
                'General Roca',
                'Cipolletti',
                'Villa Regina',
            ],


            // SALTA
            'Salta' => [
                'Salta',
                'San Ramón de la Nueva Orán',
                'Tartagal',
                'General Güemes',
                'Metán',
            ],

            // SAN JUAN
            'San Juan' => [
                'San Juan',
                'Rawson',
                'Chimbas',
                'Pocito',
                'Caucete',
            ],

            // SAN LUIS
            'San Luis' => [
                'San Luis',
                'Villa Mercedes',
                'La Punta',
                'Justo Daract',
                'Merlo',
            ],

            // SANTA CRUZ
            'Santa Cruz' => [
                'Río Gallegos',
                'Caleta Olivia',
                'Puerto Deseado',
                'Pico Truncado',
                'Las Heras',
            ],

            // SANTA FE
            'Santa Fe' => [
                'Santa Fe',
                'Rosario',
                'Rafaela',
                'Venado Tuerto',
                'Villa Gobernador Gálvez',
            ],            

                   
        // SANTIAGO DEL ESTERO
        'Santiago del Estero' => [
            'Santiago del Estero',
            'La Banda',
            'Termas de Río Hondo',
            'Frías',
            'Añatuya',
        ],

        // TIERRA DEL FUEGO
        'Tierra del Fuego' => [
            'Ushuaia',
            'Río Grande',
            'Tolhuin',
            'San Sebastián',
            'Puerto Almanza',
        ],

        // TUCUMÁN
        'Tucumán' => [
            'San Miguel de Tucumán',
            'Tafí Viejo',
            'Yerba Buena',
            'Concepción',
            'Banda del Río Salí',
        ],            

        ];

        foreach ($localities as $provinceName => $cities) {
            $province = Province::where('name', $provinceName)
                ->where('country_id', $argentina->id)
                ->first();

            if (!$province) {
                $this->command->warn("Provincia no encontrada: $provinceName");
                continue;
            }

            foreach ($cities as $cityName) {
                Locality::firstOrCreate([
                    'name' => $cityName,
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}