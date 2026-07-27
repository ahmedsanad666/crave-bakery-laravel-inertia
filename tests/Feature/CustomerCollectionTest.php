<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_collection(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->post(route('collections.store'), [
                'name' => 'Weekend Treats',
                'description' => 'Sweet picks for Saturday',
                'privacy' => 'private',
            ])
            ->assertRedirect(route('favourites.index'));

        $this->assertDatabaseHas('collections', [
            'user_id' => $customer->id,
            'name' => 'Weekend Treats',
            'description' => 'Sweet picks for Saturday',
            'privacy' => 'private',
        ]);
    }

    public function test_customer_can_update_own_collection(): void
    {
        $customer = User::factory()->create();
        $collection = Collection::factory()->for($customer)->create([
            'name' => 'Old Name',
            'privacy' => 'private',
        ]);

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->patch(route('collections.update', $collection), [
                'name' => 'New Name',
                'description' => 'Updated notes',
                'privacy' => 'public',
            ])
            ->assertRedirect(route('favourites.index'));

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'New Name',
            'description' => 'Updated notes',
            'privacy' => 'public',
        ]);
    }

    public function test_customer_can_delete_own_collection(): void
    {
        $customer = User::factory()->create();
        $collection = Collection::factory()->for($customer)->create();

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->delete(route('collections.destroy', $collection))
            ->assertRedirect(route('favourites.index'));

        $this->assertDatabaseMissing('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_customer_cannot_update_another_users_collection(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create([
            'name' => 'Private List',
        ]);

        $this->actingAs($customer)
            ->patch(route('collections.update', $collection), [
                'name' => 'Hacked',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'Private List',
        ]);
    }

    public function test_customer_cannot_delete_another_users_collection(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create();

        $this->actingAs($customer)
            ->delete(route('collections.destroy', $collection))
            ->assertForbidden();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_customer_can_attach_and_detach_product(): void
    {
        $customer = User::factory()->create();
        $collection = Collection::factory()->for($customer)->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->post(route('collections.products.attach', [
                'collection' => $collection,
                'product' => $product,
            ]))
            ->assertRedirect(route('favourites.index'));

        $this->assertTrue($collection->products()->where('products.id', $product->id)->exists());

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->delete(route('collections.products.detach', [
                'collection' => $collection,
                'product' => $product,
            ]))
            ->assertRedirect(route('favourites.index'));

        $this->assertFalse($collection->fresh()->products()->where('products.id', $product->id)->exists());
    }

    public function test_attach_product_is_idempotent(): void
    {
        $customer = User::factory()->create();
        $collection = Collection::factory()->for($customer)->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->post(route('collections.products.attach', [
                'collection' => $collection,
                'product' => $product,
            ]))
            ->assertRedirect();

        $this->actingAs($customer)
            ->post(route('collections.products.attach', [
                'collection' => $collection,
                'product' => $product,
            ]))
            ->assertRedirect();

        $this->assertSame(1, $collection->products()->count());
    }

    public function test_customer_cannot_attach_to_another_users_collection(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $collection = Collection::factory()->for($other)->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->post(route('collections.products.attach', [
                'collection' => $collection,
                'product' => $product,
            ]))
            ->assertForbidden();
    }

    public function test_favourites_index_includes_collections_summary(): void
    {
        $customer = User::factory()->create();
        $collection = Collection::factory()->for($customer)->create([
            'name' => 'Brunch Board',
            'description' => 'Morning picks',
        ]);
        $products = Product::factory()->count(2)->create();
        $collection->products()->attach($products->pluck('id'));

        $this->actingAs($customer)
            ->get(route('favourites.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Favourites/Index')
                ->has('collections', 1)
                ->where('collections.0.name', 'Brunch Board')
                ->where('collections.0.description', 'Morning picks')
                ->where('collections.0.products_count', 2)
                ->has('collections.0.products', 2)
            );
    }
}
