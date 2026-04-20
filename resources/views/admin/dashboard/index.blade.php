@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
  <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
      <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">Dashboard</h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">Resumen de ventas, pedidos y clientes</h2>
      </div>
      <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 align-items-end mt-3 mt-sm-0">
        <div>
          <label class="form-label">Desde</label>
          <input type="date" name="from" class="form-control" value="{{ $from }}">
        </div>
        <div>
          <label class="form-label">Hasta</label>
          <input type="date" name="to" class="form-control" value="{{ $to }}">
        </div>
        <div class="pb-1">
          <button class="btn btn-primary"><i class="fa fa-filter me-1"></i> Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="content">
  {{-- KPIs --}}
  <div class="row items-push">
    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Ventas</div>
          <div class="fs-3 fw-semibold text-primary">${{ number_format($sales, 2) }}</div>
          <div class="fs-sm">en el rango</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.payments.index') }}">
            <span>Ver pagos</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>        
      </div>
    </div>

    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Pedidos</div>
          <div class="fs-3 fw-semibold">{{ $ordersCount }}</div>
          <div class="fs-sm">totales</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.orders.index') }}">
            <span>Ver pedidos</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>         
      </div>
    </div>

    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Ticket Promedio</div>
          <div class="fs-3 fw-semibold">${{ number_format($avgTicket, 2) }}</div>
          <div class="fs-sm">pagados</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.payments.index') }}">
            <span>Ver pagos</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>           
      </div>
    </div>

    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Clientes nuevos</div>
          <div class="fs-3 fw-semibold">{{ $newCustomers }}</div>
          <div class="fs-sm">en el rango</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.customers.index') }}">
            <span>Ver clientes</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>           
      </div>
    </div>

    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Pendientes de pago</div>
          <div class="fs-3 fw-semibold">{{ $pendingPaymentsCount }}</div>
          <div class="fs-sm">(${{ number_format($pendingPaymentsAmount, 2) }})</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.payments.index') }}">
            <span>Ver pendientes</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>         
      </div>
    </div>

    <div class="col-6 col-xl-2">
      <div class="block block-rounded text-center">
        <div class="block-content block-content-full">
          <div class="fs-sm text-muted">Pendientes de envío</div>
          <div class="fs-3 fw-semibold">{{ $pendingShipmentCount }}</div>
          <div class="fs-sm">órdenes</div>
        </div>
        <div class="bg-body-light rounded-bottom"> 
          <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="{{ route('admin.shipments.index') }}">
            <span>Ver envios</span>
            <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
          </a>
        </div>         
      </div>
    </div>
  </div>

  {{-- Gráficas --}}
  <div class="row mb-4" style="align-items: stretch;">
    <div class="col-xl-8">
      <div class="block block-rounded" style="height: 100%;">
        <div class="block-header block-header-default">
          <h3 class="block-title">Ventas por día</h3>
        </div>
        <div class="block-content block-content-full">
          <canvas id="chartSalesByDay" height="240"></canvas>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="block block-rounded" style="height: 100%;">
        <div class="block-header block-header-default">
          <h3 class="block-title">Estados de pedidos</h3>
        </div>
        <div class="block-content block-content-full">
          <canvas id="chartStatus" height="240"></canvas>
        </div>
      </div>
    </div>


  </div>

  <div class="row">
    <div class="col-xl-6">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Top 5 clientes (monto)</h3>
        </div>
        <div class="block-content block-content-full">
          <canvas id="chartTopCustomers" height="180"></canvas>
        </div>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Top 5 productos (unidades)</h3>
        </div>
        <div class="block-content block-content-full">
          <canvas id="chartTopProducts" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>



  
{{-- Últimos pedidos + Widgets --}}
<div class="row">
  {{-- Tabla (8 cols) --}}
  <div class="col-xl-8">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">Últimos pedidos</h3>
      </div>
      <div class="block-content block-content-full">
        {{-- (tu dropdown de filtros y la tabla quedan igual) --}}
        @php
        // Mapeos de estado -> etiqueta y clase
        $orderStatusLabels = [
            'pending'   => 'Pendiente',
            'paid'      => 'Pagado',
            'shipped'   => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];
        $orderStatusClass = fn($st) => match($st) {
            'pending'   => 'bg-warning text-white',
            'paid'      => 'bg-info text-white',
            'shipped'   => 'bg-primary text-white',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            default     => 'bg-secondary',
        };

        // Contadores (si ya traés $statusDist del controller, podés usarlo directo)
        $counts = [
            'pending'   => (int)($statusDist['pending']   ?? $lastOrders->where('status','pending')->count()),
            'paid'      => (int)($statusDist['paid']      ?? $lastOrders->where('status','paid')->count()),
            'shipped'   => (int)($statusDist['shipped']   ?? $lastOrders->where('status','shipped')->count()),
            'delivered' => (int)($statusDist['delivered'] ?? $lastOrders->where('status','delivered')->count()),
            'cancelled' => (int)($statusDist['cancelled'] ?? $lastOrders->where('status','cancelled')->count()),
        ];
        $counts['all'] = array_sum($counts);

        // Filtro seleccionado (desde la query string)
        $recentStatus = request('recent_status', 'all');

        // Colección filtrada para render (lado Blade; si preferís, podés filtrar en el controller)
        $rows = $recentStatus !== 'all'
            ? $lastOrders->where('status', $recentStatus)
            : $lastOrders;

        // Helper para armar URL manteniendo filtros existentes
        $statusUrl = fn($st) => route('admin.dashboard', array_merge(request()->query(), ['recent_status' => $st]));
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Últimos pedidos</div>
        <div class="dropdown d-inline-block">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="dropdown-recent-orders-filters"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-fw fa-flask"></i>
            Filtros
            <i class="fa fa-angle-down ms-1"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-md dropdown-menu-end fs-sm" aria-labelledby="dropdown-recent-orders-filters">
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='pending'?'active':'' }}"
                href="{{ $statusUrl('pending') }}">
                Pendientes
                <span class="badge bg-primary rounded-pill">{{ $counts['pending'] }}</span>
            </a>
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='paid'?'active':'' }}"
                href="{{ $statusUrl('paid') }}">
                Pagados
                <span class="badge bg-primary rounded-pill">{{ $counts['paid'] }}</span>
            </a>
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='shipped'?'active':'' }}"
                href="{{ $statusUrl('shipped') }}">
                Enviados
                <span class="badge bg-primary rounded-pill">{{ $counts['shipped'] }}</span>
            </a>
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='delivered'?'active':'' }}"
                href="{{ $statusUrl('delivered') }}">
                Entregados
                <span class="badge bg-primary rounded-pill">{{ $counts['delivered'] }}</span>
            </a>
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='cancelled'?'active':'' }}"
                href="{{ $statusUrl('cancelled') }}">
                Cancelados
                <span class="badge bg-primary rounded-pill">{{ $counts['cancelled'] }}</span>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between {{ $recentStatus==='all'?'active':'' }}"
                href="{{ $statusUrl('all') }}">
                Todos
                <span class="badge bg-primary rounded-pill">{{ $counts['all'] }}</span>
            </a>
            </div>
        </div>
        </div>

        <div class="table-responsive">
        <table class="table table-striped table-vcenter">
            <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th class="text-end">Total</th>
                <th>Estado</th>
                <th class="text-end"></th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $o)
                @php $cls = $orderStatusClass($o->status); @endphp
                <tr>
                <td>#{{ $o->id }}</td>
                <td>{{ $o->customer?->name ?? $o->name }}</td>
                <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-end">${{ number_format($o->total, 2) }}</td>
                <td><span class="badge {{ $cls }}">{{ $orderStatusLabels[$o->status] ?? ucfirst($o->status) }}</span></td>
                <td class="text-end">
                    <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-eye"></i>
                    </a>
                </td>
                </tr>
            @empty
                <tr>
                <td colspan="6" class="text-center text-muted py-4">No hay pedidos para el filtro seleccionado.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>


      </div>
    </div>
  </div>

  {{-- Widgets (4 cols) --}}
  <div class="col-xl-4">
    <div class="row">
      {{-- Ventas vs período anterior --}}
      @php
        $salesTrend = isset($salesPrev) && $salesPrev > 0 ? (($sales - $salesPrev) / $salesPrev) * 100 : null;
      @endphp
      <div class="col-12">
        <div class="block block-rounded bg-primary text-white">
          <div class="block-content block-content-full d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-sm text-white">Ventas (rango)</div>
              <div class="fs-3 fw-semibold">${{ number_format($sales, 2) }}</div>
              <div class="fs-sm">
                <span class="text-white">Previo:</span>
                ${{ number_format($salesPrev ?? 0, 2) }}
                @if(!is_null($salesTrend))
                  @if($salesTrend >= 0)
                    <span class="badge bg-success ms-1"><i class="fa fa-caret-up me-1"></i>{{ number_format($salesTrend,1) }}%</span>
                  @else
                    <span class="badge bg-danger ms-1"><i class="fa fa-caret-down me-1"></i>{{ number_format(abs($salesTrend),1) }}%</span>
                  @endif
                @endif
              </div>
            </div>
            <div class="ms-3" style="width:120px;height:48px">
              <canvas id="sparkSales" height="48"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Pedidos vs período anterior --}}
      @php
        $ordersTrend = isset($ordersPrev) && $ordersPrev > 0 ? (($ordersCount - $ordersPrev) / $ordersPrev) * 100 : null;
      @endphp
      <div class="col-12">
        <div class="block block-rounded bg-primary-op text-white">
          <div class="block-content block-content-full d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-sm text-white">Pedidos (rango)</div>
              <div class="fs-3 fw-semibold">{{ $ordersCount }}</div>
              <div class="fs-sm">
                <span class="text-white">Previo:</span>
                {{ $ordersPrev ?? 0 }}
                @if(!is_null($ordersTrend))
                  @if($ordersTrend >= 0)
                    <span class="badge bg-success ms-1"><i class="fa fa-caret-up me-1"></i>{{ number_format($ordersTrend,1) }}%</span>
                  @else
                    <span class="badge bg-danger ms-1"><i class="fa fa-caret-down me-1"></i>{{ number_format(abs($ordersTrend),1) }}%</span>
                  @endif
                @endif
              </div>
            </div>
            <div class="ms-3" style="width:120px;height:48px">
              <canvas id="sparkOrders" height="48"></canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- Ticket promedio vs período anterior --}}
      @php
        $avgPrev   = $avgTicketPrev ?? null;
        $avgTrend  = ($avgPrev && $avgPrev > 0) ? (($avgTicket - $avgPrev) / $avgPrev) * 100 : null;
      @endphp
      <div class="col-12">
        <div class="block block-rounded bg-primary-light text-white">
          <div class="block-content block-content-full d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-sm text-white">Ticket promedio</div>
              <div class="fs-3 fw-semibold">${{ number_format($avgTicket, 2) }}</div>
              <div class="fs-sm">
                <span class="text-white">Previo:</span>
                ${{ number_format($avgPrev ?? 0, 2) }}
                @if(!is_null($avgTrend))
                  @if($avgTrend >= 0)
                    <span class="badge bg-success ms-1"><i class="fa fa-caret-up me-1"></i>{{ number_format($avgTrend,1) }}%</span>
                  @else
                    <span class="badge bg-danger ms-1"><i class="fa fa-caret-down me-1"></i>{{ number_format(abs($avgTrend),1) }}%</span>
                  @endif
                @endif
              </div>
            </div>
            <div class="ms-3 opacity-0 d-none d-xl-block" style="width:120px;height:48px"></div>
          </div>
        </div>
      </div>

      {{-- Clientes nuevos vs período anterior --}}
      @php
        $ncPrev   = $newCustomersPrev ?? null;
        $ncTrend  = ($ncPrev && $ncPrev > 0) ? (($newCustomers - $ncPrev) / $ncPrev) * 100 : null;
      @endphp
      <div class="col-12">
        <div class="block block-rounded bg-primary-lighter">
          <div class="block-content block-content-full d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-sm text-muted">Clientes nuevos</div>
              <div class="fs-3 fw-semibold">{{ $newCustomers }}</div>
              <div class="fs-sm">
                <span class="text-muted">Previo:</span>
                {{ $ncPrev ?? 0 }}
                @if(!is_null($ncTrend))
                  @if($ncTrend >= 0)
                    <span class="badge bg-success ms-1"><i class="fa fa-caret-up me-1"></i>{{ number_format($ncTrend,1) }}%</span>
                  @else
                    <span class="badge bg-danger ms-1"><i class="fa fa-caret-down me-1"></i>{{ number_format(abs($ncTrend),1) }}%</span>
                  @endif
                @endif
              </div>
            </div>
            <div class="ms-3 opacity-0 d-none d-xl-block" style="width:120px;height:48px"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>


</div>
@endsection

@section('js')

<script>
  console.log("salesByDay", @json($salesByDay));
  console.log("topCustomers", @json($topCustomers));
  console.log("topProducts", @json($topProducts));
</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // === OneUI defaults ===
  Chart.defaults.color = "#818d96";
  Chart.defaults.scale.grid.lineWidth = 0;
  Chart.defaults.scale.beginAtZero = true;
  Chart.defaults.datasets.bar.maxBarThickness = 45;
  Chart.defaults.elements.bar.borderRadius = 4;
  Chart.defaults.elements.bar.borderSkipped = false;
  Chart.defaults.elements.point.radius = 0;
  Chart.defaults.elements.point.hoverRadius = 0;
  Chart.defaults.plugins.tooltip.radius = 3;
  Chart.defaults.plugins.legend.labels.boxWidth = 10;

// Paleta base + nuevos colores intensos
const COL = {
  gray700:    "rgba(100, 116, 139, 1)",
  gray700_70: "rgba(100, 116, 139, .7)",
  red500:     "rgba(220, 38, 38, 1)",
  green600:   "rgba(101, 163, 13, 1)",
  blue600:    "rgba(37, 99, 235, 1)",
  amber500:   "rgba(245, 158, 11, 1)",
  white:      "#fff",

  // Nuevos colores vibrantes basados en la imagen
  magenta:    "rgba(202, 85, 189, 1)",  // más saturado
  pink:       "rgba(223, 50, 115, 1)",
  purple:     "rgba(160, 77, 190, 1)",

  // Rellenos al 80% de opacidad para contraste
  magenta_80: "rgba(202, 85, 189, .8)",
  pink_80:    "rgba(223, 50, 115, .8)",
  purple_80:  "rgba(160, 77, 190, .8)",
  red500_80:  "rgba(220, 38, 38, .8)",
  green600_80:"rgba(101, 163, 13, .8)",
  blue600_80: "rgba(37, 99, 235, .8)",
  amber500_80:"rgba(245, 158, 11, .8)",
  gray700_80: "rgba(100, 116, 139, .8)",
};

// Pares fill/solid con colores más potentes
const PAIRS = [
  { fill: COL.red500_80,    solid: COL.red500 },
  { fill: COL.green600_80,  solid: COL.green600 },
  { fill: COL.blue600_80,   solid: COL.blue600 },
  { fill: COL.gray700_80,   solid: COL.gray700 },
  { fill: COL.amber500_80,  solid: COL.amber500 },
  { fill: COL.magenta_80,   solid: COL.magenta },
  { fill: COL.pink_80,      solid: COL.pink },
  { fill: COL.purple_80,    solid: COL.purple },
];

  // Helpers de color
  const rand = (n) => Math.floor(Math.random() * n);
  const pickPair = () => PAIRS[rand(PAIRS.length)];
  const shuffle = (arr) => arr.map(v=>[Math.random(),v]).sort((a,b)=>a[0]-b[0]).map(v=>v[1]);
  const pickManyPairs = (n) => {
    const base = shuffle(PAIRS.slice());
    const out  = [];
    while (out.length < n) out.push(base[out.length % base.length]);
    return out;
  };

  // ==== Datos PHP ====
  const salesByDay       = @json($salesByDay);
  const statusDist       = @json($statusDist);
  const statusLabelsMap  = @json($orderStatusLabels);
  const topCustomers     = @json($topCustomers);
  const topProducts      = @json($topProducts);
  const productNames     = @json($productNames);

  // === Ventas por día ===
  if (salesByDay?.length) {
    const labelsDays = salesByDay.map(r => {
      const d = new Date(String(r.date) + 'T00:00:00');
      return new Intl.DateTimeFormat('es-AR', { day: '2-digit', month: 'short' }).format(d);
    });
    const dataDays = salesByDay.map(r => Number(r.sum) || 0);

    const { fill, solid } = pickPair();

    new Chart(document.getElementById('chartSalesByDay'), {
      type: 'bar',
      data: {
        labels: labelsDays,
        datasets: [{
          label: 'Ventas',
          data: dataDays,
          backgroundColor: fill,
          borderColor: 'transparent',
          pointBackgroundColor: solid,
          pointBorderColor: COL.white,
          pointHoverBackgroundColor: COL.white,
          pointHoverBorderColor: solid
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              title: (items) => {
                const idx = items?.[0]?.dataIndex ?? 0;
                const d = new Date(String(salesByDay[idx]?.date) + 'T00:00:00');
                return new Intl.DateTimeFormat('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
              },
              label: (ctx) => ` $${ctx.parsed.y}`
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { autoSkip: true, maxTicksLimit: 14 }
          },
          y: { beginAtZero: true }
        }
      }
    });
  }

  // === Estados (doughnut) ===
  if (statusDist && Object.keys(statusDist).length) {
    const keys   = Object.keys(statusDist);
    const vals   = keys.map(k => statusDist[k]);
    const labels = keys.map(k => statusLabelsMap[k] ?? k);

    const pairs  = pickManyPairs(vals.length);

    new Chart(document.getElementById('chartStatus'), {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: vals,
          backgroundColor: pairs.map(p => p.fill),
          borderColor: 'transparent'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  // === Top clientes ===
  if (topCustomers?.length) {
    const labels = topCustomers.map(c => c.name);
    const data   = topCustomers.map(c => parseFloat(c.sum));
    const pairs  = pickManyPairs(data.length);

    new Chart(document.getElementById('chartTopCustomers'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Monto',
          data,
          backgroundColor: pairs.map(p => p.fill),
          borderColor: 'transparent'
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
      }
    });
  }

  // === Top productos ===
  if (topProducts?.length) {
    const labels = topProducts.map(p => productNames[p.product_id] ?? ('#' + p.product_id));
    const data   = topProducts.map(p => parseFloat(p.qty));
    const pairs  = pickManyPairs(data.length);

    new Chart(document.getElementById('chartTopProducts'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Unidades',
          data,
          backgroundColor: pairs.map(p => p.fill),
          borderColor: 'transparent'
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
      }
    });
  }
});
</script>
@endsection