<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'alta',
            'description'  => "Se creó el producto #{$product->id} ({$product->name})",
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
        ]);
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();       // nuevos valores
        $original = $product->getOriginal();     // valores previos

        // Armamos un diff cortito y útil (evitamos timestamps)
        unset($changes['updated_at']);
        $diff = [];
        foreach ($changes as $field => $new) {
            $diff[$field] = ['old' => $original[$field] ?? null, 'new' => $new];
        }

        if (!empty($diff)) {
            activity()->log([
                'category'     => 'plataforma',
                'type'         => 'modificacion',
                'description'  => "Se modificó el producto #{$product->id} ({$product->name})",
                'subject_type' => Product::class,
                'subject_id'   => $product->id,
                'meta'         => ['diff' => $diff],
            ]);
        }
    }

    public function deleted(Product $product): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'baja',
            'description'  => "Se eliminó el producto #{$product->id} ({$product->name})",
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
        ]);
    }
}
