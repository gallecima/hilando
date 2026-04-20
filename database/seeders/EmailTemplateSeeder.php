<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
  public function run(): void
  {
    $now = now();
    DB::table('email_templates')->upsert([
      [
        'key'=>'order_confirmed','name'=>'Confirmación de compra',
        'subject'=>'Tu pedido #%pedido_id% fue recibido',
        'body_html'=>"<p>Hola %nombre%,</p><p>Gracias por tu compra del %fecha%.</p>%items_table%<p><b>Total:</b> %total%</p><p>Seguimiento y detalle: <a href=\"%order_link%\">%order_link%</a></p>",
        'enabled'=>true,'options'=>json_encode(['to_customer'=>true]),
        'created_at'=>$now,'updated_at'=>$now
      ],
      [
        'key'=>'payment_status_updated','name'=>'Cambio de estado de pago',
        'subject'=>'Pago de tu pedido #%pedido_id%: %payment_status%',
        'body_html'=>"<p>Hola %nombre%,</p><p>Tu pago pasó a <b>%payment_status%</b>.</p><p>Pedido #%pedido_id% — %fecha%</p><p>Ver pedido: <a href=\"%order_link%\">%order_link%</a></p>",
        'enabled'=>true,'options'=>json_encode(['to_customer'=>true]),
        'created_at'=>$now,'updated_at'=>$now
      ],
      [
        'key'=>'shipment_status_updated','name'=>'Actualización de envío',
        'subject'=>'Envío de tu pedido #%pedido_id%: %shipment_status%',
        'body_html'=>"<p>Hola %nombre%,</p><p>Tu envío ahora está: <b>%shipment_status%</b>.</p><p>Tracking: %tracking_number% — <a href=\"%tracking_url%\">Rastrear</a></p>",
        'enabled'=>true,'options'=>json_encode(['to_customer'=>true]),
        'created_at'=>$now,'updated_at'=>$now
      ],
    ], ['key'], ['subject','body_html','enabled','options','updated_at']);
  }
}