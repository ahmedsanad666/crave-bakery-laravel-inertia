<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validAddressPayload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Home',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '555-0100',
            'address_line1' => '123 Bakery Lane',
            'address_line2' => null,
            'city' => 'London',
            'state' => null,
            'postal_code' => 'SW1A 1AA',
            'country' => 'UK',
            'is_default' => false,
        ], $overrides);
    }

    public function test_guest_is_redirected_from_addresses_index(): void
    {
        $this->get(route('addresses.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_list_only_their_addresses(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();

        Address::factory()->for($customer)->create(['label' => 'Home']);
        Address::factory()->for($customer)->create(['label' => 'Office']);
        Address::factory()->for($other)->create(['label' => 'Home']);

        $this->actingAs($customer)
            ->get(route('addresses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Addresses')
                ->has('addresses', 2)
                ->has('user.id')
            );
    }

    public function test_first_address_becomes_default_automatically(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->post(route('addresses.store'), $this->validAddressPayload([
                'is_default' => false,
            ]))
            ->assertRedirect(route('addresses.index'));

        $address = Address::query()->where('user_id', $customer->id)->first();

        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);
    }

    public function test_customer_can_create_address_and_set_as_default(): void
    {
        $customer = User::factory()->create();
        Address::factory()->for($customer)->default()->create();

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->post(route('addresses.store'), $this->validAddressPayload([
                'label' => 'Office',
                'is_default' => true,
            ]))
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseCount('addresses', 2);
        $this->assertSame(
            1,
            Address::query()->where('user_id', $customer->id)->where('is_default', true)->count(),
        );
        $this->assertTrue(
            Address::query()
                ->where('user_id', $customer->id)
                ->where('label', 'Office')
                ->first()
                ->is_default,
        );
    }

    public function test_customer_can_update_own_address(): void
    {
        $customer = User::factory()->create();
        $address = Address::factory()->for($customer)->default()->create([
            'city' => 'London',
        ]);

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->patch(route('addresses.update', $address), $this->validAddressPayload([
                'city' => 'Manchester',
                'is_default' => true,
            ]))
            ->assertRedirect(route('addresses.index'));

        $this->assertSame('Manchester', $address->fresh()->city);
    }

    public function test_customer_cannot_update_another_users_address(): void
    {
        $customer = User::factory()->create();
        $otherAddress = Address::factory()->for(User::factory())->create();

        $this->actingAs($customer)
            ->patch(route('addresses.update', $otherAddress), $this->validAddressPayload())
            ->assertForbidden();
    }

    public function test_customer_can_set_default_address(): void
    {
        $customer = User::factory()->create();
        $home = Address::factory()->for($customer)->default()->create(['label' => 'Home']);
        $office = Address::factory()->for($customer)->create(['label' => 'Office', 'is_default' => false]);

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->patch(route('addresses.default', $office))
            ->assertRedirect(route('addresses.index'));

        $this->assertFalse($home->fresh()->is_default);
        $this->assertTrue($office->fresh()->is_default);
    }

    public function test_deleting_default_promotes_another_address(): void
    {
        $customer = User::factory()->create();
        $home = Address::factory()->for($customer)->default()->create(['label' => 'Home']);
        $office = Address::factory()->for($customer)->create([
            'label' => 'Office',
            'is_default' => false,
            'created_at' => now()->addMinute(),
        ]);

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->delete(route('addresses.destroy', $home))
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseMissing('addresses', ['id' => $home->id]);
        $this->assertTrue($office->fresh()->is_default);
    }

    public function test_customer_can_delete_non_default_address(): void
    {
        $customer = User::factory()->create();
        $home = Address::factory()->for($customer)->default()->create(['label' => 'Home']);
        $office = Address::factory()->for($customer)->create(['label' => 'Office', 'is_default' => false]);

        $this->actingAs($customer)
            ->from(route('addresses.index'))
            ->delete(route('addresses.destroy', $office))
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseMissing('addresses', ['id' => $office->id]);
        $this->assertTrue($home->fresh()->is_default);
    }
}
