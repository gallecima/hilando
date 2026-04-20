<?php

namespace App\Http\Controllers;

use App\Models\ShippingBox;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingBoxController extends Controller
{
    public function index()
    {
        $shippingBoxes = ShippingBox::orderBy('priority')->orderBy('name')->get();

        return view('admin.shipping-boxes.index', compact('shippingBoxes'));
    }

    public function create()
    {
        return view('admin.shipping-boxes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = $data['code'] ?: Str::upper(Str::slug($data['name'], '-'));

        ShippingBox::create($data);

        return redirect()->route('admin.shipping-boxes.index')->with('success', 'Caja de envío creada correctamente.');
    }

    public function edit(ShippingBox $shippingBox)
    {
        return view('admin.shipping-boxes.edit', compact('shippingBox'));
    }

    public function update(Request $request, ShippingBox $shippingBox)
    {
        $data = $this->validateData($request, $shippingBox->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = $data['code'] ?: Str::upper(Str::slug($data['name'], '-'));

        $shippingBox->update($data);

        return redirect()->route('admin.shipping-boxes.index')->with('success', 'Caja de envío actualizada correctamente.');
    }

    public function destroy(ShippingBox $shippingBox)
    {
        if ($shippingBox->shipmentMethods()->exists()) {
            return redirect()->route('admin.shipping-boxes.index')->with('error', 'No se puede eliminar una caja asociada a métodos de envío.');
        }

        $shippingBox->delete();

        return redirect()->route('admin.shipping-boxes.index')->with('success', 'Caja de envío eliminada correctamente.');
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:shipping_boxes,code';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['nullable', 'string', 'max:100', $uniqueRule],
            'provider' => 'nullable|string|max:120',
            'inner_length' => 'required|numeric|min:0.01',
            'inner_width' => 'required|numeric|min:0.01',
            'inner_height' => 'required|numeric|min:0.01',
            'max_weight' => 'required|numeric|min:0.01',
            'box_weight' => 'nullable|numeric|min:0',
            'priority' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
