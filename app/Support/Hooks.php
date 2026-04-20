<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class Hooks {
    protected array $slots = [];

    public function add(string $pos, callable|string $cb): void {
        $this->slots[$pos] ??= [];
        $this->slots[$pos][] = $cb;
        Log::debug('[Hooks] add', ['pos' => $pos, 'count' => count($this->slots[$pos])]);
    }

    public function render(string $pos, ...$args): string
    {
        $count = count($this->slots[$pos] ?? []);
        \Illuminate\Support\Facades\Log::debug('[Hooks] render', [
            'pos'   => $pos,
            'count' => $count,
            'args'  => count($args),
        ]);

        $out = '';

        foreach ($this->slots[$pos] ?? [] as $cb) {
            // Si es callable, lo intentamos con args; si falla por cantidad, reintentamos sin args.
            if (is_callable($cb)) {
                try {
                    $result = $cb(...$args);
                } catch (\ArgumentCountError|\TypeError $e) {
                    $result = $cb();
                }

                // Si el resultado es otro Closure (patrón "2 etapas"), también lo ejecutamos.
                if ($result instanceof \Closure) {
                    try {
                        $result = $result(...$args);
                    } catch (\ArgumentCountError|\TypeError $e) {
                        $result = $result();
                    }
                }

                // Normalizamos a string si corresponde.
                if (is_string($result)) {
                    $out .= $result;
                } elseif ($result instanceof \Stringable) {
                    $out .= (string) $result;
                } elseif ($result !== null && !is_bool($result)) {
                    // Por si algún hook devuelve arrays/objetos simples
                    $out .= (string) $result;
                }
            } else {
                // Si registraron un string o similar
                $out .= (string) $cb;
            }
        }

        return $out;
    }

}