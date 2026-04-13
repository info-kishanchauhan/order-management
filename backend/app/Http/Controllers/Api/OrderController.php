<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\OrderStatusTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderStatusTransitionService $statusTransitionService
    ) {}

    public function index(IndexOrderRequest $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $validated = $request->validated();
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $filters = [
            'sort' => $validated['sort'] ?? 'created_at',
            'direction' => $validated['direction'] ?? 'desc',
            'status' => $validated['status'] ?? null,
            'q' => $validated['q'] ?? null,
        ];

        $paginator = $this->orderRepository->paginateForUser($userId, $page, $perPage, $filters);

        $payload = [
            'items' => OrderResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'next_page_url' => $paginator->nextPageUrl(),
            ],
        ];

        return $this->actionSuccess('Orders retrieved.', $payload);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $order = $this->orderRepository->findForUser($userId, $id);

        if ($order === null) {
            return $this->notFound('Order not found.');
        }

        return $this->actionSuccess(
            'Order retrieved.',
            (new OrderResource($order))->resolve()
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $status = isset($data['status'])
            ? OrderStatus::from($data['status'])
            : OrderStatus::Pending;

        /** @var Order $order */
        $order = $this->orderRepository->create([
            'user_id' => (int) $request->user()->id,
            'customer_name' => $data['customer_name'],
            'item_name' => $data['item_name'],
            'price_cents' => $data['price_cents'],
            'status' => $status,
        ]);

        return $this->actionSuccess(
            'Order created.',
            (new OrderResource($order))->resolve(),
            self::HTTP_CREATED
        );
    }

    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $order = $this->orderRepository->findForUser($userId, $id);

        if ($order === null) {
            return $this->notFound('Order not found.');
        }

        $validated = $request->validated();
        $target = OrderStatus::from($validated['status']);
        $current = $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::from((string) $order->getRawOriginal('status'));

        if ($target !== $current) {
            $order = $this->statusTransitionService->transition($order, $target);
        }

        $order->fill([
            'customer_name' => $validated['customer_name'],
            'item_name' => $validated['item_name'],
            'price_cents' => $validated['price_cents'],
        ]);
        $order->save();

        return $this->actionSuccess(
            'Order updated.',
            (new OrderResource($order->fresh()))->resolve()
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $order = $this->orderRepository->findForUser($userId, $id);

        if ($order === null) {
            return $this->notFound('Order not found.');
        }

        $order->delete();

        return $this->actionSuccess('Order deleted.', null);
    }
}
