<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ProductionBatch;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. USERS WITH STANDARDIZED DEMO ROLES ──────────────
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email'    => 'admin@lactoflow.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'status'   => 'Active',
            ]
        );

        $productionUser = User::updateOrCreate(
            ['username' => 'production'],
            [
                'email'    => 'production@lactoflow.com',
                'password' => Hash::make('prod123'),
                'role'     => 'production',
                'status'   => 'Active',
            ]
        );

        $inventoryUser = User::updateOrCreate(
            ['username' => 'inventory'],
            [
                'email'    => 'inventory@lactoflow.com',
                'password' => Hash::make('inv123'),
                'role'     => 'inventory',
                'status'   => 'Active',
            ]
        );

        $managerUser = User::updateOrCreate(
            ['username' => 'manager'],
            [
                'email'    => 'manager@lactoflow.com',
                'password' => Hash::make('mgr123'),
                'role'     => 'manager',
                'status'   => 'Active',
            ]
        );

        // ── 2. SYSTEM SETTINGS ─────────────────────────────────
        SystemSetting::firstOrCreate(
            ['company_name' => 'LactoFlow Artisanal Dairy Co.'],
            [
                'company_description' => 'Fine handcrafted Italian & European cheeses manufactured with precision.',
                'theme'               => 'default',
            ]
        );

        // ── 3. INVENTORY STOCK & INBOUND MOVEMENTS ─────────────
        $materials = [
            ['Cagliata',         'Raw Materials', 150.0, 'kg', 20.0, 180.00, 30],
            ['Fresh Milk',       'Raw Materials', 500.0, 'L',  50.0, 45.00,  7],
            ['Cream',            'Raw Materials', 80.0,  'L',  15.0, 120.00, 14],
            ['Iodized Salt',     'Raw Materials', 50.0,  'kg', 10.0, 25.00,  180],
            ['Rock Salt',        'Raw Materials', 40.0,  'kg', 10.0, 20.00,  180],
            ['Rennet',           'Raw Materials', 15.0,  'kg', 2.0,  350.00, 90],
            ['Citric Acid',      'Raw Materials', 25.0,  'kg', 5.0,  95.00,  120],
            ['Trisodium',        'Raw Materials', 20.0,  'kg', 5.0,  110.00, 120],
            ['Milk Powder',      'Raw Materials', 60.0,  'kg', 10.0, 160.00, 60],
        ];

        foreach ($materials as [$name, $category, $qty, $unit, $reorder, $cost, $daysToExpiry]) {
            $item = InventoryItem::firstOrCreate(
                ['product_name' => $name],
                [
                    'category'      => $category,
                    'quantity'      => 0,
                    'unit'          => $unit,
                    'reorder_level' => $reorder,
                    'cost_per_unit' => $cost,
                    'updated_by'    => $admin->id,
                ]
            );

            // Record inbound movement if no movement exists yet
            if ($item->movements()->count() === 0) {
                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'inbound',
                    'quantity'          => $qty,
                    'reference'         => 'INIT-STOCK-' . strtoupper(substr(md5($name), 0, 4)),
                    'remarks'           => 'Initial inventory replenishment',
                    'recorded_by'       => $inventoryUser->id,
                    'movement_date'     => now()->subDays(rand(1, 10)),
                    'expiry_date'       => now()->addDays($daysToExpiry),
                ]);

                $item->update(['quantity' => $qty]);
            }
        }

        // ── 4. CLIENTS ─────────────────────────────────────────
        $clientsData = [
            ['name' => 'Osteria Bella Roma', 'contact_person' => 'Marco Rossi', 'phone' => '+63 917 123 4567', 'email' => 'marco@bellaroma.ph', 'address' => 'Bonifacio Global City, Taguig'],
            ['name' => 'Trattoria Da Luigi', 'contact_person' => 'Luigi Moretti', 'phone' => '+63 918 987 6543', 'email' => 'orders@daluigi.com', 'address' => 'Makati Avenue, Makati City'],
            ['name' => 'The Grand Artisan Hotel', 'contact_person' => 'Chef Elena Gomez', 'phone' => '+63 920 555 0192', 'email' => 'elena.gomez@grandartisan.com', 'address' => 'Pasay City, Metro Manila'],
        ];

        $createdClients = [];
        foreach ($clientsData as $c) {
            $createdClients[] = Client::firstOrCreate(['name' => $c['name']], $c);
        }

        // ── 5. PRODUCTION BATCHES ──────────────────────────────
        $batches = [
            ['batch_number' => 'PB-2026-001', 'product_type' => 'Burrata', 'quantity' => 12.5, 'production_date' => now()->subDays(3), 'status' => 'Completed', 'remarks' => 'High quality curd formation', 'staff_id' => $productionUser->id],
            ['batch_number' => 'PB-2026-002', 'product_type' => 'Stracciatella', 'quantity' => 10.0, 'production_date' => now()->subDays(1), 'status' => 'Curing', 'remarks' => 'Cold aging chamber A', 'staff_id' => $productionUser->id],
            ['batch_number' => 'PB-2026-003', 'product_type' => 'Mozzarella Log', 'quantity' => 20.0, 'production_date' => now(), 'status' => 'In Production', 'remarks' => 'Morning stretch cycle', 'staff_id' => $productionUser->id],
        ];

        foreach ($batches as $batch) {
            ProductionBatch::firstOrCreate(['batch_number' => $batch['batch_number']], $batch);
        }

        // ── 6. SAMPLE ORDER ────────────────────────────────────
        if (Order::count() === 0 && !empty($createdClients)) {
            $burrataVariant = ProductVariant::where('cheese_product', 'Burrata')->first();
            $stracciaVariant = ProductVariant::where('cheese_product', 'Stracciatella')->first();

            $order = Order::create([
                'po_number'    => 'PO-2026-10081',
                'client_id'    => $createdClients[0]->id,
                'client_name'  => $createdClients[0]->name,
                'order_date'   => now()->subDays(1),
                'quantity'     => 5.0,
                'unit'         => 'kg',
                'status'       => 'Confirmed',
                'notes'        => 'Weekly restaurant replenishment',
                'created_by'   => $admin->id,
                'confirmed_at' => now()->subDays(1),
            ]);

            if ($burrataVariant) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'cheese_product'  => 'Burrata',
                    'variant_name'    => $burrataVariant->variant_name,
                    'weight_grams'    => $burrataVariant->weight_grams,
                    'quantity_pieces' => 20,
                    'total_kg'        => ($burrataVariant->weight_grams / 1000) * 20,
                ]);
            }

            if ($stracciaVariant) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'cheese_product'  => 'Stracciatella',
                    'variant_name'    => $stracciaVariant->variant_name,
                    'weight_grams'    => $stracciaVariant->weight_grams,
                    'quantity_pieces' => 10,
                    'total_kg'        => ($stracciaVariant->weight_grams / 1000) * 10,
                ]);
            }
        }

        // ── 7. ACTIVITY LOGS ───────────────────────────────────
        ActivityLog::firstOrCreate(
            ['action' => 'System Seeded'],
            [
                'module'   => 'System',
                'username' => 'admin',
                'details'  => 'System successfully initialized with artisanal production configuration.',
                'user_id'  => $admin->id,
            ]
        );
    }
}