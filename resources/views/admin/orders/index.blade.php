@extends('layouts.backend')

@section('actions')
    {{-- Si quisieras un botón para crear pedidos manualmente --}}
    {{-- <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-alt-primary">Crear Pedido</a> --}}
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Pedidos</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Gestión de pedidos del sistema</h2>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

    @if(session('success'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            One.helpers('jq-notify', {
                type: 'success',
                icon: 'fa fa-check-circle me-1',
                from: 'bottom',
                message: @json(session('success'))
            });
        });
    </script>
    @endif

    <style>
    /* Fila child que inyectamos con DataTables */
    table.dataTable tbody tr.dt-progress-row > td {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }
    .dt-row-progress {
        height: 6px;
        background: #e9ecef;        /* pista */
        position: relative;
        overflow: hidden;
    }
    .dt-row-progress-fill {
        height: 100%;
        width: 0;
        transition: width .35s ease;
    }
    </style>

    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">

            <table class="table table-striped table-vcenter js-dataTable-full table-progress">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID / Fecha / Estado</th>
                        <th>Total / Pago</th>
                        <th>Cliente / Email / Ubicación</th>
                        <th>Envío</th>
                        <th>Items</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)

                    @php
                        $pay  = $order->payments->last()?->status;
                        $ship = $order->shipment?->status;

                        // Etapa del "progreso" del pedido
                        $stage = 0;
                        if (in_array($pay, ['completed','paid'])) {
                            $stage = 1;
                        }
                        if (in_array($ship, ['shipped', 'ready_for_pickup'])) { // listo para retirar o enviado
                            $stage = 2;
                        }
                        if ($ship === 'delivered') {
                            $stage = 3;
                        }

                        if ($order->status === 'cancelled') {
                            $pct = 0;
                            $barColor = '#d61f47';
                        } else {
                            $pct = [0 => 0, 1 => 33, 2 => 66, 3 => 100][$stage] ?? 0;
                            $barColor = match($stage) {
                                0 => '#6c757d',  // gris
                                1 => '#1391aa',  // azul (pagado)
                                2 => '#d4428b',  // rosa/info (enviado / listo para retirar)
                                3 => '#198754',  // verde (entregado)
                                default => '#6c757d',
                            };
                        }
                    @endphp
                    
                    <tr class="order-row" data-progress="{{ $pct }}" data-progress-color="{{ $barColor }}">
                        @php
                            $payment        = $order->payments->last();
                            $paymentStatus  = optional($payment)->getAttribute('status');
                            $paymentMethod  = optional($payment)->getAttribute('method');

                            $shipment       = $order->shipment;              // puede ser null
                            $shipmentMethod = $order->shipmentMethod;        // relación correcta
                            $methodName     = $shipmentMethod?->name;
                            $isPickup       = (bool)($shipmentMethod->is_pickup ?? false);

                            $carrier        = $shipment?->carrier;
                            $tracking       = $shipment?->tracking_number;
                            $status         = $shipment?->status;

                            // Traducciones ES
                            $orderStatusLabels = [
                                'pending'   => 'Pendiente',
                                'paid'      => 'Pagado',
                                'shipped'   => 'Enviado',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado',
                            ];
                            $paymentStatusLabels = [
                                'pending'   => 'Pendiente',
                                'completed' => 'Completado',
                                'failed'    => 'Fallido',
                                'paid'      => 'Pagado',     // por si acaso algún método usa "paid"
                            ];

                            $shipmentStatusLabels = [
                                'pending'          => 'Pendiente',
                                'ready_for_pickup' => 'Disponible para retirar',
                                'shipped'          => 'Enviado',
                                'delivered'        => 'Entregado',
                            ];

                            $orderStatusClass = match($order->status) {
                                'pending'   => 'bg-danger text-white',
                                'paid'      => 'bg-info text-white',
                                'shipped'   => 'bg-primary text-white',
                                'delivered' => 'bg-success text-white',
                                'cancelled' => 'bg-danger text-white',
                                default     => 'bg-secondary text-white',
                            };

                            $paymentStatusClass = match($paymentStatus) {
                                'pending', 'failed' => 'bg-danger text-white',
                                'completed', 'paid' => 'bg-success text-white',
                                default             => 'bg-secondary text-white',
                            };

                            $shipmentStatusClass = match($order->shipment?->status) {
                                'pending'          => 'bg-danger text-white',
                                'ready_for_pickup' => 'bg-info text-white',
                                'shipped'          => 'bg-info text-white',
                                'delivered'        => 'bg-success text-white',
                                default            => 'bg-secondary text-white',
                            };                            
                        @endphp

                        <td>{{ $order->id }}</td>
                        <td>
                            <strong>#{{ $order->id }}</strong><br>
                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small><br>
                            <span class="badge {{ $orderStatusClass }}">
                                {{ $orderStatusLabels[$order->status] ?? ucfirst($order->status) }}
                            </span>
                            {!! app(\App\Support\Hooks::class)->render('admin:orders:billing.badges', $order) !!}
                        </td>
                        <td>
                            <strong>${{ number_format($order->total, 2) }}</strong><br>
                            <small>{{ $paymentMethod }}</small><br>
                            @if($paymentStatus)
                                <span class="badge {{ $paymentStatusClass }}">
                                    {{ $paymentStatusLabels[$paymentStatus] ?? ucfirst($paymentStatus) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $order->name ?? $order->customer?->name }}</strong><br>
                            <small>{{ $order->email ?? $order->customer?->email }}</small><br>

                            @php
                                // 1) Si la orden tiene cliente con address, usamos eso
                                $addrModel = $order->customer?->address;
                                // 2) Si no, caemos al shipping_address (array/json) guardado en la orden
                                $addr = $addrModel
                                    ? (object)[
                                        'address_line' => $addrModel->address_line,
                                        'city'         => $addrModel->city,
                                        'postal_code'  => $addrModel->postal_code,
                                        'province'     => $addrModel->province,
                                    ]
                                    : (object) ($order->shipping_address ?? []);
                            @endphp

                            @if($isPickup)
                                {{-- Si es pickup, la dirección es menos relevante: mostramos igual algo si lo hubiera --}}
                                @if(!empty($addr->address_line))
                                    <small>Pickup / Retiro en: {{ $addr->address_line }} - {{ $addr->city }}</small><br>
                                    <small>CP {{ $addr->postal_code }} - {{ $addr->province }}</small>
                                @else
                                    <small class="text-muted">Retiro en punto de pickup</small>
                                @endif
                            @else
                                @if(!empty($addr->address_line))
                                    <small>{{ $addr->address_line }} - {{ $addr->city }}</small><br>
                                    <small>CP {{ $addr->postal_code }} - {{ $addr->province }}</small>
                                @else
                                    <small class="text-muted">Sin dirección</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($methodName)
                                <strong>{{ $methodName }}</strong><br>
                                @if($isPickup)
                                    <small class="text-muted">Modo: Retiro en punto de pickup</small><br>
                                @endif
                            @else
                                <small class="text-muted">Sin método</small><br>
                            @endif

                            @if(!$isPickup)
                                @if($carrier || $tracking)
                                    <small>
                                        {{ $carrier ?? '—' }}
                                        @if($carrier && $tracking) / @endif
                                        {{ $tracking ?? '' }}
                                    </small><br>
                                @endif
                            @endif

                            @if($order->shipment)
                                <span class="badge {{ $shipmentStatusClass }}">
                                    {{ $shipmentStatusLabels[$order->shipment?->status] ?? 'Sin estado' }}
                                </span>
                            @else
                                <span class="badge bg-secondary text-white">Sin envío</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-inline gap-1">
                                @foreach($order->items as $item)
                                    @php
                                        $product = $item->product;
                                        $image = null;
                                        if ($item->attributeValue && $product) {
                                            $pivot = $product->attributeValues()
                                                        ->where('attribute_values.id', $item->attribute_value_id)
                                                        ->first();
                                            $image = $pivot?->pivot?->image;
                                        }
                                        if (!$image && $product?->featured_image) {
                                            $image = $product->featured_image;
                                        }
                                    @endphp

                                    @if($image)
                                        <div class="d-flex align-items-center img-thumbnail rounded p-2 text-center" style="width:60px; height:60px; background-color:#FFF">
                                            <img src="{{ asset('storage/' . $image) }}" class="w-100" alt="">
                                        </div>
                                    @else
                                        <div class="bg-body-secondary rounded p-2 text-center" style="width:60px; height:60px;">
                                            <i class="fa fa-box"></i>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Ver detalles">
                                <i class="fa fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Eliminar pedido?')">
                                    <i class="fa fa-times"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
  $(function () {
    var table = $('.js-dataTable-full').DataTable({
      order: [[0, 'desc']],
      pageLength: 10,
      dom: 'lrtip'
    });
    table.column(0).visible(false);

    $('#customSearch').on('keyup', function () {
      table.search(this.value).draw();
    });

    function injectBars() {
      table.rows({ page: 'current' }).every(function () {
        var tr   = $(this.node());
        var pct  = parseInt(tr.data('progress') || 0, 10);
        var col  = tr.data('progress-color') || '#1391aa';

        var html = [
          '<div class="dt-row-progress">',
            '<div class="dt-row-progress-fill" style="background:'+col+';width:'+pct+'%; height:3px"></div>',
          '</div>'
        ].join('');

        if (this.child.isShown()) {
          this.child(html, 'dt-progress-row');
        } else {
          this.child(html, 'dt-progress-row').show();
        }
      });
    }

    injectBars();
    table.on('draw', injectBars);
  });    
</script>
@endsection