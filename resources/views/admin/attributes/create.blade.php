@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Atributos
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Administración de atributos de productos
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Comercio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.attributes.index') }}">Atributos</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Nuevo
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Nuevo Atributo</h3>
        </div>
        <div class="block-content block-content-full">


              <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Define attributos para categorias. Los atributos tienen valores por defecto. Ej. Material: Acero, Aluminio, Plastico.
                    </p>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="col-lg-8 space-y-5">            

                    <form action="{{ route('admin.attributes.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="has_stock_price" name="has_stock_price" value="1" {{ old('has_stock_price') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_stock_price">Permite definir stock y precio por valor</label>
                        </div>
                                                
                        <div class="mb-3">
                            @php
                                $valueRows = collect(old('values', ['','']))
                                    ->map(fn ($value) => is_string($value) ? $value : '')
                                    ->filter(fn ($value, $index) => $index < 2 || trim($value) !== '');

                                if ($valueRows->isEmpty()) {
                                    $valueRows = collect(['']);
                                }
                            @endphp
                            <div id="attribute-values-builder">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label>Valores posibles</label>
                                    <button type="button" id="add-value" class="btn btn-sm btn-outline-primary">
                                        Agregar valor <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <div id="attribute-value-list" class="d-grid gap-2">
                                    @foreach($valueRows as $valueRow)
                                        <div class="attribute-value-row d-flex align-items-center gap-2">
                                            <input type="text" name="values[]" class="form-control" value="{{ $valueRow }}" placeholder="Ej: Cuero">
                                            <button type="button" class="btn btn-sm btn-alt-danger remove-value-row" aria-label="Quitar valor">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>                    
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-alt-primary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar
                            </button>
                        </div>
                    </form>

                </div>
              </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const addButton = document.getElementById('add-value');
  const list = document.getElementById('attribute-value-list');

  if (!addButton || !list) return;

  function buildRow(value = '') {
    const row = document.createElement('div');
    row.className = 'attribute-value-row d-flex align-items-center gap-2';
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'values[]';
    input.className = 'form-control';
    input.placeholder = 'Ej: Otro valor';
    input.value = value;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-alt-danger remove-value-row';
    button.setAttribute('aria-label', 'Quitar valor');
    button.innerHTML = '<i class="fa fa-minus"></i>';

    row.appendChild(input);
    row.appendChild(button);

    return row;
  }

  addButton.addEventListener('click', function () {
    list.appendChild(buildRow());
  });

  list.addEventListener('click', function (event) {
    const button = event.target.closest('.remove-value-row');
    if (!button) return;

    const row = button.closest('.attribute-value-row');
    if (!row) return;

    if (list.children.length === 1) {
      row.querySelector('input')?.focus();
      row.querySelector('input').value = '';
      return;
    }

    row.remove();
  });
});
</script>
@endsection
