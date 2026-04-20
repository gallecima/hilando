<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Activity;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // Sólo para el backend (ajustá el prefijo si usás otro)
        if (request()->is('admin*')) {
            view()->share('activitiesByCat', $this->recentActivitiesByCategory());
        }
    }

    protected function recentActivitiesByCategory(): array
    {
        return [
            'plataforma'      => Activity::where('category', 'plataforma')->latest('occurred_at')->limit(5)->get(),
            'administrativos' => Activity::where('category', 'administrativos')->latest('occurred_at')->limit(5)->get(),
            'comerciales'     => Activity::where('category', 'comerciales')->latest('occurred_at')->limit(5)->get(),
        ];
    }
}
