<?php

use App\Models\Plugin;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

Route::middleware(['web', 'auth'])
    ->prefix('admin/plugins/smtp')
    ->name('admin.plugins.smtp.')
    ->group(function () {

        // === Settings ===
        Route::get('/', function () {
            $plugin = Plugin::where('slug','smtp')->firstOrFail();
            return view('smtp::settings', compact('plugin'));
        })->name('edit');

        // === Guardar configuración ===
        Route::post('/', function (Request $request) {
            $plugin = Plugin::where('slug','smtp')->firstOrFail();

            $data = $request->validate([
                'host'              => 'nullable|string',
                'port'              => 'nullable|integer',
                'encryption'        => 'nullable|in:tls,ssl', // tls = STARTTLS (587), ssl = SMTPS (465)
                'username'          => 'nullable|string',
                'password'          => 'nullable|string',
                'from_email'        => 'nullable|email',
                'from_name'         => 'nullable|string',
                'reply_to'          => 'nullable|email',
                'allow_self_signed' => 'nullable|boolean',
                'skip_host_verify'  => 'nullable|boolean',
                'timeout'           => 'nullable|integer|min:1',
            ]);

            // Normalizar checkboxes
            $data['allow_self_signed'] = $request->boolean('allow_self_signed');
            $data['skip_host_verify']  = $request->boolean('skip_host_verify');

            $plugin->update(['config' => array_merge($plugin->config ?? [], $data)]);

            return back()->with('success', 'Configuración guardada.');
        })->name('update');

        // === Envío de prueba con PHPMailer ===
        Route::post('/test', function (Request $request) {
            $request->validate(['to' => 'required|email']);

            $p   = Plugin::where('slug','smtp')->first();
            $cfg = $p?->config ?? [];

            // --- Normalización de config ---
            $rawHost = (string)($cfg['host'] ?? '');
            $host    = preg_replace('#^\s*(ssl://|tls://|smtp://)#i', '', $rawHost);
            $host    = preg_replace('#:\d+$#', '', $host);
            $port    = (int)($cfg['port'] ?? 0);
            $enc     = $cfg['encryption'] ?? null;        // 'ssl' | 'tls' | null
            $user    = (string)($cfg['username'] ?? '');
            $pass    = (string)($cfg['password'] ?? '');

            $fromEmail = $cfg['from_email'] ?? ($user ?: 'no-reply@localhost');
            $fromName  = $cfg['from_name']  ?? config('app.name');
            $replyTo   = $cfg['reply_to']   ?? null;

            $allowSelf = (bool)($cfg['allow_self_signed'] ?? false);
            $skipHostV = (bool)($cfg['skip_host_verify']  ?? false);
            $timeout   = (int)($cfg['timeout'] ?? 20);

            // Helper para enviar usando PHPMailer con la config indicada
            $sendWithPhpMailer = function(array $opts) use ($request, $fromEmail, $fromName, $replyTo, $user, $pass, $allowSelf, $skipHostV, $timeout) {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $opts['host'];
                $mail->Port       = $opts['port'];
                $mail->SMTPAuth   = !empty($user) || !empty($pass);
                $mail->Username   = $user;
                $mail->Password   = $pass;
                $mail->Timeout    = $timeout;
                $mail->SMTPDebug  = 0; // subir a 2 si querés más detalle en desarrollo

                // Cifrado
                // - ssl  => PHPMailer::ENCRYPTION_SMTPS (implicit TLS / 465)
                // - tls  => PHPMailer::ENCRYPTION_STARTTLS (STARTTLS / 587)
                if ($opts['enc'] === 'ssl' || $opts['port'] == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($opts['enc'] === 'tls' || $opts['port'] == 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPSecure = false; // sin cifrado
                }

                // SSL options (permitir self-signed / omitir verificación de nombre)
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => !$allowSelf,
                        'verify_peer_name'  => !$skipHostV,
                        'allow_self_signed' =>  $allowSelf,
                        // Tip: si sabés el host exacto del cert: 'peer_name' => $opts['host'],
                    ],
                ];

                // Remitente / Reply-To
                $mail->setFrom($fromEmail, $fromName);
                if (!empty($replyTo)) $mail->addReplyTo($replyTo);

                // Destinatario y contenido
                $mail->addAddress($request->to);
                $mail->Subject = 'Prueba SMTP (PHPMailer)';
                $mail->Body    = "Email de prueba enviado vía PHPMailer.\nHost: {$opts['host']}\nPort: {$opts['port']}\nEnc: ".($opts['enc'] ?: 'none');

                $mail->send();
            };

            // INTENTO #1: usar exactamente lo que configuró el usuario
            try {
                $tryPort = $port ?: (($enc === 'ssl') ? 465 : 587);
                $opts = ['host' => $host, 'port' => $tryPort, 'enc' => $enc];
                Log::debug('[SMTP Plugin][PHPMailer] Intento #1', $opts + [
                    'allow_self_signed' => $allowSelf,
                    'skip_host_verify'  => $skipHostV,
                ]);
                $sendWithPhpMailer($opts);

                return back()->with('success', 'Correo de prueba enviado correctamente con PHPMailer (configuración original).');

            } catch (PHPMailerException $e1) {
                Log::error('[SMTP Plugin][PHPMailer] Error intento #1', [
                    'msg' => $e1->getMessage(),
                    'host' => $host, 'port' => $port, 'enc' => $enc,
                    'allow_self_signed' => $allowSelf, 'skip_host_verify' => $skipHostV,
                ]);

                // ¿Aplicamos fallback automático? si era SSL:465 o hay errores típicos de cert/STARTTLS
                $shouldFallback =
                    ($enc === 'ssl' && ($port == 465 || $port == 0)) ||
                    str_contains($e1->getMessage(), 'certificate verify failed') ||
                    str_contains($e1->getMessage(), 'STARTTLS') ||
                    str_contains($e1->getMessage(), 'stream_socket_enable_crypto') ||
                    str_contains($e1->getMessage(), 'Connection could not be established');

                if (! $shouldFallback) {
                    $hint = ' Revisá host, puerto, cifrado (SSL 465 o TLS 587), usuario/clave.';
                    if (str_contains($e1->getMessage(), 'certificate verify failed')) {
                        $hint = $allowSelf
                          ? ' Tenés self-signed habilitado, pero el cert no coincide con el host o falta la cadena intermedia.'
                          : ' El certificado no pudo verificarse (podés habilitar self-signed o usar el host exacto del cert).';
                    }
                    return back()->with('error', 'No se pudo enviar el correo de prueba (PHPMailer).' . $hint);
                }

                // INTENTO #2 (fallback): TLS 587
                try {
                    $opts = ['host' => $host, 'port' => 587, 'enc' => 'tls'];
                    Log::debug('[SMTP Plugin][PHPMailer] Intento #2 (fallback TLS 587)', $opts + [
                        'allow_self_signed' => $allowSelf,
                        'skip_host_verify'  => $skipHostV,
                    ]);
                    $sendWithPhpMailer($opts);

                    return back()->with('success', 'Correo de prueba enviado correctamente con PHPMailer (fallback TLS 587).');

                } catch (PHPMailerException $e2) {
                    Log::error('[SMTP Plugin][PHPMailer] Error intento #2', [
                        'msg' => $e2->getMessage(),
                        'host' => $host, 'port' => 587, 'enc' => 'tls',
                        'allow_self_signed' => $allowSelf, 'skip_host_verify' => $skipHostV,
                    ]);

                    $hint = ' Revisá host, puerto, cifrado (SSL 465 o TLS 587), usuario/clave.';
                    $all  = $e1->getMessage().' | '.$e2->getMessage();
                    if (str_contains($all, 'certificate verify failed')) {
                        $hint = $allowSelf
                          ? ' Tenés self-signed habilitado, pero el servidor sigue rechazando el handshake (CN/SAN no coincide o cadena incompleta). Usá exactamente el host del certificado o corregí la cadena.'
                          : ' El certificado del servidor no pudo verificarse. Activá “self-signed” o usá el host exacto del certificado.';
                    }
                    return back()->with('error', 'No se pudo enviar el correo de prueba (PHPMailer).' . $hint);
                } catch (\Throwable $e2u) {
                    Log::error('[SMTP Plugin][PHPMailer] Error inesperado intento #2', ['msg' => $e2u->getMessage()]);
                    return back()->with('error', 'Ocurrió un error inesperado al enviar (PHPMailer fallback).');
                }

            } catch (\Throwable $e) {
                Log::error('[SMTP Plugin][PHPMailer] Error inesperado', ['msg' => $e->getMessage()]);
                return back()->with('error', 'Ocurrió un error inesperado al enviar (PHPMailer).');
            }
        })->name('test');

    });