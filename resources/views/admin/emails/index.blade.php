@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Emails</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Plantillas de email para comunicacion de eventos en la tienda</h2>
            </div>
        </div>
    </div>
</div>

<div class="content content-full">
  <div class="block block-rounded">
    <div class="block-header block-header-default">
      <h3 class="block-title">Emails — Plantillas</h3>
    </div>
    <div class="block-content block-content-full overflow-x-auto">
      <table class="table table-striped">
        <thead><tr><th>Key</th><th>Nombre</th><th>Asunto</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
          @foreach($rows as $t)
          <tr>
            <td><code>{{ $t->key }}</code></td>
            <td>{{ $t->name }}</td>
            <td>{{ $t->subject }}</td>
            <td><span class="badge {{ $t->enabled?'bg-success':'bg-secondary' }}">{{ $t->enabled?'Activa':'Inactiva' }}</span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-alt-primary" href="{{ route('admin.emails.edit', $t->key) }}">Editar</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="mt-3 small text-muted">
        Variables: %pedido_id%, %nombre%, %email%, %fecha%, %total%, %payment_status%, %shipment_status%, %tracking_number%, %tracking_url%, %order_link%, %items_table%
      </div>
    </div>
  </div>
</div>
@endsection