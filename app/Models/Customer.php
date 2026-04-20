<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Plugins\SMTP\Services\PluginMailer;

class Customer extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document',
        'is_active',
        'is_wholesaler',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_wholesaler' => 'boolean',
    ];

    public function address()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    public function billingData()
    {
        return $this->hasOne(CustomerBillingData::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $resetUrl = route('customer.password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        $html = view('front.emails.customer-reset-password', [
            'customer' => $this,
            'resetUrl' => $resetUrl,
        ])->render();

        app(PluginMailer::class)->send(
            (string) $this->email,
            'Restablecé tu contraseña',
            $html,
            []
        );
    }

    public function isWholesaler(): bool
    {
        return (bool) $this->is_wholesaler;
    }
}
