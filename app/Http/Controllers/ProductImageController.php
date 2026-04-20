<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:8192',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        // Guarda directo en la carpeta final
        $path = $request->file('file')->store("uploads/products/gallery", "public");

        // Registra en la DB en ese instante
        $image = ProductImage::create([
            'product_id' => $request->product_id,
            'path' => $path,
        ]);



        return response()->json([
            'id' => $image->id,
            'path' => $path,
        ]);
    }

    public function destroy(ProductImage $productImage)
    {
        Storage::disk('public')->delete($productImage->path);
        $productImage->delete();

        return response()->json(['success' => true]);
    }
}
