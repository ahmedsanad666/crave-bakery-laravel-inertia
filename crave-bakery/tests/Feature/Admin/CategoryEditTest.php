<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_renders_with_category_fields(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create([
            'name' => 'Pastries',
            'slug' => 'pastries',
            'meta_title' => 'Pastries | Crave',
            'show_in_homepage' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Edit')
                ->where('category.id', $category->id)
                ->where('category.name', 'Pastries')
                ->where('category.slug', 'pastries')
                ->where('category.meta_title', 'Pastries | Crave')
                ->where('category.show_in_homepage', true)
                ->has('parentOptions')
            );
    }

    public function test_update_changes_category_fields(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'New Name',
                'slug' => 'new-name',
                'status' => 'active',
                'parent_id' => '',
                'sort_order' => 2,
                'show_in_navigation' => true,
                'show_in_homepage' => false,
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'status' => 'active',
            'description' => 'Updated description',
            'parent_id' => null,
        ]);
    }

    public function test_update_replaces_category_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->superAdmin()->create();
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('categories/thumbnails', 'public');

        $category = Category::factory()->create([
            'name' => 'Cakes',
            'slug' => 'cakes',
            'image' => $oldPath,
            'status' => 'active',
        ]);

        $newImage = UploadedFile::fake()->image('new.jpg', 400, 400);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Cakes',
                'slug' => 'cakes',
                'status' => 'active',
                'image' => $newImage,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertNotSame($oldPath, $category->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($category->image);

        $publicUrl = Category::toPublicUrl($category->image);

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['view' => 'tree']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Index')
                ->where('categoryTree.0.image', $publicUrl)
            );
    }

    public function test_update_rejects_self_as_parent(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create([
            'name' => 'Breads',
            'slug' => 'breads',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.categories.edit', $category))
            ->put(route('admin.categories.update', $category), [
                'name' => 'Breads',
                'slug' => 'breads',
                'status' => 'active',
                'parent_id' => $category->id,
            ])
            ->assertRedirect(route('admin.categories.edit', $category))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_update_rejects_descendant_as_parent(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $parent = Category::factory()->create([
            'name' => 'Parent',
            'slug' => 'parent-cat',
        ]);
        $child = Category::factory()->create([
            'name' => 'Child',
            'slug' => 'child-cat',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.categories.edit', $parent))
            ->put(route('admin.categories.update', $parent), [
                'name' => 'Parent',
                'slug' => 'parent-cat',
                'status' => 'active',
                'parent_id' => $child->id,
            ])
            ->assertRedirect(route('admin.categories.edit', $parent))
            ->assertSessionHasErrors('parent_id');
    }
}
