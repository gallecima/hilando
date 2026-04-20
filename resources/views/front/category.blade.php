@extends('layouts.front')

@section('title', $category->name)
@section('body_class', 'page-hero category-page')

@section('content')
  @php
    $isSimpleListing = in_array(($category->slug ?? null), ['todas', 'gratuitos'], true);
    $listingProducts = $uncategorizedProducts ?? ($products ?? collect());
    $breadcrumbs = [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => $category->name],
    ];
    $categoryImage = data_get($category, 'image');
    $heroBackgroundImage = filled($categoryImage)
      ? asset('storage/' . ltrim((string) $categoryImage, '/'))
      : null;
  @endphp

  @include('front.partials.page-header', [
    'variant' => 'hero',
    // 'eyebrow' => 'Categoría',
    'title' => $category->name,
    // 'subtitle' => $isSimpleListing ? 'Explorá el catálogo completo en una grilla simple y directa.' : 'Listado de productos disponibles en esta categoría.',
    'breadcrumbs' => $breadcrumbs,
    'backgroundImage' => $heroBackgroundImage,
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
      @if(request()->has('attrs'))
        @php $attrs = request('attrs', []); @endphp
        <div class="mb-4">
          @foreach($attrs as $attrSlug => $csv)
            @foreach(array_filter(array_map('trim', explode(',', $csv))) as $valueSlug)
              <a
                class="btn btn-sm btn-outline-secondary me-2 mb-2"
                href="{{ url()->current() . '?' . http_build_query([
                  'attrs' => \Illuminate\Support\Arr::except($attrs, [$attrSlug]) + [$attrSlug => implode(',', array_values(array_diff(explode(',', $csv), [$valueSlug])))],
                ]) }}"
              >
                {{ strtoupper($attrSlug) }}: {{ $valueSlug }} x
              </a>
            @endforeach
          @endforeach
          <a class="btn btn-sm btn-link" href="{{ route('category.show', ['slug' => $category->slug]) }}">Limpiar filtros</a>
        </div>
      @endif

      <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
        @forelse($isSimpleListing ? $listingProducts : $products as $product)
          <div class="col">
            @include('front.partials.product-card', ['product' => $product, 'showCategories' => true])
          </div>
        @empty
          <div class="col-12">
            <div class="alert alert-info mb-0">
              {{ ($category->slug ?? null) === 'gratuitos' ? 'No hay recursos gratuitos para mostrar.' : 'No se encontraron productos para esta categoría.' }}
            </div>
          </div>
        @endforelse
      </div>

      @if(method_exists($isSimpleListing ? $listingProducts : $products, 'links'))
        <div class="d-flex justify-content-center mt-4">
          {{ ($isSimpleListing ? $listingProducts : $products)->onEachSide(1)->links('front.partials.pagination-bootstrap') }}
        </div>
      @endif
      </div>
    </div>
  </section>
@endsection
