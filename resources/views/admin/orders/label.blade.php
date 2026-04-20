<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Etiqueta #{{ $order->id }}</title>

<style>
  /* Etiqueta: 105 x 148.5 mm (A6-ish pero exacto) */
  @page { size: 105mm 148.5mm; margin: 0; }

  html, body {
    margin: 0; padding: 0;
    background: #fff; color: #2b2b2b;
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    font-size: 11px; line-height: 1.25;
  }

  /* Área segura: si tu impresora recorta, subí a 5mm */
  .sheet {
    width: 105mm;
    height: 148.5mm;
    padding: 4mm;              /* área segura */
    box-sizing: border-box;
    overflow: hidden;
  }

  /* Contenedor interno opcional (si querés borde solo para debug) */
  .label {
    width: 100%;
    height: 100%;
    /* border: 1px dashed #cbd5d9; */  /* solo debug */
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 3mm;
  }

  .header {
    display: grid;
    grid-template-columns: 22mm 1fr;
    column-gap: 4mm;
    align-items: center;
  }

  .logo {
    width: 22mm;
    height: 22mm;
    border-radius: 3mm;
    display:flex; align-items:center; justify-content:center;
    overflow: hidden;
  }

  .brand {
    font-weight: 800;
    font-size: 15px;
    color:#333;
    line-height: 1.1;
  }

  .priority {
    background: #111;
    color: #fff;
    font-weight: 800;
    border-radius: 2mm;
    text-align: center;
    padding: 1.2mm 2mm;
    font-size: 10px;
    margin-top: 2mm;
    display: inline-block;
    width: fit-content;
  }

  .fromto {
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 3mm;
  }

  .box {
    border: 1px solid #9fb3b7;
    border-radius: 2mm;
    padding: 2.5mm;
    min-height: 34mm;
    box-sizing: border-box;
  }

  .box h4 {
    margin:0 0 1.2mm 0;
    font-size: 9px;
    color:#5a757a;
    letter-spacing: .2px;
  }

  .box p {
    margin:0 0 1.2mm 0;
    font-size: 10px;  /* un poquito más grande */
    word-break: break-word;
  }

  .iconsRow {
    display:flex;
    gap: 2mm;
    flex-wrap: wrap;
    align-items: center;
  }

  .icon {
    width: 10mm; height: 10mm;
    border:1px solid #9fb3b7;
    border-radius: 2mm;
    display:flex; align-items:center; justify-content:center;
    font-size: 10px;
  }

  .fragil {
    font-size: 9px;
    font-weight: 800;
    color: #444;
  }

  /* Área de código de barras / QR */
  .barcodeArea {
    margin-top: 1mm;
    display:flex;
    align-items:center;
    justify-content:center;
    height: 26mm;          /* más alto, aprovecha la etiqueta */
    border: 1px solid #9fb3b7;
    border-radius: 2mm;
    overflow: hidden;
  }

  .barcodeFallback {
    width: 100%;
    height: 100%;
    background: repeating-linear-gradient(90deg, #000 0 1mm, transparent 1mm 2mm);
  }

  .trackRow {
    margin-top: auto; /* empuja esta sección al fondo de la etiqueta */
    border-top: 1px solid #9fb3b7;
    padding-top: 2mm;
    font-size: 10px;
    color:#2b2b2b;
    display: flex;
    justify-content: space-between;
    gap: 3mm;
    align-items: baseline;
    word-break: break-word;
  }

  .trackStrong {
    font-weight: 800;
  }

  @media print {
    .actions { display:none !important; }
  }
</style>
</head>

<body>
@php
  $shipment       = $order->shipment;
  $shipmentMethod = $order->shipmentMethod;
  $isPickup       = (bool)($shipmentMethod->is_pickup ?? false);

  $addr       = $shipment?->address;
  $addrTo1    = is_array($addr) ? trim(($addr['address_line'] ?? '')) : (string) $addr;
  $addrTo2    = is_array($addr) ? trim(($addr['city'] ?? '').', '.($addr['province'] ?? '')) : '';
  $addrTo3    = is_array($addr) ? trim('CP '.($addr['postal_code'] ?? '').' — '.($addr['country'] ?? '')) : '';

  $pickupPointName = $shipmentMethod->name ?? 'Punto de retiro';
  $tracking = $shipment?->tracking_number ?: '—';
  $priorityLabel = $isPickup ? 'RETIRO' : 'ENVÍO';
@endphp

<div class="sheet">
  <div class="label">

    <div class="header">
      <div class="logo">
        <img src="{{ asset('images/logo.svg') }}" alt="Logo" style="width:100%; height:auto;">
      </div>
      <div>
        <div class="brand">{{ $site->company_name }}</div>
        <div class="priority">{{ $priorityLabel }}</div>
      </div>
    </div>

    <div class="fromto">
      <div class="box">
        @if($isPickup)
          <h4>PUNTO DE RETIRO</h4>
          <p><strong>{{ $pickupPointName }}</strong></p>
          <p>Cliente: {{ $order->name }}</p>
        @else
          <h4>PARA</h4>
          <p><strong>{{ $order->name }}</strong></p>
          <p>{{ $addrTo1 }}</p>
          <p>{{ $addrTo2 }}</p>
          <p>{{ $addrTo3 }}</p>
        @endif
      </div>

      <div class="box">
        <h4>DE</h4>
        <p><strong>{{ $site->company_name }}</strong></p>
        <p>{{ $site->company_address }}</p>
        <p>{{ $site->company_website }}</p>
        <p>{{ $site->support_email }}</p>
      </div>
    </div>

    <div class="iconsRow">
      <div class="icon">⚡</div>
      <div class="icon">⬆️</div>
      <div class="icon">☂️</div>
      <span class="fragil">FRÁGIL</span>
    </div>

    <div class="barcodeArea">
      <div class="barcodeFallback"></div>
    </div>

    <div class="trackRow">
      <div>
        @if($isPickup)
          Código retiro:
        @else
          Seguimiento:
        @endif
        <span class="trackStrong">{{ $tracking }}</span>
      </div>
      <div>#{{ $order->id }}</div>
    </div>

  </div>
</div>

</body>
</html>