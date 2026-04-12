<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/orders', [
                'customer_name' => 'Jane Customer',
                'item_name' => 'Premium Widget',
                'price_cents' => 2499,
            ]);

        $response->assertCreated()
            ->assertJsonPath('status', 201)
            ->assertJsonPath('data.customer_name', 'Jane Customer')
            ->assertJsonPath('data.item_name', 'Premium Widget')
            ->assertJsonPath('data.price_cents', 2499)
            ->assertJsonPath('data.status', 'pending');

        $orderId = (int) $response->json('data.id');
        $this->assertSame('ORD-'.str_pad((string) $orderId, 4, '0', STR_PAD_LEFT), $response->json('data.order_code'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Jane Customer',
            'order_code' => 'ORD-'.str_pad((string) $orderId, 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function test_can_transition_cancelled_order_to_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Cancelled,
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/orders/'.$order->id, [
                'customer_name' => $order->customer_name,
                'item_name' => $order->item_name,
                'price_cents' => $order->price_cents,
                'status' => 'paid',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }
}
