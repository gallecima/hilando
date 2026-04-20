<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FavoriteController extends Controller
{
    private const SESSION_KEY = 'favorite_product_ids';

    public function count()
    {
        return response()->json([
            'count' => $this->getFavoriteIds()->count(),
        ]);
    }

    public function partial()
    {
        $favoriteIds = $this->getFavoriteIds();
        $favorites = collect();

        if ($favoriteIds->isNotEmpty()) {
            $favorites = Product::query()
                ->whereIn('id', $favoriteIds->all())
                ->where('is_active', true)
                ->with('categories')
                ->get()
                ->sortBy(fn (Product $product) => $favoriteIds->search((int) $product->id))
                ->values();
        }

        return view('front.favorites.partials.offcanvas', compact('favorites'));
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = (int) $data['product_id'];
        $favoriteIds = $this->getFavoriteIds();

        if ($favoriteIds->contains($productId)) {
            $favoriteIds = $favoriteIds
                ->reject(fn (int $id) => $id === $productId)
                ->values();

            $active = false;
            $message = 'Producto quitado de favoritos.';
        } else {
            $favoriteIds = $favoriteIds
                ->push($productId)
                ->unique()
                ->values();

            $active = true;
            $message = 'Producto agregado a favoritos.';
        }

        session([self::SESSION_KEY => $favoriteIds->all()]);

        return response()->json([
            'success' => true,
            'active' => $active,
            'count' => $favoriteIds->count(),
            'message' => $message,
        ]);
    }

    private function getFavoriteIds(): Collection
    {
        return collect(session(self::SESSION_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
    }
}
