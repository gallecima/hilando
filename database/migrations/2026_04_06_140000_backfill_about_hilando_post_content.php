<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blog_posts') || !Schema::hasTable('blog_categories')) {
            return;
        }

        $categoryId = DB::table('blog_categories')->where('slug', 'sobre-hilando')->value('id');
        if (!$categoryId) {
            return;
        }

        $templates = [
            'quienes-somos' => [
                'bajada' => 'Una marca que pone en valor el oficio, la materia y las historias que atraviesan cada pieza.',
                'descripcion' => "Hilando es un proyecto que cruza diseno, produccion y cultura material para construir una tienda con identidad propia.\n\nReunimos productos, relatos y procesos que nacen del trabajo consciente, de la seleccion de materiales y de una mirada contemporanea sobre lo artesanal.\n\nLa marca se apoya en una idea simple: cada objeto cotidiano puede ser tambien una forma de narrar de donde venimos, como producimos y que valores queremos sostener en el tiempo.",
            ],
            'quienes-somos-1' => [
                'bajada' => 'Una marca que pone en valor el oficio, la materia y las historias que atraviesan cada pieza.',
                'descripcion' => "Hilando es un proyecto que cruza diseno, produccion y cultura material para construir una tienda con identidad propia.\n\nReunimos productos, relatos y procesos que nacen del trabajo consciente, de la seleccion de materiales y de una mirada contemporanea sobre lo artesanal.\n\nLa marca se apoya en una idea simple: cada objeto cotidiano puede ser tambien una forma de narrar de donde venimos, como producimos y que valores queremos sostener en el tiempo.",
            ],
            'manifesto' => [
                'bajada' => 'Una serie de ideas que ordenan nuestra forma de disenar, producir y compartir.',
                'descripcion' => "Creemos en las piezas que envejecen bien, en los materiales que ganan valor con el uso y en las decisiones visuales que no dependen de una tendencia momentanea.\n\nElegimos una comunicacion clara, una experiencia de marca serena y un catalogo que prioriza la calidad por sobre el exceso.\n\nPreferimos lo esencial, lo legible y lo bien resuelto. Valoramos el trabajo hecho con criterio, la colaboracion con sentido y la posibilidad de construir una identidad que se reconozca en cada detalle.",
            ],
            'faqs' => [
                'bajada' => 'Una primera guia para responder dudas habituales sobre la marca, los productos y la compra.',
                'descripcion' => "Que tipo de productos ofrece Hilando? Trabajamos con un catalogo curado de piezas textiles, objetos y recursos vinculados al habitar, al oficio y a la cultura material.\n\nComo se actualiza este contenido? Cada bloque de esta pagina puede vincularse a posts especificos para editarlo desde administracion, manteniendo la estructura del front intacta.\n\nPuedo sumar nuevas secciones en el futuro? Si. Esta pagina queda preparada para crecer y reorganizarse sin perder la logica editorial general.",
            ],
        ];

        foreach ($templates as $slug => $template) {
            $post = DB::table('blog_posts')
                ->where('blog_category_id', $categoryId)
                ->where('slug', $slug)
                ->first();

            if (!$post) {
                continue;
            }

            $updates = [];

            $currentBajada = trim((string) ($post->bajada ?? ''));
            $currentDescripcion = trim((string) ($post->descripcion ?? ''));

            if ($currentBajada === '') {
                $updates['bajada'] = $template['bajada'];
            }

            if (mb_strlen($currentDescripcion) < 80) {
                $updates['descripcion'] = $template['descripcion'];
            }

            if (!empty($updates)) {
                $updates['updated_at'] = now();

                DB::table('blog_posts')
                    ->where('id', $post->id)
                    ->update($updates);
            }
        }
    }

    public function down(): void
    {
    }
};
