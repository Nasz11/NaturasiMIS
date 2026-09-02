<?php

namespace Tests\Unit;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Policies\InventoryItemPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductionBatchPolicy;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    protected function createUserWithRole(string $role, int $id = 1): User
    {
        $user = new User();
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    public function test_order_policy_rules(): void
    {
        $policy = new OrderPolicy();
        $admin = $this->createUserWithRole('admin');
        $production = $this->createUserWithRole('production');
        $inventory = $this->createUserWithRole('inventory');

        $pendingOrder = new Order();
        $pendingOrder->status = 'Pending';

        $confirmedOrder = new Order();
        $confirmedOrder->status = 'Confirmed';

        // Admin & Inventory can view and create
        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($inventory));
        $this->assertFalse($policy->viewAny($production));

        // Can only update pending orders
        $this->assertTrue($policy->update($admin, $pendingOrder));
        $this->assertFalse($policy->update($admin, $confirmedOrder));

        // Only admin can delete
        $this->assertTrue($policy->delete($admin, $pendingOrder));
        $this->assertFalse($policy->delete($inventory, $pendingOrder));
    }

    public function test_production_batch_policy_rules(): void
    {
        $policy = new ProductionBatchPolicy();
        $admin = $this->createUserWithRole('admin');
        $production = $this->createUserWithRole('production');
        $inventory = $this->createUserWithRole('inventory');
        $batch = new ProductionBatch();

        $this->assertTrue($policy->viewAny($production));
        $this->assertTrue($policy->create($production));
        $this->assertTrue($policy->viewAny($admin));

        // Inventory cannot create production batches
        $this->assertFalse($policy->create($inventory));

        // Only admin can delete production batches
        $this->assertTrue($policy->delete($admin, $batch));
        $this->assertFalse($policy->delete($production, $batch));
    }

    public function test_inventory_item_policy_rules(): void
    {
        $policy = new InventoryItemPolicy();
        $admin = $this->createUserWithRole('admin');
        $inventory = $this->createUserWithRole('inventory');
        $production = $this->createUserWithRole('production');
        $item = new InventoryItem();

        $this->assertTrue($policy->viewAny($inventory));
        $this->assertTrue($policy->create($inventory));
        $this->assertFalse($policy->viewAny($production));

        // Only admin can permanently delete inventory
        $this->assertTrue($policy->delete($admin, $item));
        $this->assertFalse($policy->delete($inventory, $item));
    }

    public function test_user_policy_rules(): void
    {
        $policy = new UserPolicy();
        $admin = $this->createUserWithRole('admin', 1);
        $otherUser = $this->createUserWithRole('inventory', 2);

        // Only admin can view all users or create users
        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($otherUser));

        // Admin cannot delete themselves, but can delete another user
        $this->assertFalse($policy->delete($admin, $admin));
        $this->assertTrue($policy->delete($admin, $otherUser));
    }
}
