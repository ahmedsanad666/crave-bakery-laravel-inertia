<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\SiteSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_view_settings(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_without_site_settings_permission_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create([
            'permissions' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();
        SiteSettingService::set('name', 'Crave Bakery');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Index')
                ->where('settings.name', 'Crave Bakery')
                ->has('palettes', 6)
                ->has('fonts.heading')
                ->has('fonts.body')
                ->has('resolvedPalette.id')
            );
    }

    public function test_super_admin_can_update_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->patch(route('admin.settings.update'), [
                'name' => 'Updated Bakery',
                'overview' => 'Fresh daily.',
                'hero_title' => 'Hello Oven',
                'hero_description' => 'Warm breads.',
                'story_title' => 'Our Tale',
                'story_content' => 'Once upon a loaf.',
                'email' => 'hello@example.com',
                'phone' => '555-0100',
                'address' => '1 Crumb Lane',
                'social_links' => [
                    'facebook' => 'https://facebook.com/crave',
                    'instagram' => null,
                    'twitter' => null,
                    'youtube' => null,
                ],
                'theme_palette' => 'honey_cream',
                'font_heading' => 'Lora',
                'font_body' => 'Source Sans 3',
                'seo_title_template' => '%site_name% | Fresh',
                'seo_meta_description' => 'Best bakery in town.',
                'seo_meta_keywords' => ['bakery', 'bread'],
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertSame('Updated Bakery', SiteSettingService::get('name'));
        $this->assertSame('Hello Oven', SiteSettingService::get('hero_title'));
        $this->assertSame('honey_cream', SiteSettingService::get('theme_palette'));
        $this->assertSame('Lora', SiteSettingService::get('font_heading'));
        $this->assertSame(
            ['facebook' => 'https://facebook.com/crave', 'instagram' => null, 'twitter' => null, 'youtube' => null],
            SiteSettingService::get('social_links'),
        );
        $this->assertSame(['bakery', 'bread'], SiteSettingService::get('seo_meta_keywords'));
    }

    public function test_invalid_palette_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->patch(route('admin.settings.update'), [
                'name' => 'Crave Bakery',
                'theme_palette' => 'not-a-real-palette',
                'font_heading' => 'Playfair Display',
                'font_body' => 'Inter',
            ])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHasErrors('theme_palette');
    }

    public function test_logo_upload_is_stored(): void
    {
        Storage::fake('public');

        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->patch(route('admin.settings.update'), [
                'name' => 'Crave Bakery',
                'theme_palette' => 'artisanal_warmth',
                'font_heading' => 'Playfair Display',
                'font_body' => 'Inter',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect(route('admin.settings.index'));

        $path = SiteSettingService::get('logo');

        $this->assertIsString($path);
        $this->assertNotSame('', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'logo',
            'value' => $path,
        ]);
    }

    public function test_public_payload_includes_palette_tokens(): void
    {
        $payload = app(SiteSettingService::class)->publicPayload();

        $this->assertArrayHasKey('tokens', $payload['theme']['palette']);
        $this->assertSame('#3D1A0E', $payload['theme']['palette']['tokens']['primary']);
        $this->assertSame('#E8572A', $payload['theme']['palette']['tokens']['accent']);
        $this->assertArrayHasKey('--color-primary', app(SiteSettingService::class)->themeCssProperties());
    }
}
