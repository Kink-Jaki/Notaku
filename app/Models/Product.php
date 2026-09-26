<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'name', 'description', 'price', 'stock', 'image', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public static function findStock(int $productId): ?int
    {
        return self::whereKey($productId)->value('stock');
    }

    public static function decrementStock(int $productId, int $qty): void
    {
        self::whereKey($productId)->decrement('stock', $qty);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilter($query, array $filters)
    {
        if ($filters['category_id'] ?? null) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($filters['search'] ?? null) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
