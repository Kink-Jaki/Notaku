<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'description', 'type', 'value', 'min_order', 'is_active',
    'starts_at', 'ends_at', 'usage_limit', 'used_count',
])]
class PromoCode extends Model
{
    /** @use HasFactory<PromoCodeFactory> */
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    public function isCurrentlyActive(?Carbon $now = null): bool
    {
        $now ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->lt($now)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Compute the discount amount for a given subtotal.
     */
    public function discountFor(int $subtotal): int
    {
        if ($this->type === self::TYPE_FIXED) {
            return min((int) $this->value, $subtotal);
        }

        return (int) round($subtotal * ((int) $this->value / 100));
    }

    public function getDescriptionAttribute(): string
    {
        return $this->attributes['description'] ?? ($this->type === self::TYPE_PERCENT
            ? $this->value.'% diskon'
            : 'Rp '.number_format($this->value, 0, ',', '.').' diskon');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
