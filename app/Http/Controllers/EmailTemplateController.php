<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Order;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateRenderer;
use Plugins\SMTP\Services\PluginMailer;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $rows = EmailTemplate::orderBy('key')->get();
        return view('admin.emails.index', compact('rows'));
    }

    public function edit(string $key)
    {
        $tpl = EmailTemplate::where('key', $key)->firstOrFail();
        return view('admin.emails.edit', compact('tpl'));
    }

    public function update(Request $req, string $key)
    {
        $tpl = EmailTemplate::where('key', $key)->firstOrFail();
        $tpl->update([
            'name'      => (string) $req->input('name', ''),
            'subject'   => (string) $req->input('subject', ''),
            'body_html' => (string) $req->input('body_html', ''),
            'enabled'   => (bool) $req->boolean('enabled'),
        ]);

        return back()->with('success', 'Plantilla actualizada.');
    }

    public function preview(string $key)
    {
        $tpl = EmailTemplate::where('key', $key)->firstOrFail();
        $order = Order::latest('id')->first();
        if (!$order) {
            return response('No hay pedidos para previsualizar', 400);
        }

        /** @var EmailTemplateRenderer $r */
        $r = app(EmailTemplateRenderer::class);
        return response($r->renderBodyHtml($tpl->body_html, $order));
    }

    public function testSend(Request $request, string $key)
    {
        $tpl = EmailTemplate::where('key', $key)->first();
        if (!$tpl) {
            return back()->with('error', 'Key desconocida.');
        }

        $order = Order::latest('id')->first();
        if (!$order) {
            return back()->with('error', 'No hay pedidos de prueba.');
        }

        // Destinatario (por defecto, el del último pedido)
        $to = (string) Str::of($request->input('to', $order->email))->trim();

        /** @var EmailTemplateRenderer $renderer */
        $renderer = app(EmailTemplateRenderer::class);

        // ✅ Ahora el subject también reemplaza variables
        $subject = $renderer->renderSubject((string) $tpl->subject, $order);
        $html    = $renderer->renderBodyHtml((string) $tpl->body_html, $order);

        try {
            // Envío de prueba por plugin SMTP (PHPMailer).
            app(PluginMailer::class)->send($to, $subject, $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo enviar el email de prueba.');
        }

        return back()->with('success', "Enviado a {$to}");
    }
}
