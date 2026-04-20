@php
    // selected: array<attrSlug => array<valueSlug>>
    $selected = collect(request('attrs', []))
        ->map(fn($csv)=>collect(explode(',', (string)$csv))->map(fn($s)=>trim($s))->filter()->values()->all())
        ->toArray();

    // Helper para saber si un value está tildado
    $isChecked = function($attrSlug, $valueSlug) use ($selected){
        return in_array($valueSlug, $selected[$attrSlug] ?? []);
    };

    // Construir URL con toggle de un valor sin perder el resto
    function toggleFacet($attrSlug, $valueSlug) {
        $query = request()->query();
        $attrs = $query['attrs'] ?? [];
        $current = isset($attrs[$attrSlug]) && $attrs[$attrSlug] !== ''
            ? explode(',', $attrs[$attrSlug])
            : [];

        $current = array_values(array_filter(array_map('trim', $current)));
        if (in_array($valueSlug, $current)) {
            // quitar
            $current = array_values(array_diff($current, [$valueSlug]));
        } else {
            // agregar
            $current[] = $valueSlug;
        }

        if (count($current) > 0) {
            $attrs[$attrSlug] = implode(',', $current);
        } else {
            unset($attrs[$attrSlug]);
        }

        $query['attrs'] = $attrs;
        return url()->current() . (count($query) ? ('?' . http_build_query($query)) : '');
    }
@endphp

@php
    // nombre de ruta configurable (por si usás 'front.category' o 'category.show')
    $categoryRoute = $categoryRoute ?? 'category.show';
    $currentSlug   = isset($category) ? ($category->slug ?? null) : null;
@endphp

<div class="card mb-4">
  <div class="card-header fw-bold">Categorías</div>
  <div class="list-group list-group-flush">

    {{-- Caso 1: hay categoría actual --}}
    @if(!empty($category))
      {{-- Link a la categoría actual (sin filtros) --}}

      @if ($currentSlug != "todas")        
        <a href="{{ route($categoryRoute, ['slug' => $category->slug]) }}"
          class="list-group-item list-group-item-action {{ request()->route('slug') === ($category->slug ?? '') ? 'active' : '' }}">
          {{ $category->name }}
        </a>
      @endif

      {{-- Subcategorías inmediatas --}}
      @foreach(($subcategories ?? collect()) as $sub)
        <a href="{{ route($categoryRoute, ['slug' => $sub->slug]) }}"
           class="list-group-item list-group-item-action ps-5">
          {{ $sub->name }}
        </a>
      @endforeach

      {{-- Hermanas (mismo nivel) si las estás pasando --}}
      @if(($siblings ?? collect())->count())
        <div class="list-group-item disabled fw-semibold">También en {{ optional($category->parent)->name ?? 'esta sección' }}</div>
        @foreach($siblings as $sib)
          <a href="{{ route($categoryRoute, ['slug' => $sib->slug]) }}"
             class="list-group-item list-group-item-action">
            {{ $sib->name }}
          </a>
        @endforeach
      @endif

    <a href="{{ route($categoryRoute, ['slug' => 'todas']) }}"
       class="list-group-item list-group-item-action {{ $currentSlug === 'todas' ? 'active' : '' }}">
      Todas las categorías
    </a>      

    {{-- Caso 2: no hay categoría (búsqueda global) --}}
    @else
      {{-- Si te llegan categorías raíz o sugeridas, listalas.
           Podés pasar $rootCategories desde el controlador de search. --}}
      @if(($rootCategories ?? collect())->count())
        @foreach($rootCategories as $root)
          <a href="{{ route($categoryRoute, ['slug' => $root->slug]) }}"
             class="list-group-item list-group-item-action">
            {{ $root->name }}
          </a>
        @endforeach
      @else
        <div class="list-group-item text-muted">Sin categoría seleccionada.</div>
      @endif
    @endif
  </div>
</div>



<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span class="fw-bold">Filtrar por</span>
    @if(request()->has('attrs'))
      @php
        // Construir una URL igual a la actual pero sin el parámetro "attrs"
        $clearQuery = request()->query();
        unset($clearQuery['attrs']);
        $clearUrl = url()->current() . (count($clearQuery) ? ('?' . http_build_query($clearQuery)) : '');
      @endphp
      <a href="{{ $clearUrl }}" class="small text-decoration-none">Limpiar filtros</a>
    @endif
  </div>


  <div class="card-body">
    @forelse($attributes as $attr)
      @if($attr->values->count())
        <div class="mb-3">
          <div class="fw-semibold mb-1">{{ $attr->name }}</div>
          <div class="d-flex flex-wrap gap-2">
            @foreach($attr->values as $val)
              @php
                $checked = $isChecked($attr->slug, $val->slug);
                $toggleUrl = toggleFacet($attr->slug, $val->slug);
              @endphp
              <a href="{{ $toggleUrl }}"
                 class="badge fw-normal text-wrap rounded-pill {{ $checked ? 'text-bg-primary' : 'text-bg-light border' }}"
                 title="{{ $val->value }}">
                 {{ $val->value }}
              </a>
            @endforeach
          </div>
        </div>
        <hr class="my-2">
      @endif
    @empty
      <p class="text-muted mb-0">No hay filtros disponibles para esta categoría.</p>
    @endforelse
  </div>
</div>
