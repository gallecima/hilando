<?php

namespace App\Services;

use App\Models\ShipmentMethod;
use App\Models\ShippingBox;
use Illuminate\Support\Collection;

class ShippingPackagingService
{
    public function packageCartForMethod(CartService $cart, ShipmentMethod $method): ?array
    {
        $boxes = $method->shippingBoxes()
            ->where('is_active', 1)
            ->orderBy('priority')
            ->orderByRaw('(inner_length * inner_width * inner_height) asc')
            ->get();

        if ($boxes->isEmpty()) {
            return [
                'packages' => [],
                'package_count' => 0,
                'total_weight' => 0.0,
                'total_box_weight' => 0.0,
                'unpacked_items' => [],
            ];
        }

        $units = $this->expandCartItemsToUnits($cart->getCartItems());
        if ($units === null) {
            return null;
        }

        if ($units->isEmpty()) {
            return [
                'packages' => [],
                'package_count' => 0,
                'total_weight' => 0.0,
                'total_box_weight' => 0.0,
                'unpacked_items' => [],
            ];
        }

        $packages = [];

        foreach ($units as $unit) {
            $placed = false;

            foreach ($packages as &$package) {
                if ($this->unitFitsPackage($unit, $package)) {
                    $this->pushUnitIntoPackage($package, $unit);
                    $placed = true;
                    break;
                }
            }
            unset($package);

            if ($placed) {
                continue;
            }

            $box = $this->selectBoxForUnit($unit, $boxes);
            if (!$box) {
                return null;
            }

            $packages[] = $this->createPackageFromUnit($box, $unit);
        }

        return [
            'packages' => array_values($packages),
            'package_count' => count($packages),
            'total_weight' => round(collect($packages)->sum('items_weight'), 2),
            'total_box_weight' => round(collect($packages)->sum('box_weight'), 2),
            'unpacked_items' => [],
        ];
    }

    protected function expandCartItemsToUnits($items): ?Collection
    {
        $units = collect();

        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $isDigital = (bool) ($product->is_digital ?? false);
            $hasDownloadFiles = (bool) ($product->has_downloadable_files ?? false);
            if ($isDigital || $hasDownloadFiles) {
                continue;
            }

            $weight = (float) ($product->weight ?? 0);
            $length = (float) ($product->length ?? 0);
            $width = (float) ($product->width ?? 0);
            $height = (float) ($product->height ?? 0);

            if ($weight <= 0 || $length <= 0 || $width <= 0 || $height <= 0) {
                return null;
            }

            $quantity = max(1, (int) $item->quantity);
            for ($i = 0; $i < $quantity; $i++) {
                $units->push([
                    'product_id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'weight' => $weight,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'volume' => round($length * $width * $height, 2),
                ]);
            }
        }

        return $units
            ->sortByDesc(fn (array $unit) => [$unit['volume'], $unit['weight']])
            ->values();
    }

    protected function selectBoxForUnit(array $unit, Collection $boxes): ?ShippingBox
    {
        return $boxes
            ->first(fn (ShippingBox $box) => $this->unitFitsBox($unit, $box));
    }

    protected function unitFitsPackage(array $unit, array $package): bool
    {
        $box = $package['box_model'];
        if (!$box instanceof ShippingBox) {
            return false;
        }

        if (!$this->unitFitsBox($unit, $box)) {
            return false;
        }

        $nextWeight = (float) $package['items_weight'] + (float) $unit['weight'];
        if ($nextWeight > (float) $box->max_weight) {
            return false;
        }

        $nextVolume = (float) $package['items_volume'] + (float) $unit['volume'];

        return $nextVolume <= (float) $box->inner_volume;
    }

    protected function unitFitsBox(array $unit, ShippingBox $box): bool
    {
        if ((float) $unit['weight'] > (float) $box->max_weight) {
            return false;
        }

        $unitDims = [(float) $unit['length'], (float) $unit['width'], (float) $unit['height']];
        $boxDims = [(float) $box->inner_length, (float) $box->inner_width, (float) $box->inner_height];

        sort($unitDims);
        sort($boxDims);

        foreach ($unitDims as $index => $dim) {
            if ($dim > ($boxDims[$index] ?? 0)) {
                return false;
            }
        }

        return (float) $unit['volume'] <= (float) $box->inner_volume;
    }

    protected function createPackageFromUnit(ShippingBox $box, array $unit): array
    {
        return [
            'box_id' => (int) $box->id,
            'box_name' => (string) $box->name,
            'box_code' => (string) $box->code,
            'box_weight' => (float) $box->box_weight,
            'items_weight' => (float) $unit['weight'],
            'items_volume' => (float) $unit['volume'],
            'total_shipping_weight' => round((float) $unit['weight'] + (float) $box->box_weight, 2),
            'items' => [[
                'product_id' => (int) $unit['product_id'],
                'name' => (string) $unit['name'],
                'quantity' => 1,
                'weight' => (float) $unit['weight'],
                'volume' => (float) $unit['volume'],
            ]],
            'box_model' => $box,
        ];
    }

    protected function pushUnitIntoPackage(array &$package, array $unit): void
    {
        $package['items_weight'] = round((float) $package['items_weight'] + (float) $unit['weight'], 2);
        $package['items_volume'] = round((float) $package['items_volume'] + (float) $unit['volume'], 2);
        $package['total_shipping_weight'] = round((float) $package['items_weight'] + (float) $package['box_weight'], 2);

        foreach ($package['items'] as &$line) {
            if ((int) $line['product_id'] === (int) $unit['product_id']) {
                $line['quantity']++;
                $line['weight'] = round((float) $line['weight'] + (float) $unit['weight'], 2);
                $line['volume'] = round((float) $line['volume'] + (float) $unit['volume'], 2);
                return;
            }
        }
        unset($line);

        $package['items'][] = [
            'product_id' => (int) $unit['product_id'],
            'name' => (string) $unit['name'],
            'quantity' => 1,
            'weight' => (float) $unit['weight'],
            'volume' => (float) $unit['volume'],
        ];
    }
}
