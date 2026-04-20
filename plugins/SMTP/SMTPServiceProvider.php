<?php

namespace Plugins\SMTP;

use App\Models\Plugin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Plugins\SMTP\Services\PluginMailer;

class SMTPServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rutas/Vistas del panel del plugin
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'smtp');

        // Registrar el mailer del plugin (PHPMailer) como singleton
        $this->app->singleton(PluginMailer::class, function () {
            // PluginMailer ya lee la configuración activa desde DB al enviar.
            return new PluginMailer();
        });
    }

    public function boot(): void
    {
        // Si no hay tabla plugins, salir silenciosamente (evita errores en cache/queue/optimize)
        if (! Schema::hasTable('plugins')) return;

        // Si no está activo, no hacemos nada (igual el servicio quedó registrado)
        $active = (bool) Plugin::where('slug', 'smtp')->value('is_active');
        if (! $active) return;

        // Sólo un log informativo (no modificamos config('mail.*'))
        \Log::debug('[SMTP] Plugin SMTP activo. Los envíos pueden hacerse vía PluginMailer (PHPMailer).');
    }
}
/*
<?php

namespace Plugins\SMTP;

use App\Models\Plugin;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;


class SMTPServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rutas/Vistas del plugin
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'smtp');
    }

    public function boot(): void
    {
        // Si aún no hay tabla, salir silenciosamente (evita errores en cache:clear, etc.)
        if (! Schema::hasTable('plugins')) return;

        $row = Plugin::where('slug', 'smtp')->first();
        if (! $row || ! $row->is_active) return;

        $cfg = $row->config ?? [];

        // from:
        if (! empty($cfg['from_email'])) {
            Config::set('mail.from.address', $cfg['from_email']);
            Config::set('mail.from.name', $cfg['from_name'] ?? config('app.name'));
        }

        // mailer smtp (solo seteo lo que tengas configurado)
        Config::set('mail.mailers.smtp', array_filter([
            'transport'  => 'smtp',
            'host'       => $cfg['host'] ?? null,
            'port'       => isset($cfg['port']) ? (int) $cfg['port'] : null,
            'encryption' => $cfg['encryption'] ?? null, // null|tls|ssl
            'username'   => $cfg['username'] ?? null,
            'password'   => $cfg['password'] ?? null,
            'allow_self_signed'   => $cfg['allow_self_signed'] ?? false,
            'skip_host_verify'   => $cfg['skip_host_verify'] ?? false,
            'timeout'    => null,
            'auth_mode'  => null,
        ]));
    }
}
*/
