<?php

namespace App\Http\Controllers;

use App\Models\CustomerBillingData;
use Illuminate\Http\Request;

class CustomerBillingDataController extends Controller
{
    public function index()
    {
        $billingData = CustomerBillingData::with('customer')->paginate(20);
        return view('customer_billing_data.index', compact('billingData'));
    }

    public function create()
    {
        return view('customer_billing_data.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_name' => 'required|string',
            'document_number' => 'required|string',
            'tax_status' => 'required|in:Responsable Inscripto,Monotributista,Consumidor Final,Exento',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'is_default' => 'boolean',
        ]);

        CustomerBillingData::create($data);

        return redirect()->route('customer_billing_data.index')->with('success', 'Datos de facturación creados.');
    }

    public function edit(CustomerBillingData $customerBillingData)
    {
        return view('customer_billing_data.edit', compact('customerBillingData'));
    }

    public function update(Request $request, CustomerBillingData $customerBillingData)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'business_name' => 'required|string',
            'document_number' => 'required|string',
            'tax_status' => 'required|in:Responsable Inscripto,Monotributista,Consumidor Final,Exento',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'is_default' => 'boolean',
        ]);

        $customerBillingData->update($data);

        return redirect()->route('customer_billing_data.index')->with('success', 'Datos de facturación actualizados.');
    }

    public function destroy(CustomerBillingData $customerBillingData)
    {
        $customerBillingData->delete();
        return redirect()->route('customer_billing_data.index')->with('success', 'Datos de facturación eliminados.');
    }
}