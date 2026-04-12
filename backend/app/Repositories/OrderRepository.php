<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function model(): string
    {
        return Order::class;
    }

    /**
     * @return array<int, string>
     */
    public function getFieldsSearchable(): array
    {
        return ['user_id', 'status'];
    }

    /**
     * @param  array{sort?: string, direction?: string, status?: string|null, q?: string|null, per_page?: int}  $filters
     */
    public function paginateForUser(int $userId, int $page, int $perPage, array $filters): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->forUser($userId)
            ->filter($filters)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findForUser(int $userId, int $orderId): ?Order
    {
        /** @var Order|null $order */
        $order = $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereKey($orderId)
            ->first();

        return $order;
    }
}
