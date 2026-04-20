<?php

namespace App\Http\Controllers;

use App\Models\ShippingBox;
use App\Models\ShippingPoint;
use App\Models\ShipmentMethod;
use Illuminate\Http\Request;

class ShipmentMethodController extends Controller
{
    public function index()
    {
        $shipmentMethods = ShipmentMethod::with(['country', 'province', 'locality', 'shippingPoint', 'shippingBoxes'])->get();
        return view('admin.shipmentmethod.index', compact('shipmentMethods'));
    }

    public function create()
    {
        $shippingPoints = ShippingPoint::where('is_active', 1)->orderBy('name')->get();
        $shippingBoxes = ShippingBox::where('is_active', 1)->orderBy('priority')->orderBy('name')->get();

        return view('admin.shipmentmethod.create', compact('shippingPoints', 'shippingBoxes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'delay' => 'nullable|string|max:255',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'country_name' => 'nullable|string|max:120',
            'province_name' => 'nullable|string|max:120',
            'locality_name' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'shipping_point_id' => 'nullable|exists:shipping_points,id',
            'allow_nearby_match' => 'nullable|boolean',
            'nearby_radius_km' => 'nullable|numeric|min:0',
            'weight_limit' => 'nullable|numeric|min:0',
            'height_limit' => 'nullable|numeric|min:0',
            'width_limit' => 'nullable|numeric|min:0',
            'length_limit' => 'nullable|numeric|min:0',
            'shipping_boxes' => 'nullable|array',
            'shipping_boxes.*' => 'exists:shipping_boxes,id',
            'is_pickup'       => 'nullable|boolean',
            'is_active' => 'nullable|boolean', 
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_pickup'] = $request->boolean('is_pickup');
        $validated['allow_nearby_match'] = $request->boolean('allow_nearby_match');
        $validated['country_id'] = null;
        $validated['province_id'] = null;
        $validated['locality_id'] = null;

        foreach (['country_name', 'province_name', 'locality_name', 'postal_code'] as $field) {
            $value = trim((string) ($validated[$field] ?? ''));
            $validated[$field] = $value !== '' ? $value : null;
        }

        $shipmentMethod = ShipmentMethod::create($validated);
        $shipmentMethod->shippingBoxes()->sync($validated['shipping_boxes'] ?? []);

        return redirect()->route('admin.shipmentmethod.index')->with('success', 'Método de envío creado correctamente.');
    }

    public function edit(ShipmentMethod $shipmentmethod)
    {
        $shippingPoints = ShippingPoint::where('is_active', 1)->orderBy('name')->get();
        $shippingBoxes = ShippingBox::where('is_active', 1)->orderBy('priority')->orderBy('name')->get();

        return view('admin.shipmentmethod.edit', [
            'shipmentMethod' => $shipmentmethod,
            'shippingPoints' => $shippingPoints,
            'shippingBoxes' => $shippingBoxes,
        ]);
    }

    public function update(Request $request, ShipmentMethod $shipmentmethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'delay' => 'nullable|string|max:255',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_value' => 'nullable|numeric|min:0',            
            'country_name' => 'nullable|string|max:120',
            'province_name' => 'nullable|string|max:120',
            'locality_name' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'shipping_point_id' => 'nullable|exists:shipping_points,id',
            'allow_nearby_match' => 'nullable|boolean',
            'nearby_radius_km' => 'nullable|numeric|min:0',
            'weight_limit' => 'nullable|numeric|min:0',
            'height_limit' => 'nullable|numeric|min:0',
            'width_limit' => 'nullable|numeric|min:0',
            'length_limit' => 'nullable|numeric|min:0',
            'shipping_boxes' => 'nullable|array',
            'shipping_boxes.*' => 'exists:shipping_boxes,id',
            'is_pickup'       => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_pickup'] = $request->boolean('is_pickup');
        $validated['allow_nearby_match'] = $request->boolean('allow_nearby_match');
        $validated['country_id'] = null;
        $validated['province_id'] = null;
        $validated['locality_id'] = null;

        foreach (['country_name', 'province_name', 'locality_name', 'postal_code'] as $field) {
            $value = trim((string) ($validated[$field] ?? ''));
            $validated[$field] = $value !== '' ? $value : null;
        }
                
        $shipmentmethod->update($validated);
        $shipmentmethod->shippingBoxes()->sync($validated['shipping_boxes'] ?? []);

        return redirect()->route('admin.shipmentmethod.index')->with('success', 'Método de envío actualizado correctamente.');
    }

    public function destroy(ShipmentMethod $shipmentmethod)
    {
        $shipmentmethod->delete();

        return redirect()->route('admin.shipmentmethod.index')->with('success', 'Método de envío eliminado correctamente.');
    }
}
