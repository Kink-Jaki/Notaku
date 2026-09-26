<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'customer_name', 'note', 'status',
    'subtotal', 'discount', 'promo_code_id', 'total', 'payment_method',
    'approved_by', 'approved_at', 'rejected_reason',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function displayLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'menunggu',
            self::STATUS_PROCESSING => 'diproses',
            self::STATUS_COMPLETED => 'selesai',
            self::STATUS_REJECTED => 'ditolak',
            default => $this->status ?? '-',
        };
    }

    public function displayBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-soft--warning',
            self::STATUS_PROCESSING => 'badge-soft--info',
            self::STATUS_COMPLETED => 'badge-soft--success',
            self::STATUS_REJECTED => 'badge-soft--danger',
            default => 'badge-soft--neutral',
        };
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }
}
