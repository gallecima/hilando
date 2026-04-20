<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('name')->get();
        return view('admin.payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request)
    {
        if (is_string($request->input('config'))) {
            $request->merge(['config' => json_decode($request->input('config'), true) ?: []]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', 'string', Rule::in(PaymentMethod::TYPES)],
            'instructions' => 'nullable|string',
            'config' => 'nullable|array',
            'active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if (PaymentMethod::where('slug', $data['slug'])->exists()) {
            return back()->withErrors(['name' => 'Ya existe otro método con ese nombre.'])->withInput();
        }

        $data['config'] = $data['config'] ?? [];
        $data['active'] = $request->has('active');

        PaymentMethod::create($data);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Método de pago creado.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if (is_string($request->input('config'))) {
            $request->merge(['config' => json_decode($request->input('config'), true) ?: []]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', 'string', Rule::in(PaymentMethod::TYPES)],
            'instructions' => 'nullable|string',
            'config' => 'nullable|array',
            'active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if (PaymentMethod::where('slug', $data['slug'])->where('id', '!=', $paymentMethod->id)->exists()) {
            return back()->withErrors(['name' => 'Ya existe otro método con ese nombre.'])->withInput();
        }

        $data['config'] = $data['config'] ?? [];
        $data['active'] = $request->has('active');

        $paymentMethod->update($data);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Método de pago actualizado.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('admin.payment-methods.index')->with('success', 'Método de pago eliminado.');
    }
}