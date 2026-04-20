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

<div class="content">
  <div class="row">
    <div class="col-xl-8">

        <div class="block block-rounded">
            <div class="block-header block-header-default d-flex justify-content-between">
                <h3 class="block-title">Editar plantilla: {{ $tpl->name }} <small class="text-muted">({{ $tpl->key }})</small></h3>
            </div>

            <div class="block-content block-content-full overflow-x-auto">

                <form method="POST" action="{{ route('admin.emails.update', $tpl->key) }}">
                    @csrf @method('PUT')

                    <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="enabled" name="enabled" {{ $tpl->enabled?'checked':'' }}>
                    <label class="form-check-label" for="enabled">Plantilla activa</label>
                    </div>

                    <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $tpl->name) }}">
                    </div>

                    <div class="mb-3">
                    <label class="form-label">Asunto</label>
                    <input type="text" class="form-control" name="subject" value="{{ old('subject', $tpl->subject) }}">
                    </div>

                    <div class="mb-3">
                    <label class="form-label">Cuerpo (HTML)</label>
                    <textarea class="form-control" name="body_html" rows="14">{{ old('body_html', $tpl->body_html) }}</textarea>
                    <div class="form-text">
                        Variables: %pedido_id%, %nombre%, %email%, %fecha%, %total%, %payment_status%, %shipment_status%, %tracking_number%, %tracking_url%, %order_link%, %items_table%
                    </div>
                    </div>

                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.emails.index') }}" class="btn btn-alt-primary me-2">
                                            Cancelar
                                        </a>          
                        <button class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-alt-secondary" id="btnPreview">Vista previa</button>
                    </div>
                </form>

            </div>

            <div class="block block-rounded d-none mt-3" id="previewBlock">
                <div class="block-header block-header-default"><h3 class="block-title">Vista previa</h3></div>
                <div class="block-content" id="previewContent"><div class="text-muted">Cargando…</div></div>
            </div>            
        </div>

    </div>
    <div class="col-xl-4">

    <div class="block block-rounded mb-5">
        <div class="block-header block-header-default"><h3 class="block-title">Vista previa</h3></div>
        <div class="block-content block-content-full overflow-x-auto">

                <form method="POST" action="{{ route('admin.emails.test', $tpl->key) }}" class="mb-3">
                    @csrf
                    <div class="mb-3">
                    <label class="form-label">Destinatario</label>
                    <input name="to" type="email" class="form-control" required>
                    </div>
                    <button class="btn btn-alt-primary w-100">
                        <i class="fa fa-paper-plane me-1"></i> Probar envío
                    </button>                    
                </form>

                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif                

        </div>
    </div>    


    </div>


</div>

<script>
document.getElementById('btnPreview')?.addEventListener('click', async () => {
  const block = document.getElementById('previewBlock');
  const content = document.getElementById('previewContent');
  block.classList.remove('d-none');
  content.innerHTML = '<div class="text-muted">Cargando…</div>';
  const res = await fetch(@json(route('admin.emails.preview', $tpl->key)), {
    method: 'POST', headers: {'X-CSRF-TOKEN': @json(csrf_token())}
  });
  content.innerHTML = await res.text();
});
</script>
@endsection