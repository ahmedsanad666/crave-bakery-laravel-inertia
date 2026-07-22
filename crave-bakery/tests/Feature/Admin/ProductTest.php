<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_with_stats(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Product::factory()->count(2)->create(['status' => 'active', 'is_featured' => true]);
        Product::factory()->outOfStock()->create(['is_featured' => false]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Index')
                ->where('stats.total', 3)
                ->where('stats.active', 3)
                ->where('stats.out_of_stock', 1)
                ->where('stats.featured', 2)
                ->has('products.data', 3)
            );
    }

    public function test_create_page_loads_options(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Category::factory()->create(['name' => 'Breads']);
        Attribute::factory()->create(['name' => 'Size']);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Create')
                ->has('categoryOptions', 1)
                ->has('attributeOptions', 1)
            );
    }

    public function test_store_creates_product_with_relations(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create();
        $attribute = Attribute::factory()->create();
        $value = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Sourdough Loaf',
                'slug' => 'sourdough-loaf',
                'sku' => 'CB-10001',
                'regular_price' => 12.5,
                'status' => 'active',
                'stock_quantity' => 10,
                'category_ids' => [$category->id],
                'attribute_value_ids' => [$value->id],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'sourdough-loaf')->first();

        $this->assertNotNull($product);
        $this->assertSame('in_stock', $product->stock_status);
        $this->assertTrue($product->categories->contains('id', $category->id));
        $this->assertTrue($product->attributeValues->contains('id', $value->id));
    }

    public function test_store_uploads_thumbnail_and_gallery(): void
    {
        Storage::fake('public');
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Croissant',
                'slug' => 'croissant',
                'sku' => 'CB-10002',
                'regular_price' => 4.5,
                'status' => 'draft',
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
                'images' => [
                    UploadedFile::fake()->image('gallery-1.jpg'),
                    UploadedFile::fake()->image('gallery-2.jpg'),
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'croissant')->first();

        $this->assertNotNull($product?->thumbnail);
        Storage::disk('public')->assertExists($product->thumbnail);
        $this->assertCount(2, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    public function test_update_syncs_pivots_and_fields(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'sku' => 'CB-OLD',
        ]);
        $oldCategory = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $product->categories()->sync([$oldCategory->id]);

        $value = AttributeValue::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'New Name',
                'slug' => 'new-name',
                'sku' => 'CB-NEW',
                'regular_price' => 9.99,
                'status' => 'active',
                'stock_quantity' => 0,
                'allow_backorders' => true,
                'category_ids' => [$newCategory->id],
                'attribute_value_ids' => [$value->id],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame('New Name', $product->name);
        $this->assertSame('on_backorder', $product->stock_status);
        $this->assertTrue($product->categories->contains('id', $newCategory->id));
        $this->assertFalse($product->categories->contains('id', $oldCategory->id));
        $this->assertTrue($product->attributeValues->contains('id', $value->id));
    }

    public function test_update_rejects_duplicate_slug(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Product::factory()->create(['slug' => 'taken-slug', 'sku' => 'CB-A']);
        $product = Product::factory()->create(['slug' => 'other-slug', 'sku' => 'CB-B']);

        $this->actingAs($admin)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'slug' => 'taken-slug',
                'sku' => 'CB-B',
                'regular_price' => 5,
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('slug');
    }

    public function test_destroy_soft_deletes_product(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_guest_cannot_access_index(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect();
    }

    public function test_regular_user_cannot_store_product(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Cake',
                'slug' => 'cake',
                'sku' => 'CB-X',
                'regular_price' => 10,
                'status' => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_edit_page_renders_product(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $product = Product::factory()->create(['name' => 'Baguette']);

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Products/Edit')
                ->where('product.name', 'Baguette')
                ->has('categoryOptions')
                ->has('attributeOptions')
            );
    }
}
