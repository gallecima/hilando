<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\Locality;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function index()
    {
        // Ahora también cargamos la localidad asociada
        $addresses = CustomerAddress::with(['customer', 'locality'])->paginate(20);

        return view('customer_addresses.index', compact('addresses'));
    }

    public function create()
    {
        // Si querés mostrar un combo de localidades en el form:
        $localities = Locality::orderBy('name')->get();

        return view('customer_addresses.create', compact('localities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'title'        => 'nullable|string|max:255',
            'address_line' => 'required|string|max:255',
            'city'         => 'nullable|string|max:255',
            'province'     => 'nullable|string|max:255',
            'postal_code'  => 'nullable|string|max:50',
            'country'      => 'required|string|max:255',

            // 👉 Nuevo: localidad vinculada
            'locality_id'  => 'nullable|exists:localities,id',

            'is_default'   => 'boolean',
        ]);

        // Si el checkbox no viene, forzamos false
        $data['is_default'] = $request->boolean('is_default');

        CustomerAddress::create($data);

        return redirect()
            ->route('customer_addresses.index')
            ->with('success', 'Dirección creada correctamente.');
    }

    public function edit(CustomerAddress $customerAddress)
    {
        // Para el formulario de edición
        $localities = Locality::orderBy('name')->get();

        return view('customer_addresses.edit', [
            'customerAddress' => $customerAddress,
            'localities'      => $localities,
        ]);
    }

    public function update(Request $request, CustomerAddress $customerAddress)
    {
        $data = $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'title'        => 'nullable|string|max:255',
            'address_line' => 'required|string|max:255',
            'city'         => 'nullable|string|max:255',
            'province'     => 'nullable|string|max:255',
            'postal_code'  => 'nullable|string|max:50',
            'country'      => 'required|string|max:255',

            // 👉 Nuevo: localidad vinculada
            'locality_id'  => 'nullable|exists:localities,id',

            'is_default'   => 'boolean',
        ]);

        $data['is_default'] = $request->boolean('is_default');

        $customerAddress->update($data);

        return redirect()
            ->route('customer_addresses.index')
            ->with('success', 'Dirección actualizada.');
    }

    public function destroy(CustomerAddress $customerAddress)
    {
        $customerAddress->delete();

        return redirect()
            ->route('customer_addresses.index')
            ->with('success', 'Dirección eliminada.');
    }
}