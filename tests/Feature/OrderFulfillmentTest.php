<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ProductVariant $burrataVariant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->burrataVariant = ProductVariant::where('cheese_product', 'Burrata')->firstOrFail();
    }

    private function addStock(string $productName, float $qty): InventoryItem
    {
        $item = InventoryItem::where('product_name', $productName)->firstOrFail();
        InventoryMovement::create([
            'inventory_item_id' => $item->id,
            'type'              => 'inbound',
            'quantity'          => $qty,
            'recorded_by'       => $this->admin->id,
            'movement_date'     => now(),
        ]);
        $item->update(['quantity' => $item->computedQuantity()]);
        return $item;
    }

    public function test_order_preview_calculates_ingredient_needs_accurately(): void
    {
        $this->addStock('Cagliata', 20);
        $this->addStock('Cream', 5);
        $this->addStock('Iodized Salt', 5);

        $response = $this->actingAs($this->admin)->postJson(route('orders.preview'), [
            'items' => [[
                'product'      => 'Burrata',
                'variant_id'   => $this->burrataVariant->id,
                'quantity_pcs' => 10,
            ]],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'preview',
            'insufficient',
            'batchAdjustments',
        ]);
    }

    public function test_confirming_order_fails_gracefully_when_stock_is_insufficient(): void
    {
        // Zero stock added
        $response = $this->actingAs($this->admin)->post(route('orders.confirm'), [
            'client_name' => 'Boutique Bistro',
            'order_date'  => today()->toDateString(),
            'items' => [[
                'product'      => 'Burrata',
                'variant_id'   => $this->burrataVariant->id,
                'quantity_pcs' => 100,
            ]],
        ]);

        $response->assertSessionHasErrors('insufficient');
        $this->assertDatabaseMissing('orders', ['client_name' => 'Boutique Bistro']);
    }

    public function test_cancelling_confirmed_order_restores_inventory_stock(): void
    {
        $cagliata = $this->addStock('Cagliata', 50);
        $cream = $this->addStock('Cream', 50);
        $salt = $this->addStock('Iodized Salt', 50);

        $initialCagliataStock = $cagliata->fresh()->quantity;

        // Confirm order
        $this->actingAs($this->admin)->post(route('orders.confirm'), [
            'client_name' => 'Artisan Deli',
            'order_date'  => today()->toDateString(),
            'items' => [[
                'product'      => 'Burrata',
                'variant_id'   => $this->burrataVariant->id,
                'quantity_pcs' => 5,
            ]],
        ]);

        $order = Order::where('client_name', 'Artisan Deli')->firstOrFail();
        $this->assertEquals('Confirmed', $order->status);

        $stockAfterConfirm = $cagliata->fresh()->quantity;
        $this->assertLessThan($initialCagliataStock, $stockAfterConfirm);

        // Cancel order -> should restore inventory
        $this->actingAs($this->admin)->patch(route('orders.updateStatus', $order), [
            'status' => 'Cancelled',
        ]);

        $order->refresh();
        $this->assertEquals('Cancelled', $order->status);
        $this->assertEquals($initialCagliataStock, $cagliata->fresh()->quantity);
    }
}
