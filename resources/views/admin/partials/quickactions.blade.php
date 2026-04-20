{{-- === Recent Activity (máximo 5 por categoría) === --}}
@php
  // Fallback si no vino del controller
  if (!isset($activitiesByCat)) {
    $activitiesByCat = [
      'plataforma'      => \App\Models\Activity::where('category', 'plataforma')->latest('occurred_at')->limit(5)->get(),
      'administrativos' => \App\Models\Activity::where('category', 'administrativos')->latest('occurred_at')->limit(5)->get(),
      'comerciales'     => \App\Models\Activity::where('category', 'comerciales')->latest('occurred_at')->limit(5)->get(),
    ];
  }

  // Iconos/colores por categoría
  $catUi = [
    'plataforma'      => ['icon' => 'fa-cogs',          'class' => 'text-muted'],
    'administrativos' => ['icon' => 'fa-briefcase',     'class' => 'text-info'],
    'comerciales'     => ['icon' => 'fa-shopping-cart', 'class' => 'text-success'],
  ];
@endphp


    @foreach (['comerciales','administrativos','plataforma'] as $cat)
      @php
        $items = $activitiesByCat[$cat] ?? collect();
        $icon  = $catUi[$cat]['icon']  ?? 'fa-clock';
        $klass = $catUi[$cat]['class'] ?? 'text-muted';
      @endphp

      <div class="block block-transparent">
        <div class="block-header block-header-default">
          <h3 class="block-title"> <i class="fa fa-fw {{ $icon }}"></i> {{ $cat }}</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"></button>
          </div>
        </div>

        <div class="block-content p-0">


            <ul class="nav-items">
              @forelse ($items as $a)
                <li>
                  <a class="text-dark d-flex py-2 p-1" href="javascript:void(0)">
                    <div class="flex-grow-1 fs-sm">
                      <div class="fw-semibold">
                        {{ \Illuminate\Support\Str::limit($a->description, 80) }}
                      </div>
                      <div class="text-muted">
                        {{-- type opcional + usuario si existe --}}
                        <span class="me-2">{{ $a->type ?? 'evento' }}</span>
                        @if($a->user) <span>• {{ $a->user->name }}</span> @endif
                      </div>
                      <small class="text-muted">
                        {{ optional($a->occurred_at)->diffForHumans() ?? $a->created_at->diffForHumans() }}
                      </small>
                    </div>
                  </a>
                </li>
              @empty
                <li class="px-3 py-2 text-muted fs-sm">Sin actividad</li>
              @endforelse
            </ul>

        </div>
      </div>      

    @endforeach

{{-- === /Recent Activity === --}}
