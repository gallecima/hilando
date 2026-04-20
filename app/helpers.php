<?php

use App\Models\Slider;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\Hooks;
use Illuminate\Support\Facades\View;

if (!function_exists('slider')) {
    function slider($slug,$class,$orientacion)
    {
        $slider = Slider::where('slug', $slug)->with('images')->first();

        if (!$slider || $slider->images->isEmpty()) {
            return '';
        }

        return view('components.slider', compact('slider','class','orientacion'))->render();
    }
}


if (!function_exists('posts_by_category')) {
    function posts_by_category($categoriaSlug, $logo = "", $link = "", $view = 'components.posts-by-category', $limit = 3)
    {
        $categoria = BlogCategory::where('slug', $categoriaSlug)->first();

        $posts = [];

        if ($categoria) {
            $posts = BlogPost::where('blog_category_id', $categoria->id)
                        ->where('activo', true)
                        ->latest('fecha')
                        ->take($limit)
                        ->get();
        }

        return View::make($view, [
            'categoria' => $categoria,
            'logo' => $logo,
            'link' => $link,
            'posts' => $posts
        ])->render();
    }
}


if (! function_exists('add_hook')) {
  function add_hook(string $hook, callable $cb, int $priority = 10): void {
    app(Hooks::class)->add($hook, $cb, $priority);
  }
}


if (! function_exists('hook')) {
    function hook(string $pos): string {
        return app(Hooks::class)->render($pos);
    }
}


if (!function_exists('resaltarMitad')) {
    function resaltarMitad($texto)
    {
        $palabras = explode(' ', $texto);
        $totalPalabras = count($palabras);

        if ($totalPalabras < 2) {
            return "<span style=\"font-weight: 900\">$texto</span>";
        }

        $mitad = floor($totalPalabras / 2);

        $parte1 = implode(' ', array_slice($palabras, 0, $mitad));
        $parte2 = implode(' ', array_slice($palabras, $mitad));

        return "$parte1 <span style=\"font-weight: 900\">$parte2</span>";
    }
}
