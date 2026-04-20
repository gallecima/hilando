<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index()
    {
        $values = AttributeValue::with('attribute')->paginate(20);
        return view('attribute_values.index', compact('values'));
    }

    public function create()
    {
        return view('attribute_values.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string',
            'slug' => 'required|string|unique:attribute_values',
            'is_active' => 'boolean',
        ]);

        AttributeValue::create($data);

        return redirect()->route('attribute_values.index')->with('success', 'Valor de atributo creado.');
    }

    public function edit(AttributeValue $attributeValue)
    {
        return view('attribute_values.edit', compact('attributeValue'));
    }

    public function update(Request $request, AttributeValue $attributeValue)
    {
        $data = $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string',
            'slug' => 'required|string|unique:attribute_values,slug,' . $attributeValue->id,
            'is_active' => 'boolean',
        ]);

        $attributeValue->update($data);

        return redirect()->route('attribute_values.index')->with('success', 'Valor de atributo actualizado.');
    }

    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();
        return redirect()->route('attribute_values.index')->with('success', 'Valor de atributo eliminado.');
    }
}