<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            $code = 'ORD-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
            $order->forceFill(['order_code' => $code])->saveQuietly();
        });
    }

    protected $fillable = [
        'user_id',
        'customer_name',
        'item_name',
        'price_cents',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'status' => OrderStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWhereStatusFilter(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeWhereSearchFilter(Builder $query, ?string $q): Builder
    {
        if ($q === null || $q === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($q) {
            $inner->where('customer_name', 'like', '%'.$q.'%')
                ->orWhere('item_name', 'like', '%'.$q.'%')
                ->orWhere('order_code', 'like', '%'.$q.'%');
        });
    }

    /**
     * @param  array{sort?: string|null, direction?: string|null, status?: string|null, q?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->whereStatusFilter($filters['status'] ?? null)
            ->whereSearchFilter($filters['q'] ?? null)
            ->orderForList(
                $filters['sort'] ?? null,
                $filters['direction'] ?? null
            );
    }

    public function scopeOrderForList(Builder $query, ?string $sort, ?string $direction): Builder
    {
        $allowed = ['created_at', 'price_cents', 'status', 'customer_name', 'item_name', 'order_code'];
        $sort = in_array($sort, $allowed, true) ? $sort : 'created_at';
        $direction = strtolower($direction ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $direction);
    }
}
