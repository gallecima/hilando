<?php

namespace App\Http\Controllers;

use App\Models\FacturacionElectronica;
use Illuminate\Http\Request;

class FacturacionElectronicaController extends Controller
{
    public function index()
    {
        $items = FacturacionElectronica::paginate(20);
        return view('facturacion_electronica.index', compact('items'));
    }

    public function create()
    {
        return view('facturacion_electronica.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social' => 'required|string',
            'domicilio' => 'required|string',
            'cuit' => 'required|string|unique:facturacion_electronica',
            'cert_crt' => 'nullable|string',
            'public_key' => 'nullable|string',
            'punto_venta' => 'required|integer',
        ]);

        FacturacionElectronica::create($data);

        return redirect()->route('admin.facturacion_electronica.index')->with('success', 'Datos de facturación creados.');
    }

    public function edit(FacturacionElectronica $facturacionElectronica)
    {
        return view('facturacion_electronica.edit', compact('facturacionElectronica'));
    }

    public function update(Request $request, FacturacionElectronica $facturacionElectronica)
    {
        $data = $request->validate([
            'razon_social' => 'required|string',
            'domicilio' => 'required|string',
            'cuit' => 'required|string|unique:facturacion_electronica,cuit,' . $facturacionElectronica->id,
            'cert_crt' => 'nullable|string',
            'public_key' => 'nullable|string',
            'punto_venta' => 'required|integer',
        ]);

        $facturacionElectronica->update($data);

        return redirect()->route('admin.facturacion_electronica.index')->with('success', 'Datos de facturación actualizados.');
    }

    public function destroy(FacturacionElectronica $facturacionElectronica)
    {
        $facturacionElectronica->delete();
        return redirect()->route('admin.facturacion_electronica.index')->with('success', 'Registro eliminado.');
    }
}