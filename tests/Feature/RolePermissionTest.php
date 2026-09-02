<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_management_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('inventory.index'))->assertOk();
        $this->actingAs($admin)->get(route('production.index'))->assertOk();
        $this->actingAs($admin)->get(route('orders.index'))->assertOk();
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('settings.index'))->assertOk();
        $this->actingAs($admin)->get(route('logs.index'))->assertOk();
    }

    public function test_production_staff_cannot_access_user_or_system_settings(): void
    {
        $productionUser = User::factory()->create(['role' => 'production']);

        $this->actingAs($productionUser)->get(route('dashboard'))->assertOk();
        $this->actingAs($productionUser)->get(route('production.index'))->assertOk();

        // Should be forbidden from user management and system settings
        $this->actingAs($productionUser)->get(route('users.index'))->assertForbidden();
        $this->actingAs($productionUser)->get(route('settings.index'))->assertForbidden();
        $this->actingAs($productionUser)->get(route('logs.index'))->assertForbidden();
    }

    public function test_inventory_clerk_cannot_access_production_module(): void
    {
        $inventoryUser = User::factory()->create(['role' => 'inventory']);

        $this->actingAs($inventoryUser)->get(route('dashboard'))->assertOk();
        $this->actingAs($inventoryUser)->get(route('inventory.index'))->assertOk();
        $this->actingAs($inventoryUser)->get(route('orders.index'))->assertOk();

        // Forbidden from production floor and admin areas
        $this->actingAs($inventoryUser)->get(route('production.index'))->assertForbidden();
        $this->actingAs($inventoryUser)->get(route('users.index'))->assertForbidden();
    }
}
