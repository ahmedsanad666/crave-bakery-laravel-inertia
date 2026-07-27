<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_with_parent_options(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $parent = Category::factory()->create(['name' => 'Breads', 'slug' => 'breads']);

        $this->actingAs($admin)
            ->get(route('admin.categories.create', ['parent_id' => $parent->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Create')
                ->has('parentOptions', 1)
                ->where('parentOptions.0.id', $parent->id)
            );
    }

    public function test_store_creates_active_category(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Artisanal Breads',
                'slug' => 'artisanal-breads',
                'description' => 'Fresh hearth-baked loaves.',
                'parent_id' => '',
                'status' => 'active',
                'sort_order' => 1,
                'show_in_navigation' => true,
                'show_in_footer' => false,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Artisanal Breads',
            'slug' => 'artisanal-breads',
            'status' => 'active',
            'parent_id' => null,
        ]);
    }

    public function test_store_creates_draft_category(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Seasonal Specials',
                'slug' => 'seasonal-specials',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'seasonal-specials',
            'status' => 'draft',
        ]);
    }

    public function test_store_uploads_category_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->superAdmin()->create();
        $thumbnail = UploadedFile::fake()->image('thumb.jpg', 400, 400);
        $banner = UploadedFile::fake()->image('banner.jpg', 1280, 720);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Pastries',
                'slug' => 'pastries',
                'status' => 'active',
                'image' => $thumbnail,
                'banner_image' => $banner,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'pastries')->first();

        $this->assertNotNull($category);
        $this->assertNotNull($category->image);
        $this->assertNotNull($category->banner_image);
        Storage::disk('public')->assertExists($category->image);
        Storage::disk('public')->assertExists($category->banner_image);

        $publicImageUrl = Category::toPublicUrl($category->image);
        $this->assertNotNull($publicImageUrl);
        $this->assertStringStartsWith('/storage/', $publicImageUrl);

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['view' => 'tree']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Index')
                ->where('categoryTree.0.image', $publicImageUrl)
            );
    }

    public function test_to_public_url_leaves_absolute_urls_unchanged(): void
    {
        $external = 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80';

        $this->assertSame($external, Category::toPublicUrl($external));
        $this->assertNull(Category::toPublicUrl(null));
        $this->assertNull(Category::toPublicUrl(''));
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [])
            ->assertRedirect(route('admin.categories.create'))
            ->assertSessionHasErrors(['name', 'slug', 'status']);
    }
}
