<?php

namespace App\Models;

use App\Support\CatalogAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'base_price',
        'wholesale_price',
        'wholesale_min_quantity',
        'stock',
        'is_active',
        'order',
        'is_new',
        'is_featured',
        'featured_image',
        'downloadable_file_path',
        'downloadable_files_json',
        'height',
        'width',
        'length',
        'weight',
        'is_digital',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'wholesale_min_quantity' => 'integer',
        'downloadable_files_json' => 'array',
        'is_digital' => 'boolean',
        'is_active' => 'boolean',
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_product')
                    ->withPivot(['stock', 'price', 'image'])
                    ->withTimestamps();
    }  

    public function blogPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_product')->withTimestamps();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return array<int, string>
     */
    public function getDownloadableFilesAttribute(): array
    {
        $paths = [];

        foreach ((array) ($this->downloadable_files_json ?? []) as $entry) {
            if (is_string($entry)) {
                $paths[] = $entry;
                continue;
            }

            if (is_array($entry) && is_string($entry['path'] ?? null)) {
                $paths[] = $entry['path'];
            }
        }

        if (is_string($this->downloadable_file_path ?? null) && trim((string) $this->downloadable_file_path) !== '') {
            $paths[] = (string) $this->downloadable_file_path;
        }

        return collect($paths)
            ->map(fn (string $path) => trim($path))
            ->map(fn (string $path) => ltrim($path, '/'))
            ->filter(fn (string $path) => $path !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function getHasDownloadableFilesAttribute(): bool
    {
        return count($this->downloadable_files) > 0;
    }

    public function getPrimaryDownloadableFilePathAttribute(): ?string
    {
        return $this->downloadable_files[0] ?? null;
    }

    public function getHasDiscountPriceAttribute(): bool
    {
        $base = $this->base_price;
        if ($base === null) {
            return false;
        }

        return (float) $base > (float) $this->price;
    }

    public function usesWholesalePricing(?Customer $customer = null): bool
    {
        return CatalogAccess::isWholesale($customer) && $this->wholesale_price !== null;
    }

    public function resolveMinQuantity(?Customer $customer = null): int
    {
        if (CatalogAccess::isWholesale($customer)) {
            return max(1, (int) ($this->wholesale_min_quantity ?? 1));
        }

        return 1;
    }

    public function resolveUnitPrice(?Customer $customer = null, array $selectedAttributes = []): float
    {
        if ($this->usesWholesalePricing($customer)) {
            return round((float) $this->wholesale_price, 2);
        }

        $variantPrice = $this->resolveVariantPrice($selectedAttributes);

        return $variantPrice !== null
            ? round((float) $variantPrice, 2)
            : round((float) $this->price, 2);
    }

    public function resolveVariantPrice(array $selectedAttributes = []): ?float
    {
        if (count($selectedAttributes) !== 1) {
            return null;
        }

        $valueId = (int) data_get($selectedAttributes, '0.value_id', 0);
        if ($valueId < 1) {
            return null;
        }

        $attributeValue = $this->relationLoaded('attributeValues')
            ? $this->attributeValues->firstWhere('id', $valueId)
            : $this->attributeValues()->where('attribute_value_id', $valueId)->first();

        if ($attributeValue && $attributeValue->pivot && $attributeValue->pivot->price !== null) {
            return (float) $attributeValue->pivot->price;
        }

        return null;
    }

    public function getDownloadableFileUrlAttribute(): ?string
    {
        $path = $this->primary_downloadable_file_path;

        return $path
            ? Storage::disk('public')->url($path)
            : null;
    }

    public function attributeValuesGroupedByAttribute()
    {
        // Carga los valores con su relación de atributo y datos pivot
        return $this->attributeValues
            ->load('attribute') // nos aseguramos de tener acceso a $value->attribute
            ->groupBy(function ($value) {
                return $value->attribute->id;
            })
            ->map(function ($values) {
                // Agrupa bajo el mismo modelo de atributo
                $attribute = $values->first()->attribute;
                $attribute->setRelation('values', $values); // los valores se setean en la relación
                return $attribute;
            });
    }  
}
