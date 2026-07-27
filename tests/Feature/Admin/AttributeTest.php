<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_for_super_admin(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Attribute::factory()->create(['name' => 'Size']);

        $this->actingAs($admin)
            ->get(route('admin.attributes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Attributes/Index')
                ->has('attributes', 1)
                ->where('attributes.0.name', 'Size')
            );
    }

    public function test_store_creates_attribute_with_values(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.attributes.store'), [
                'name' => 'Size',
                'type' => 'text',
                'display_type' => 'pills',
                'sort_order' => 1,
                'values' => [
                    ['value' => 'Small', 'sort_order' => 1],
                    ['value' => 'Large', 'sort_order' => 2],
                ],
            ])
            ->assertRedirect(route('admin.attributes.index'));

        $attribute = Attribute::query()->where('name', 'Size')->first();

        $this->assertNotNull($attribute);
        $this->assertDatabaseCount('attribute_values', 2);
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Small',
        ]);
    }

    public function test_update_syncs_values_add_rename_remove(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $attribute = Attribute::factory()->create([
            'name' => 'Flavor',
            'type' => 'text',
            'display_type' => 'dropdown',
        ]);
        $keep = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Vanilla',
            'sort_order' => 1,
        ]);
        $remove = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Old Flavor',
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.attributes.update', $attribute), [
                'name' => 'Flavor',
                'type' => 'text',
                'display_type' => 'dropdown',
                'sort_order' => 1,
                'values' => [
                    [
                        'id' => $keep->id,
                        'value' => 'Classic Vanilla',
                        'sort_order' => 1,
                    ],
                    [
                        'value' => 'Chocolate',
                        'sort_order' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.attributes.index'));

        $this->assertDatabaseHas('attribute_values', [
            'id' => $keep->id,
            'value' => 'Classic Vanilla',
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Chocolate',
        ]);
        $this->assertDatabaseMissing('attribute_values', [
            'id' => $remove->id,
        ]);
    }

    public function test_reorder_updates_sort_order(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $first = Attribute::factory()->create(['name' => 'A', 'sort_order' => 1]);
        $second = Attribute::factory()->create(['name' => 'B', 'sort_order' => 2]);

        $this->actingAs($admin)
            ->patch(route('admin.attributes.reorder'), [
                'ordered_ids' => [$second->id, $first->id],
            ])
            ->assertRedirect();

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
    }

    public function test_destroy_removes_attribute_and_values(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $this->actingAs($admin)
            ->delete(route('admin.attributes.destroy', $attribute))
            ->assertRedirect(route('admin.attributes.index'));

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
        $this->assertDatabaseCount('attribute_values', 0);
    }

    public function test_guest_cannot_access_index(): void
    {
        $this->get(route('admin.attributes.index'))->assertRedirect();
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.attributes.index'))
            ->post(route('admin.attributes.store'), [])
            ->assertRedirect(route('admin.attributes.index'))
            ->assertSessionHasErrors(['name', 'type', 'display_type']);
    }

    public function test_color_type_requires_color_swatch_on_values(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.attributes.index'))
            ->post(route('admin.attributes.store'), [
                'name' => 'Glaze',
                'type' => 'color',
                'display_type' => 'swatches',
                'values' => [
                    ['value' => 'Honey', 'sort_order' => 1],
                ],
            ])
            ->assertRedirect(route('admin.attributes.index'))
            ->assertSessionHasErrors('values.0.color_swatch');
    }

    public function test_regular_user_cannot_store_attribute(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('admin.attributes.store'), [
                'name' => 'Size',
                'type' => 'text',
                'display_type' => 'pills',
            ])
            ->assertForbidden();
    }
}
