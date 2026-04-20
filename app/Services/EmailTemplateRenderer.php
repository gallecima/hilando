<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SiteInfo;
use Illuminate\Support\Facades\Route;


class EmailTemplateRenderer
{
    /** Alias de compatibilidad: mismo comportamiento que renderSubject() */
    public function renderText(string $text, Order $order, array $extra = []): string
    {
        return $this->renderSubject($text, $order, $extra);
    }

    /** Render para SUBJECT (texto plano) */
    public function renderSubject(string $subject, Order $order, array $extra = []): string
    {
        return strtr($subject, $this->vars($order, $extra));
    }

    /** Render para BODY HTML (inyecta %items_table% si corresponde) */
    public function renderBodyHtml(string $html, Order $order, array $extra = []): string
    {
        $vars = $this->vars($order, $extra);

        if (str_contains($html, '%items_table%')) {
            $vars['%items_table%'] = $this->itemsTable($order);
        }

        return strtr($html, $vars);
    }

    /** ------------------ Internos ------------------ */

    protected function vars(Order $order, array $extra): array
    {
        $name   = $order->name ?: $order->customer?->name ?: 'Cliente';
        $email  = $order->email ?: $order->customer?->email ?: '';
        $pago   = optional($order->payments->last())->status;
        $ship   = $order->shipment;

        $track     = $ship?->tracking_number ?: '';
        $trackUrl  = data_get($ship?->shipping_data_json, 'enviacom.generate.parsed.trackUrl', '');
        $orderLink = route('admin.orders.show', $order);

        // ✅ Link PÚBLICO (por token)
        $publicLink = Route::has('orders.track')
            ? route('orders.track', ['token' => $order->public_token])
            : rtrim(config('app.url', ''), '/') . '/pedido/' . $order->public_token;

        // (Opcional) Link admin por si lo necesitás en alguna plantilla interna
        $adminLink  = Route::has('admin.orders.show')
            ? route('admin.orders.show', $order)
            : '';        

        $base = [
            '%pedido_id%'        => (string) $order->id,
            '%nombre%'           => $name,
            '%email%'            => $email,
            '%fecha%'            => $order->created_at?->format('d/m/Y H:i') ?? '',
            '%total%'            => number_format((float) $order->total, 2),
            '%payment_status%'   => $pago ? ucfirst($pago) : '-',
            '%shipment_status%'  => $ship?->status ? ucfirst($ship->status) : '-',
            '%tracking_number%'  => $track,
            '%tracking_url%'     => $trackUrl ?: '',
            '%order_link%'       => $publicLink,
            '%order_admin_link%' => $adminLink,
            '%tienda_nombre%'    => config('app.name', 'Tienda'),
            '%tienda_url%'       => rtrim(config('app.url', ''), '/'),
        ];

        $site = SiteInfo::query()->first();

        $base += [
            '%site_title%'      => (string)($site->site_title ?? ''),
            '%company_name%'     => (string)($site->company_name ?? ''),
            '%company_address%'  => (string)($site->company_address ?? ''),
            '%company_website%'  => (string)($site->company_website ?? ''),
            '%support_email%'    => (string)($site->support_email ?? ''),
        ];        

        // Merge de extras (%lo_que_sea%)
        foreach ($extra as $k => $v) {
            if (!str_starts_with($k, '%')) $k = "%{$k}%";
            $base[$k] = (string) $v;
        }

        return $base;
    }

    protected function itemsTable(Order $order): string
    {
        $rows = '';
        foreach ($order->items as $it) {
            $name  = $it->product?->name ?? 'Producto';
            $qty   = (int) $it->quantity;
            $price = number_format((float) $it->price, 2);
            $rows .= "<tr><td>{$name}</td><td style=\"text-align:right\">{$qty}</td><td style=\"text-align:right\">\${$price}</td></tr>";
        }

        $subtotal = number_format((float) $order->subtotal, 2);
        $discount = number_format((float) ($order->discount ?? 0), 2);
        $total    = number_format((float) $order->total, 2);

        return <<<HTML
<table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;border:1px solid #eee">
  <thead>
    <tr style="background:#f8f8f8">
      <th align="left">Producto</th>
      <th align="right">Cant.</th>
      <th align="right">Precio</th>
    </tr>
  </thead>
  <tbody>
    {$rows}
    <tr><td colspan="2" align="right"><b>Subtotal</b></td><td align="right">\${$subtotal}</td></tr>
    <tr><td colspan="2" align="right"><b>Descuento</b></td><td align="right">-\${$discount}</td></tr>
    <tr><td colspan="2" align="right"><b>Total</b></td><td align="right">\${$total}</td></tr>
  </tbody>
</table>
HTML;
    }
}
