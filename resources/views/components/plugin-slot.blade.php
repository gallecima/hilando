@props(['position'])

@php
  $list = $activePluginsByPosition[$position] ?? [];
@endphp

@foreach($list as $plugin)
  {{-- Si el plugin trae su propia vista 'hook.blade.php' en su carpeta,
       y registramos el namespace con el slug, podemos hacer: --}}
  @includeIf($plugin->slug.'::hook', ['plugin' => $plugin])

  {{-- Si el plugin no trae vista, caemos a un fallback genérico --}}
  @if (! view()->exists($plugin->slug.'::hook'))
    <div class="alert alert-info my-2">
      {{ data_get($plugin->config, 'message', 'Plugin activo: '.$plugin->name) }}
    </div>
  @endif
@endforeach