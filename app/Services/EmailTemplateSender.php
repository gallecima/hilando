<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\SMTP\Services\PluginMailer;

class EmailTemplateSender
{
  /**
   * @param array<array{data?:string,path?:string,name?:string,mime?:string}> $attachments
   */
  public function send(string $key, Order $order, array $extra = [], ?string $to = null, array $attachments = []): bool
  {
    $tpl = \App\Models\EmailTemplate::where('key', $key)->first();
    if (!$tpl || !$tpl->enabled) return false;

    $to = (string) Str::of($to ?: ($order->email ?? ''))->trim();
    if ($to === '') return false;

    /** @var \App\Services\EmailTemplateRenderer $renderer */
    $renderer = app(\App\Services\EmailTemplateRenderer::class);
    $subject  = $renderer->renderSubject((string) $tpl->subject, $order, $extra);
    $html     = $renderer->renderBodyHtml((string) $tpl->body_html, $order, $extra);

    $postPurchaseBlock = $extra['%post_purchase_block%'] ?? $extra['post_purchase_block'] ?? null;
    $accessBlock = $extra['%acceso_plataforma%'] ?? $extra['acceso_plataforma'] ?? $postPurchaseBlock;

    if (is_string($accessBlock) && trim($accessBlock) !== '') {
      $tplBody = (string) $tpl->body_html;
      $resolvedBlock = trim($accessBlock);

      if (str_contains($tplBody, '%acceso_plataforma%')) {
        $html = str_replace('%acceso_plataforma%', $resolvedBlock, $html);
      } elseif (str_contains($tplBody, '%post_purchase_block%')) {
        $html = str_replace('%post_purchase_block%', $resolvedBlock, $html);
      } else {
        $html = $this->injectPostPurchaseBlock($html, $resolvedBlock);
      }
    }

    $transport = 'plugin_smtp';
    $ok = false; $err = null;

    /*
    $attachments = $attachments ?? [];
    $site = SiteInfo::query()->first();
    if ($site && $site->logo_path) {
        $logoDisk = config('filesystems.default', 'public');
        if (Storage::disk($logoDisk)->exists($site->logo_path)) {
            // Adjuntamos inline como cid:company_logo
            $attachments[] = [
                'path'   => Storage::disk($logoDisk)->path($site->logo_path),
                'name'   => basename($site->logo_path),
                'mime'   => 'image/'.(str_ends_with(strtolower($site->logo_path), '.png') ? 'png' : 'jpeg'),
                'inline' => true,
                'cid'    => 'company_logo',
            ];
        }
    }
    <img src="cid:company_logo" alt="%company_name%" height="40" style="display:block;">
    */


    try {
      // Envío unificado por plugin SMTP (PHPMailer).
      app(PluginMailer::class)->send($to, $subject, $html, $attachments);
      $ok = true;
    } catch (\Throwable $e) {
      $ok  = false;
      $err = $e->getMessage();
      \Log::error('[EmailTemplateSender] '.$key.' error: '.$err);
    } finally {
      try {
        if (Schema::hasTable('email_logs')) {
          EmailLog::create([
            'key'       => $key,
            'order_id'  => $order->id ?? null,
            'to'        => $to,
            'subject'   => $subject,
            'transport' => $transport,
            'ok'        => $ok,
            'error'     => $err,
            'context'   => $extra,
          ]);
        }
      } catch (\Throwable $e) {
        \Log::warning('[EmailTemplateSender] No se pudo registrar email_log: '.$e->getMessage(), [
          'key' => $key,
          'order_id' => $order->id ?? null,
          'to' => $to,
        ]);
      }
    }

    return $ok;
  }

  private function injectPostPurchaseBlock(string $html, string $block): string
  {
    $block = trim($block);
    if ($block === '') {
      return $html;
    }

    // Insertar dentro del documento para que no quede fuera de </html>.
    $withBody = preg_replace('/<\/body>/i', $block . '</body>', $html, 1, $countBody);
    if (is_string($withBody) && ($countBody ?? 0) > 0) {
      return $withBody;
    }

    $withHtml = preg_replace('/<\/html>/i', $block . '</html>', $html, 1, $countHtml);
    if (is_string($withHtml) && ($countHtml ?? 0) > 0) {
      return $withHtml;
    }

    return $html . $block;
  }
}
