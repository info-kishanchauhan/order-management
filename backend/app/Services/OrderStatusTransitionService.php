<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusTransitionService
{
    /**
     * Valid transitions:
     * pending -> paid | cancelled; paid -> cancelled; cancelled -> pending | paid (reopen / correction).
     *
     * @throws \App\Exceptions\InvalidOrderStatusTransitionException
     */
    public function transition(Order $order, OrderStatus $target): Order
    {
        $current = $order->status instanceof OrderStatus ? $order->status : OrderStatus::from((string) $order->getRawOriginal('status'));

        if ($current === $target) {
            return $order;
        }

        $allowed = match ($current) {
            OrderStatus::Pending => [OrderStatus::Paid, OrderStatus::Cancelled],
            OrderStatus::Paid => [OrderStatus::Cancelled],
            OrderStatus::Cancelled => [OrderStatus::Pending, OrderStatus::Paid],
        };

        if (! in_array($target, $allowed, true)) {
            throw new \App\Exceptions\InvalidOrderStatusTransitionException(
                "Cannot change status from {$current->value} to {$target->value}."
            );
        }

        return DB::transaction(function () use ($order, $target, $current) {
            $order->status = $target;
            $order->save();

            Log::info('order.status_transition', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'from' => $current->value,
                'to' => $target->value,
            ]);

            return $order->fresh();
        });
    }
}
