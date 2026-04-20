<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'is_active',
    ];

    /**
     * Relación: Un valor pertenece a un atributo.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Relación: Un valor puede estar en muchos productos.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_product');
    }

    public static function normalizeHexColor(?string $value): ?string
    {
        $value = trim((string) $value);

        if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value, $matches)) {
            return null;
        }

        $hex = strtoupper($matches[1]);

        if (strlen($hex) === 3) {
            $hex = collect(str_split($hex))
                ->map(fn (string $char) => $char . $char)
                ->implode('');
        }

        return '#' . $hex;
    }

    public static function isHexColorString(?string $value): bool
    {
        return static::normalizeHexColor($value) !== null;
    }

    public function isHexColor(): bool
    {
        return static::isHexColorString($this->value);
    }

    public function getHexColorAttribute(): ?string
    {
        return static::normalizeHexColor($this->value);
    }
}
