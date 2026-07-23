<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_only_their_own_orders(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $ownPending = Order::factory()->for($customer)->create(['status' => 'pending']);
        $ownDelivered = Order::factory()->for($customer)->create(['status' => 'delivered']);
        Order::factory()->for($other)->create(['status' => 'pending']);

        OrderItem::factory()->for($ownPending)->create();
        OrderItem::factory()->for($ownDelivered)->create();

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->has('orders.data', 2)
                ->where('filters.status', null)
                ->has('user.id')
            );
    }

    public function test_customer_can_filter_orders_by_status(): void
    {
        $customer = User::factory()->create();

        Order::factory()->for($customer)->create(['status' => 'pending']);
        Order::factory()->for($customer)->create(['status' => 'delivered']);
        Order::factory()->for($customer)->create(['status' => 'delivered']);

        $this->actingAs($customer)
            ->get(route('orders.index', ['status' => 'delivered']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->has('orders.data', 2)
                ->where('filters.status', 'delivered')
            );
    }

    public function test_customer_can_search_orders_by_order_number(): void
    {
        $customer = User::factory()->create();

        Order::factory()->for($customer)->create(['order_number' => 'CRV-FIND-ME']);
        Order::factory()->for($customer)->create(['order_number' => 'CRV-OTHER-01']);

        $this->actingAs($customer)
            ->get(route('orders.index', ['search' => 'FIND-ME']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->has('orders.data', 1)
                ->where('filters.search', 'FIND-ME')
                ->where('orders.data.0.order_number', 'CRV-FIND-ME')
            );
    }

    public function test_customer_can_view_own_order_detail(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['status' => 'processing']);
        OrderItem::factory()->for($order)->create();

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->where('order.id', $order->id)
                ->where('order.order_number', $order->order_number)
                ->has('order.items', 1)
                ->has('user.id')
            );
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $customer = User::factory()->create();
        $otherOrder = Order::factory()->for(User::factory())->create();

        $this->actingAs($customer)
            ->get(route('orders.show', $otherOrder))
            ->assertForbidden();
    }

    public function test_customer_can_view_checkout_confirmation_for_own_order(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create();
        OrderItem::factory()->for($order)->create();

        $this->actingAs($customer)
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Checkout/Confirmation')
                ->where('order.id', $order->id)
            );
    }

    public function test_placing_order_redirects_to_checkout_confirmation(): void
    {
        Mail::fake();

        $customer = User::factory()->create();
        $address = Address::factory()->for($customer)->default()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Bakery Lane',
            'city' => 'London',
            'postal_code' => 'SW1A 1AA',
            'country' => 'UK',
        ]);

        $product = Product::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'stock_status' => 'in_stock',
            'stock_quantity' => 20,
            'regular_price' => 12.50,
            'sale_price' => null,
        ]);

        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'session_id' => null,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'selected_attributes' => null,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($customer)->post(route('orders.store'), [
            'address_id' => $address->id,
            'email' => 'jane@example.com',
            'delivery_method' => 'standard',
            'delivery_notes' => null,
        ]);

        $order = Order::query()->where('user_id', $customer->id)->first();

        $this->assertNotNull($order);
        $this->assertSame('Jane', $order->first_name);
        $this->assertSame('123 Bakery Lane', $order->address_line1);
        $response->assertRedirect(route('checkout.confirmation', $order));
    }
}