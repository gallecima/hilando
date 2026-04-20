<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'alta',
            'description'  => "Se creó el usuario #{$user->id} ({$user->name})",
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'meta'         => [
                'email'  => $user->email,
                'perfil' => $user->perfil_id,
                'active' => (bool) $user->active,
            ],
        ]);
    }

    public function updated(User $user): void
    {
        $changes  = $user->getChanges();    // nuevos valores
        $original = $user->getOriginal();   // valores previos

        // Campos que no nos interesan loguear como diff
        unset($changes['updated_at']);
        // Campos sensibles: NO se loguean diffs
        unset($changes['password'], $changes['remember_token']);

        // Construimos un diff simple campo → {old,new}
        $diff = [];
        foreach ($changes as $field => $new) {
            $diff[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $new,
            ];
        }

        if (empty($diff)) {
            return;
        }

        // Si cambió 'active', tipificamos activación/desactivación; si no, modificación genérica
        $type = 'modificacion';
        if (array_key_exists('active', $changes)) {
            $type = ($changes['active']) ? 'activacion' : 'desactivacion';
        }

        activity()->log([
            'category'     => 'plataforma',
            'type'         => $type,
            'description'  => "Se actualizó el usuario #{$user->id} ({$user->name})",
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'meta'         => ['diff' => $diff],
        ]);
    }

    public function deleted(User $user): void
    {
        activity()->log([
            'category'     => 'plataforma',
            'type'         => 'baja',
            'description'  => "Se eliminó el usuario #{$user->id} ({$user->name})",
            'subject_type' => User::class,
            'subject_id'   => $user->id,
        ]);
    }
}
