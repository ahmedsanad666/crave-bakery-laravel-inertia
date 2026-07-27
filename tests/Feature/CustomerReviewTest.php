<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_store_a_review(): void
    {
        $product = Product::factory()->create();

        $this->post(route('reviews.store', $product), [
            'rating' => 5,
            'title' => 'Amazing pastry',
            'body' => 'Absolutely loved it.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_without_delivered_purchase_cannot_store_a_review(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->from(route('products.show', $product))
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'title' => 'Amazing pastry',
                'body' => 'Absolutely loved it.',
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors('product');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_with_delivered_purchase_can_store_a_pending_verified_review(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->delivered()->for($customer)->create();

        OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $this->actingAs($customer)
            ->from(route('products.show', $product))
            ->post(route('reviews.store', $product), [
                'rating' => 4,
                'title' => 'Great bakery treat',
                'body' => 'Fresh and delicious, will order again.',
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'title' => 'Great bakery treat',
            'status' => 'pending',
            'is_verified_purchase' => true,
        ]);
    }

    public function test_customer_cannot_store_a_duplicate_review(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->delivered()->for($customer)->create();

        OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        Review::factory()->for($customer)->for($product)->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->from(route('products.show', $product))
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'title' => 'Second review',
                'body' => 'Should not be allowed.',
            ])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors('product');

        $this->assertSame(1, Review::query()->where('user_id', $customer->id)->count());
    }

    public function test_product_show_passes_can_review_for_eligible_customer(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);
        $order = Order::factory()->delivered()->for($customer)->create();

        OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);

        $this->actingAs($customer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('canReview', true)
            );
    }

    public function test_product_show_hides_can_review_for_guest(): void
    {
        $product = Product::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('canReview', false)
            );
    }
}
