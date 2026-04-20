<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    public function created(Customer $c): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'alta',
            'description'  => "Se creó el cliente #{$c->id} ({$c->name})",
            'subject_type' => Customer::class,
            'subject_id'   => $c->id,
        ]);
    }

    public function updated(Customer $c): void
    {
        $changes = $c->getChanges();
        unset($changes['updated_at']);
        $diff = [];
        foreach ($changes as $field => $new) {
            $diff[$field] = ['old' => $c->getOriginal($field), 'new' => $new];
        }

        if (!empty($diff)) {
            activity()->log([
                'category'     => 'plataforma',
                'type'         => 'modificacion',
                'description'  => "Se modificó el cliente #{$c->id} ({$c->name})",
                'subject_type' => Customer::class,
                'subject_id'   => $c->id,
                'meta'         => ['diff' => $diff],
            ]);
        }
    }

    public function deleted(Customer $c): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'baja',
            'description'  => "Se eliminó el cliente #{$c->id} ({$c->name})",
            'subject_type' => Customer::class,
            'subject_id'   => $c->id,
        ]);
    }
}
