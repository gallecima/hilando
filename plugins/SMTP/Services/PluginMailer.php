<?php

namespace Plugins\SMTP\Services;

use App\Models\Plugin;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class PluginMailer
{
    /**
     * Envía un email vía PHPMailer usando la configuración del plugin SMTP.
     *
     * @param string|array $to  Destinatario(s). Acepta "a@b.com, c@d.com" o array de emails.
     * @param string       $subject
     * @param string       $html  Cuerpo HTML
     * @param array        $attachments  Adjuntos. Formatos aceptados:
     *   - ['path' => '/abs/o/rel/file.pdf', 'name' => 'Comprobante.pdf', 'mime' => 'application/pdf', 'inline' => false, 'cid' => 'opcional']
     *   - ['data' => <string binario>, 'name' => 'Comprobante.pdf', 'mime' => 'application/pdf', 'inline' => false, 'cid' => 'opcional']
     *   - '/ruta/al/archivo.ext' (string simple, compatibilidad hacia atrás)
     * @param string|null  $from
     * @param string|null  $fromName
     * @throws PHPMailerException|\RuntimeException
     */
    public function send(string|array $to, string $subject, string $html, array $attachments = [], ?string $from = null, ?string $fromName = null): void
    {
        $row = Plugin::where('slug', 'smtp')->first();
        if (!$row || !$row->is_active) {
            throw new \RuntimeException('SMTP plugin inactivo.');
        }

        $cfg = (array) ($row->config ?? []);

        // Host puede venir como ssl://host:465 o tls://host:587
        $rawHost    = (string) ($cfg['host'] ?? '');
        $host       = $rawHost;
        $port       = isset($cfg['port']) ? (int) $cfg['port'] : null;
        $encryption = $cfg['encryption'] ?? null; // 'ssl'|'tls'|null

        if (preg_match('~^(?:(ssl|tls)://)?([^:/]+)(?::(\d+))?$~i', $rawHost, $m)) {
            $scheme = strtolower($m[1] ?? '');
            $host   = $m[2];
            $hPort  = isset($m[3]) ? (int)$m[3] : null;

            if (!$encryption && $scheme) $encryption = $scheme;
            if (!$port && $hPort)        $port = $hPort;
        }
        if (!$port) {
            $port = ($encryption === 'ssl') ? 465 : 587;
        }

        // SMTPAuth opcional si no hay user/pass
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';
        $smtpAuth = ($username !== '' || $password !== '');

        // Opciones de certificado (para self-signed o mismatch)
        $allowSelf  = (bool)($cfg['allow_self_signed'] ?? false);
        $skipVerify = (bool)($cfg['skip_host_verify'] ?? false);

        // Timeouts y opciones varias
        $timeout = (int)($cfg['timeout'] ?? 20); // segundos

        // From / Reply-To
        $fromAddr = $from     ?? ($cfg['from_email'] ?? ('no-reply@' . (parse_url((string)config('app.url'), PHP_URL_HOST) ?: 'localhost')));
        $fromName = $fromName ?? ($cfg['from_name']  ?? (string)config('app.name', 'App'));

        $replyToAddr = (string)($cfg['reply_to_email'] ?? '');
        $replyToName = (string)($cfg['reply_to_name']  ?? '');

        // CC / BCC en config (opcionales). Aceptan string con comas o array.
        $cc  = $cfg['cc']  ?? [];
        $bcc = $cfg['bcc'] ?? [];

        // Normalizar destinatarios a array
        $toList = is_array($to) ? $to : array_filter(array_map('trim', explode(',', $to)));

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = $timeout; // socket timeout
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->Port       = $port;
            $mail->SMTPAuth   = $smtpAuth;
            $mail->Username   = $username;
            $mail->Password   = $password;

            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;     // SMTPS (465)
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // STARTTLS (587)
            } else {
                $mail->SMTPSecure = false;                           // sin cifrado (no recomendado)
            }

            if ($allowSelf || $skipVerify) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => !$skipVerify,
                        'verify_peer_name'  => !$skipVerify,
                        'allow_self_signed' => $allowSelf,
                    ],
                ];
            }

            // DKIM (si está configurado)
            if (!empty($cfg['dkim_domain']) && !empty($cfg['dkim_selector']) && !empty($cfg['dkim_private_key'])) {
                $mail->DKIM_domain     = $cfg['dkim_domain'];
                $mail->DKIM_selector   = $cfg['dkim_selector'];
                $mail->DKIM_private    = $cfg['dkim_private_key']; // puede ser path o contenido
                $mail->DKIM_passphrase = $cfg['dkim_passphrase'] ?? '';
                $mail->DKIM_identity   = $fromAddr;
            }

            // From / Reply-To
            $mail->setFrom($fromAddr, $fromName);
            if ($replyToAddr !== '') {
                $mail->addReplyTo($replyToAddr, $replyToName ?: $replyToAddr);
            }

            // To / CC / BCC
            foreach ($toList as $addr) {
                if ($addr !== '') $mail->addAddress($addr);
            }
            foreach ((array)(is_array($cc) ? $cc : explode(',', (string)$cc)) as $addr) {
                $addr = trim((string)$addr);
                if ($addr !== '') $mail->addCC($addr);
            }
            foreach ((array)(is_array($bcc) ? $bcc : explode(',', (string)$bcc)) as $addr) {
                $addr = trim((string)$addr);
                if ($addr !== '') $mail->addBCC($addr);
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);

            // Adjuntos
            foreach ($attachments as $att) {
                // Compatibilidad: si viene un string, lo tratamos como path
                if (is_string($att)) {
                    if ($att !== '') {
                        $mail->addAttachment($att);
                    }
                    continue;
                }

                if (!is_array($att)) {
                    continue; // formato no soportado
                }

                $name   = $att['name'] ?? 'attachment';
                $mime   = $att['mime'] ?? 'application/octet-stream';
                $inline = !empty($att['inline']); // true para contenido embebido
                $cid    = $att['cid'] ?? null;

                // data (string binario) o path
                if (!empty($att['data'])) {
                    $data = $att['data'];
                    if ($inline) {
                        // Embebido en el mail (para <img src="cid:...">)
                        $cid = $cid ?: ('cid_' . bin2hex(random_bytes(6)));
                        // addStringEmbeddedImage($string, $cid, $name='', $encoding='base64', $type='', $disposition='inline')
                        $mail->addStringEmbeddedImage($data, $cid, $name, PHPMailer::ENCODING_BASE64, $mime, 'inline');
                        /* Tip: en el HTML: <img src="cid:<?=$cid?>"> */
                    } else {
                        $mail->addStringAttachment($data, $name, PHPMailer::ENCODING_BASE64, $mime);
                    }
                } elseif (!empty($att['path'])) {
                    $path = $att['path'];
                    if ($inline) {
                        $cid = $cid ?: ('cid_' . bin2hex(random_bytes(6)));
                        // addEmbeddedImage($path, $cid, $name='', $encoding='base64', $type='', $disposition='inline')
                        $mail->addEmbeddedImage($path, $cid, $name, PHPMailer::ENCODING_BASE64, $mime, 'inline');
                    } else {
                        $mail->addAttachment($path, $name);
                    }
                }
            }

            $mail->send();
        } catch (PHPMailerException $e) {
            \Log::error('[SMTP PluginMailer] Error al enviar: '.$e->getMessage(), [
                'to'      => $toList,
                'host'    => $host,
                'port'    => $port,
                'secure'  => $encryption,
            ]);
            throw $e;
        }
    }
}