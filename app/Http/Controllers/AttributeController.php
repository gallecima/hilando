<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::paginate(20);
        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:attributes',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string',
            'has_stock_price' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['has_stock_price'] = $request->has('has_stock_price') ? 1 : 0;

        $attribute = Attribute::create($data);

        if (!empty($data['values'])) {
            foreach ($data['values'] as $value) {
                $value = trim((string) $value);

                if ($value !== '') {
                    $attribute->values()->create([
                        'value' => $value,
                        'slug' => Str::slug($attribute->name . '-' . $value),
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')->with('success', 'Atributo creado con valores.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->loadMissing('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $attribute->loadMissing('values');

        $data = $request->validate([
            'name' => 'required|string|unique:attributes,name,' . $attribute->id,
            'values' => 'nullable|array',
            'values.*' => 'nullable|string',
            'existing_ids' => 'nullable|array',
            'existing_values' => 'nullable|array',
            'has_stock_price' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['has_stock_price'] = $request->has('has_stock_price') ? 1 : 0;

        $attribute->update($data);

        $submittedExistingIds = collect($data['existing_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        $idsToDelete = $attribute->values
            ->pluck('id')
            ->diff($submittedExistingIds);

        if ($idsToDelete->isNotEmpty()) {
            $attribute->values()->whereIn('id', $idsToDelete)->delete();
        }

        // Actualizar valores existentes
        if ($submittedExistingIds->isNotEmpty()) {
            foreach (($data['existing_ids'] ?? []) as $index => $id) {
                $normalizedId = (int) $id;
                $valueText = trim((string) ($data['existing_values'][$index] ?? ''));

                if ($normalizedId < 1 || $valueText === '') {
                    continue;
                }

                $value = $attribute->values->firstWhere('id', $normalizedId) ?: AttributeValue::find($normalizedId);

                if ($value) {
                    $value->update([
                        'value' => $valueText,
                        'slug' => Str::slug($attribute->name . '-' . $valueText),
                    ]);
                }
            }
        }

        // Agregar nuevos valores
        if (!empty($data['values'])) {
            foreach ($data['values'] as $value) {
                $value = trim((string) $value);

                if ($value !== '') {
                    $attribute->values()->create([
                        'value' => $value,
                        'slug' => Str::slug($attribute->name . '-' . $value),
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')->with('success', 'Atributo actualizado.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('admin.attributes.index')->with('success', 'Atributo eliminado.');
    }
}
