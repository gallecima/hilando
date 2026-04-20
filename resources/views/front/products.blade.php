@extends('layouts.front')

@section('title', 'Productos')

@section('content')
  @include('front.partials.page-header', [
    'title' => 'Productos',
    'subtitle' => 'Listado completo del catálogo.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos'],
    ],
  ])

  <section class="pb-5">
    <div class="container">
      <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
        @forelse($products as $product)
          <div class="col">
            @include('front.partials.product-card', ['product' => $product, 'showCategories' => true])
          </div>
        @empty
          <div class="col-12">
            <div class="alert alert-info mb-0">No hay productos para mostrar.</div>
          </div>
        @endforelse
      </div>

      <div class="d-flex justify-content-center mt-4">
        {{ $products->onEachSide(1)->links('front.partials.pagination-bootstrap') }}
      </div>
    </div>
  </section>
@endsection
