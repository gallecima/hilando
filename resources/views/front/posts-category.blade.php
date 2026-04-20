@extends('layouts.front')

@section('title', $categoria->nombre)

@section('content')
  @include('front.partials.page-header', [
    'title' => $categoria->nombre,
    'subtitle' => 'Artículos y novedades de esta categoría.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => $categoria->nombre],
    ],
  ])

  <section class="pb-5">
    <div class="container">
      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
        @foreach($posts as $post)
          <div class="col">
            <a href="{{ route('post.show', ['slug' => $post->slug]) }}" class="card h-100 text-decoration-none text-dark shadow-sm">
              @if($post->imagen_destacada)
                <img src="{{ asset('storage/' . $post->imagen_destacada) }}" class="card-img-top" alt="{{ $post->titulo }}">
              @endif
              <div class="card-body">
                <p class="text-body-secondary small mb-2">{{ \Illuminate\Support\Str::limit($post->bajada, 100) }}</p>
                <h2 class="h5 mb-0">{{ $post->titulo }}</h2>
              </div>
            </a>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-center mt-4">
        {{ $posts->links('front.partials.pagination-bootstrap') }}
      </div>
    </div>
  </section>
@endsection
