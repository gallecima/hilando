@extends('layouts.front')

@section('title', 'Resultados de búsqueda')
@section('body_class', 'page-hero search-page')

@section('content')
  @include('front.partials.page-header', [
    'variant' => 'hero',
    // 'title' => 'Resultados de búsqueda',
    'subtitle' => $q !== '' ? 'Mostrando resultados para "' . $q . '".' : 'Ingresá un término de búsqueda para encontrar productos.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Resultados'],
    ],
    'slides' => $searchHeroSlides ?? collect(),
    'backgroundImage' => $searchHeroBackgroundImage ?? null,
    'heroId' => 'searchResultsHeroCarousel',
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
      @if(request()->has('attrs'))
        @php $attrs = request('attrs', []); @endphp
        <div class="mb-4">
          @foreach($attrs as $attrSlug => $csv)
            @foreach(array_filter(array_map('trim', explode(',', $csv))) as $valueSlug)
              @php
                $query = request()->query();
                $list = array_values(array_diff(array_filter(array_map('trim', explode(',', $csv))), [$valueSlug]));
                if (count($list)) {
                  $query['attrs'][$attrSlug] = implode(',', $list);
                } else {
                  unset($query['attrs'][$attrSlug]);
                  if (empty($query['attrs'])) {
                    unset($query['attrs']);
                  }
                }
                $removeUrl = url()->current() . (count($query) ? ('?' . http_build_query($query)) : '');
              @endphp
              <a class="btn btn-sm btn-outline-secondary me-2 mb-2" href="{{ $removeUrl }}">
                {{ strtoupper($attrSlug) }}: {{ $valueSlug }} x
              </a>
            @endforeach
          @endforeach

          @php
            $clearUrl = url()->current() . (request()->filled('q') ? ('?' . http_build_query(['q' => request('q')])) : '');
          @endphp
          <a class="btn btn-sm btn-link" href="{{ $clearUrl }}">Limpiar filtros</a>
        </div>
      @endif

      @if(method_exists($products, 'total'))
        <p class="text-body-secondary">{{ $products->total() }} producto{{ $products->total() === 1 ? '' : 's' }}</p>
      @endif

      @if($products->isEmpty())
        <div class="alert alert-info">No se encontraron productos.</div>
      @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
          @foreach($products as $product)
            <div class="col">
              @include('front.partials.product-card', ['product' => $product, 'showCategories' => true])
            </div>
          @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
          {{ $products->appends(request()->query())->links('front.partials.pagination-bootstrap') }}
        </div>
      @endif
      </div>
    </div>
  </section>
@endsection
