<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmedOrderInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_an_order_records_outbound_inventory_movements(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $variant = ProductVariant::where('cheese_product', 'Burrata')->firstOrFail();

        foreach ([
            'Cagliata' => 10,
            'Cream' => 10,
            'Iodized Salt' => 10,
        ] as $name => $quantity) {
            $item = InventoryItem::where('product_name', $name)->firstOrFail();
            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'inbound',
                'quantity' => $quantity,
                'recorded_by' => $user->id,
                'movement_date' => now(),
            ]);
            $item->update(['quantity' => $item->computedQuantity()]);
        }

        $response = $this->actingAs($user)->post(route('orders.confirm'), [
            'client_name' => 'Test Client',
            'order_date' => today()->toDateString(),
            'items' => [[
                'product' => 'Burrata',
                'variant_id' => $variant->id,
                'quantity_pcs' => 1,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['status' => 'Confirmed']);
        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'outbound',
            'reference' => $this->app['db']->table('orders')->value('po_number'),
        ]);
    }
}
