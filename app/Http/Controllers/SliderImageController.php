<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SliderImageController extends Controller
{
    public function store(Request $request, Slider $slider)
    {
        $data = $this->validatePayload($request, false);

        $filename = Str::uuid() . '.' . $request->file('imagen')->getClientOriginalExtension();

        $path = $request->file('imagen')->storeAs(
            'uploads/sliders',
            $filename,
            'public'
        );

        $ctaButtons = $this->normalizeCtaButtons($data['cta_buttons'] ?? []);

        $slider->images()->create([
            'imagen' => $path,
            'orden' => $data['orden'] ?? 0,
            'hero_title' => filled($data['hero_title'] ?? null) ? trim((string) $data['hero_title']) : null,
            'hero_text' => filled($data['hero_text'] ?? null) ? trim((string) $data['hero_text']) : null,
            'cta_buttons' => $ctaButtons !== [] ? $ctaButtons : null,
        ]);

        return back()->with('success', 'Imagen agregada al slider.');
    }

    public function update(Request $request, Slider $slider, SliderImage $image)
    {
        if ($image->slider_id !== $slider->id) {
            abort(403);
        }

        $data = $this->validatePayload($request, true);
        $payload = [
            'orden' => $data['orden'] ?? $image->orden,
            'hero_title' => filled($data['hero_title'] ?? null) ? trim((string) $data['hero_title']) : null,
            'hero_text' => filled($data['hero_text'] ?? null) ? trim((string) $data['hero_text']) : null,
        ];

        $ctaButtons = $this->normalizeCtaButtons($data['cta_buttons'] ?? []);
        $payload['cta_buttons'] = $ctaButtons !== [] ? $ctaButtons : null;

        if ($request->hasFile('imagen')) {
            $filename = Str::uuid() . '.' . $request->file('imagen')->getClientOriginalExtension();
            $path = $request->file('imagen')->storeAs(
                'uploads/sliders',
                $filename,
                'public'
            );

            Storage::disk('public')->delete($image->imagen);
            $payload['imagen'] = $path;
        }

        $image->update($payload);

        return back()->with('success', 'Imagen actualizada correctamente.');
    }

    public function destroy(Slider $slider, SliderImage $image)
    {
        // Asegurarse de que la imagen pertenezca al slider
        if ($image->slider_id !== $slider->id) {
            abort(403);
        }

        Storage::disk('public')->delete($image->imagen);
        $image->delete();

        return back()->with('success', 'Imagen eliminada.');
    }

    public function sort(Request $request, Slider $slider)
    {
        $orden = $request->input('orden');

        foreach ($orden as $item) {
            SliderImage::where('id', $item['id'])
                ->where('slider_id', $slider->id)
                ->update(['orden' => $item['orden']]);
        }

        return response()->json(['success' => true]);
    }

    private function validatePayload(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'imagen' => [$isUpdate ? 'nullable' : 'required', 'image', 'max:4096'],
            'orden' => 'nullable|integer',
            'hero_title' => 'nullable|string|max:255',
            'hero_text' => 'nullable|string|max:2000',
            'cta_buttons' => 'nullable|array|max:5',
            'cta_buttons.*.label' => 'nullable|string|max:80',
            'cta_buttons.*.url' => 'nullable|string|max:255',
        ]);
    }

    private function normalizeCtaButtons(array $buttons): array
    {
        return collect($buttons)
            ->take(5)
            ->map(function ($button) {
                return [
                    'label' => trim((string) ($button['label'] ?? '')),
                    'url' => trim((string) ($button['url'] ?? '')),
                ];
            })
            ->filter(fn (array $button) => $button['label'] !== '' && $button['url'] !== '')
            ->values()
            ->all();
    }
} 
