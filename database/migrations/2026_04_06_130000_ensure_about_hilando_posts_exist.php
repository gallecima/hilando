<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blog_categories') || !Schema::hasTable('blog_posts') || !Schema::hasTable('users')) {
            return;
        }

        $userId = DB::table('users')->orderBy('id')->value('id');
        if (!$userId) {
            return;
        }

        $now = now();

        $categoryId = DB::table('blog_categories')->where('slug', 'sobre-hilando')->value('id');

        if (!$categoryId) {
            $categoryId = DB::table('blog_categories')->insertGetId([
                'nombre' => 'Sobre Hilando',
                'slug' => 'sobre-hilando',
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $posts = [
            [
                'titulo' => 'Quienes somos',
                'slug' => 'quienes-somos',
                'bajada' => 'Una marca que pone en valor el oficio, la materia y las historias que atraviesan cada pieza.',
                'descripcion' => "Hilando es un proyecto que cruza diseno, produccion y cultura material para construir una tienda con identidad propia.\n\nReunimos productos, relatos y procesos que nacen del trabajo consciente, de la seleccion de materiales y de una mirada contemporanea sobre lo artesanal.\n\nLa marca se apoya en una idea simple: cada objeto cotidiano puede ser tambien una forma de narrar de donde venimos, como producimos y que valores queremos sostener en el tiempo.",
            ],
            [
                'titulo' => 'Proposito',
                'slug' => 'propostio',
                'bajada' => 'Crear un puente entre el hacer, el habitar y una forma mas sensible de elegir lo que nos rodea.',
                'descripcion' => "Nuestro proposito es acercar piezas con caracter, con una presencia sobria y una historia clara detras.\n\nQueremos que el catalogo no sea solo un listado de productos, sino un espacio para descubrir texturas, tecnicas, decisiones de produccion y formas de uso.\n\nTrabajamos para que la experiencia de compra sea tan cuidada como el objeto final: simple, honesta y alineada con una estetica calida, atemporal y cercana.",
            ],
            [
                'titulo' => 'Manifesto',
                'slug' => 'manifesto',
                'bajada' => 'Una serie de ideas que ordenan nuestra forma de disenar, producir y compartir.',
                'descripcion' => "Creemos en las piezas que envejecen bien, en los materiales que ganan valor con el uso y en las decisiones visuales que no dependen de una tendencia momentanea.\n\nElegimos una comunicacion clara, una experiencia de marca serena y un catalogo que prioriza la calidad por sobre el exceso.\n\nPreferimos lo esencial, lo legible y lo bien resuelto. Valoramos el trabajo hecho con criterio, la colaboracion con sentido y la posibilidad de construir una identidad que se reconozca en cada detalle.",
            ],
            [
                'titulo' => 'FAQs',
                'slug' => 'faqs',
                'bajada' => 'Una primera guia para responder dudas habituales sobre la marca, los productos y la compra.',
                'descripcion' => "Que tipo de productos ofrece Hilando? Trabajamos con un catalogo curado de piezas textiles, objetos y recursos vinculados al habitar, al oficio y a la cultura material.\n\nComo se actualiza este contenido? Cada bloque de esta pagina puede vincularse a posts especificos para editarlo desde administracion, manteniendo la estructura del front intacta.\n\nPuedo sumar nuevas secciones en el futuro? Si. Esta pagina queda preparada para crecer y reorganizarse sin perder la logica editorial general.",
            ],
        ];

        foreach ($posts as $post) {
            $exists = DB::table('blog_posts')->where('slug', $post['slug'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('blog_posts')->insert([
                'titulo' => $post['titulo'],
                'slug' => $post['slug'],
                'bajada' => $post['bajada'],
                'descripcion' => $post['descripcion'],
                'fecha' => $now->toDateString(),
                'user_id' => $userId,
                'blog_category_id' => $categoryId,
                'imagen_destacada' => null,
                'activo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('blog_posts') || !Schema::hasTable('blog_categories')) {
            return;
        }

        DB::table('blog_posts')
            ->whereIn('slug', ['quienes-somos', 'propostio', 'manifesto', 'faqs'])
            ->delete();

        $categoryId = DB::table('blog_categories')->where('slug', 'sobre-hilando')->value('id');
        if (!$categoryId) {
            return;
        }

        $remainingPosts = DB::table('blog_posts')->where('blog_category_id', $categoryId)->count();
        if ($remainingPosts === 0) {
            DB::table('blog_categories')->where('id', $categoryId)->delete();
        }
    }
};
