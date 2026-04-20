<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;

class DiscountCouponController extends Controller
{
    public function index()
    {
        $coupons = DiscountCoupon::orderByDesc('id')->paginate(20);
        return view('admin.discount_coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.discount_coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:discount_coupons,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses' => 'nullable|integer|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $data['uses'] = $data['uses'] ?? 0;

        DiscountCoupon::create($data);

        return redirect()->route('admin.discount-coupons.index')->with('success', 'Cupón creado correctamente.');
    }

    public function edit(DiscountCoupon $discountCoupon)
    {
        return view('admin.discount_coupons.edit', ['coupon' => $discountCoupon]);
    }

    public function update(Request $request, DiscountCoupon $discountCoupon)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:discount_coupons,code,' . $discountCoupon->id,
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'uses' => 'nullable|integer|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $data['uses'] = $data['uses'] ?? 0;

        $discountCoupon->update($data);

        return redirect()->route('admin.discount-coupons.index')->with('success', 'Cupón actualizado correctamente.');
    }

    public function destroy(DiscountCoupon $discountCoupon)
    {
        $discountCoupon->delete();

        return redirect()->route('admin.discount-coupons.index')->with('success', 'Cupón eliminado.');
    }
}