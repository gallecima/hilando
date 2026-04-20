<?php

namespace App\Support;

use App\Models\Customer;

class CatalogAccess
{
    public const RETAIL = 'minorista';
    public const WHOLESALE = 'mayorista';

    public static function canUseWholesale(?Customer $customer = null): bool
    {
        return (bool) ($customer?->is_wholesaler);
    }

    public static function mode(?Customer $customer = null): string
    {
        if (!static::canUseWholesale($customer)) {
            return static::RETAIL;
        }

        return session('catalog_access_mode') === static::WHOLESALE
            ? static::WHOLESALE
            : static::RETAIL;
    }

    public static function isWholesale(?Customer $customer = null): bool
    {
        return static::mode($customer) === static::WHOLESALE;
    }

    public static function setMode(string $mode, ?Customer $customer = null): string
    {
        $resolvedMode = $mode === static::WHOLESALE && static::canUseWholesale($customer)
            ? static::WHOLESALE
            : static::RETAIL;

        session(['catalog_access_mode' => $resolvedMode]);

        return $resolvedMode;
    }
}
