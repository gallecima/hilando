@extends('layouts.front')

@section('title', $post->titulo)

@section('content')
@php
  $linkedProducts = $linkedProducts ?? collect();
  $linkedCount = $linkedProducts->count();
  $singleLinkedProduct = $linkedCount === 1 ? $linkedProducts->first() : null;
@endphp

@include('front.partials.page-header', [
  'title' => $post->titulo,
  'subtitle' => $post->bajada,
  'breadcrumbs' => [
    ['label' => 'Inicio', 'url' => route('home')],
    $post->categoria->slug != 'textos-fijos'
      ? ['label' => $post->categoria->nombre, 'url' => route('post.category', $post->categoria->slug)]
      : ['label' => 'Contenido'],
    ['label' => $post->titulo],
  ],
])

<section class="pb-5">
  <div class="container">
    <div class="row g-4">
      @if($post->categoria->slug != 'textos-fijos')
        <div class="{{ $singleLinkedProduct ? 'col-lg-4' : 'col-lg-5' }}">
          @if($singleLinkedProduct)
            @include('front.partials.product-card', ['product' => $singleLinkedProduct, 'showCategories' => true])
          @elseif($post->imagen_destacada)
            <div class="card shadow-sm">
              <img src="{{ asset('storage/' . $post->imagen_destacada) }}" class="card-img-top" alt="{{ $post->titulo }}">
            </div>
          @endif
        </div>
      @endif

      <div class="{{ $post->categoria->slug != 'textos-fijos' ? ($singleLinkedProduct ? 'col-lg-8' : 'col-lg-7') : 'col-12' }}">
        <div class="card shadow-sm">
          <div class="card-body">
            @if($post->bajada)
              <p class="lead">{{ $post->bajada }}</p>
            @endif
            <div>{!! nl2br(e($post->descripcion)) !!}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@if($linkedCount > 1)
  <section class="pb-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">Productos relacionados</h2>
      </div>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
        @foreach($linkedProducts as $linkedProduct)
          <div class="col">
            @include('front.partials.product-card', ['product' => $linkedProduct, 'showCategories' => true])
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif

@if($post->categoria->slug != 'textos-fijos' && $otrosPosts->count())
  <section class="pb-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">También puede interesarte</h2>
        <a href="{{ route('post.category', $post->categoria->slug) }}" class="btn btn-outline-primary">Ver todos</a>
      </div>
      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
        @foreach($otrosPosts as $otro)
          <div class="col">
            <a href="{{ route('post.show', ['slug' => $otro->slug]) }}" class="card h-100 text-decoration-none text-dark shadow-sm">
              @if($otro->imagen_destacada)
                <img src="{{ asset('storage/' . $otro->imagen_destacada) }}" class="card-img-top" alt="{{ $otro->titulo }}">
              @endif
              <div class="card-body">
                <p class="text-body-secondary small mb-2">{{ \Illuminate\Support\Str::limit($otro->bajada, 80) }}</p>
                <h2 class="h5 mb-0">{{ $otro->titulo }}</h2>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif
@endsection
