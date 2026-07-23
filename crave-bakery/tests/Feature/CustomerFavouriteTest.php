<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favourite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerFavouriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_favourites_index(): void
    {
        $this->get(route('favourites.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_list_only_their_favourites_newest_first(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        $older = Product::factory()->create(['name' => 'Older Cake']);
        $newer = Product::factory()->create(['name' => 'Newer Cake']);
        $otherProduct = Product::factory()->create();

        Favourite::factory()->for($customer)->for($older)->create([
            'created_at' => now()->subDay(),
        ]);
        Favourite::factory()->for($customer)->for($newer)->create([
            'created_at' => now(),
        ]);
        Favourite::factory()->for($other)->for($otherProduct)->create();

        $this->actingAs($customer)
            ->get(route('favourites.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Favourites/Index')
                ->has('favourites.data', 2)
                ->where('favourites.data.0.product.name', 'Newer Cake')
                ->where('favourites.data.1.product.name', 'Older Cake')
                ->where('favourites.data.0.product.is_favourited', true)
                ->has('collections')
                ->has('filters')
                ->has('categoryOptions')
                ->has('user.id')
            );
    }

    public function test_customer_can_toggle_favourite_on_and_off(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->post(route('favourites.toggle', $product))
            ->assertRedirect(route('favourites.index'));

        $this->assertDatabaseHas('favourites', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->post(route('favourites.toggle', $product))
            ->assertRedirect(route('favourites.index'));

        $this->assertDatabaseMissing('favourites', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_clear_all_favourites(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        Favourite::factory()->for($customer)->count(2)->create();
        Favourite::factory()->for($other)->create();

        $this->actingAs($customer)
            ->from(route('favourites.index'))
            ->delete(route('favourites.clear'))
            ->assertRedirect(route('favourites.index'));

        $this->assertSame(0, Favourite::query()->where('user_id', $customer->id)->count());
        $this->assertSame(1, Favourite::query()->where('user_id', $other->id)->count());
    }

    public function test_favourites_can_be_filtered_by_search_and_category(): void
    {
        $customer = User::factory()->create();
        $category = Category::factory()->create(['status' => 'active']);

        $match = Product::factory()->create(['name' => 'Chocolate Croissant']);
        $other = Product::factory()->create(['name' => 'Vanilla Tart']);

        $match->categories()->attach($category);
        $other->categories()->attach($category);

        Favourite::factory()->for($customer)->for($match)->create();
        Favourite::factory()->for($customer)->for($other)->create();

        $this->actingAs($customer)
            ->get(route('favourites.index', [
                'search' => 'Chocolate',
                'category_id' => $category->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Favourites/Index')
                ->has('favourites.data', 1)
                ->where('favourites.data.0.product.name', 'Chocolate Croissant')
                ->where('filters.search', 'Chocolate')
                ->where('filters.category_id', $category->id)
            );
    }
}
