@php
  $wrapperClass = trim((string) ($wrapperClass ?? ''));
@endphp

@if(session('success') || session('error'))
  <div @class([$wrapperClass => $wrapperClass !== ''])>
    @if(session('success'))
      <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div @class([
        'alert alert-danger',
        'mb-0' => !session('success'),
        'mt-3 mb-0' => session('success'),
      ])>{{ session('error') }}</div>
    @endif
  </div>
@endif
