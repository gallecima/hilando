<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with('user'); // puede venir null si la acción fue de un customer

        // Fechas sobre occurred_at (más semántico que created_at)
        if ($from = $request->input('from')) {
            $query->whereDate('occurred_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        if ($q = trim($request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('description', 'like', "%{$q}%")
                  ->orWhere('type', 'like', "%{$q}%")
                  // búsqueda directa en JSON meta (MySQL 5.7+/8.0)
                  ->orWhereRaw("JSON_EXTRACT(meta, '$.actor.email') LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("JSON_EXTRACT(meta, '$.actor.name') LIKE ?", ["%{$q}%"]);
            });
        }

        // Filtro por "user" debe contemplar:
        // - User clásico (relación users)
        // - Customer guardado en meta.actor (cuando user_id es null)
        if ($user = trim($request->input('user'))) {
            $query->where(function ($w) use ($user) {
                // Coincidencia con User (relación)
                $w->whereHas('user', function ($qU) use ($user) {
                    $qU->where('name', 'like', "%{$user}%")
                       ->orWhere('email', 'like', "%{$user}%");
                })
                // O coincidencia con actor en meta (cuando fue un customer u otro actor)
                ->orWhereRaw("JSON_EXTRACT(meta, '$.actor.email') LIKE ?", ["%{$user}%"])
                ->orWhereRaw("JSON_EXTRACT(meta, '$.actor.name') LIKE ?", ["%{$user}%"]);
            });
        }

        // Orden lógico por la fecha del evento
        $activities = $query
            ->orderByDesc('occurred_at')
            ->paginate(20)
            ->withQueryString();

        $filters = $request->only(['from', 'to', 'q', 'category', 'user']);

        return view('admin.logs.index', compact('activities', 'filters'));
    }
}