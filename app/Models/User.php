<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Plugins\SMTP\Services\PluginMailer;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo',
        'perfil_id',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        $html = view('admin.emails.password-reset', [
            'user' => $this,
            'resetUrl' => $resetUrl,
        ])->render();

        app(PluginMailer::class)->send(
            (string) $this->email,
            'Restablecé tu contraseña',
            $html,
            []
        );
    }

    public function sendEmailVerificationNotification(): void
    {
        if (!filled($this->email) || !Route::has('verification.verify')) {
            return;
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1((string) $this->email),
            ]
        );

        $html = view('admin.emails.verify-email', [
            'user' => $this,
            'verificationUrl' => $verificationUrl,
        ])->render();

        app(PluginMailer::class)->send(
            (string) $this->email,
            'Verificá tu correo electrónico',
            $html,
            []
        );
    }
}
