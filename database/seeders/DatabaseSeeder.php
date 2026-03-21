<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\ProductionBatch;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ──────────────────────────────────────
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('1234'),
            'role'     => 'admin',
            'status'   => 'Active',
            'email'    => 'admin@naturasi.com',
        ]);

        $inventory = User::create([
            'username' => 'inventory',
            'password' => Hash::make('5678'),
            'role'     => 'inventory',
            'status'   => 'Active',
        ]);

        $production = User::create([
            'username' => 'production',
            'password' => Hash::make('91011'),
            'role'     => 'production',
            'status'   => 'Active',
        ]);

        User::create([
            'username' => 'manager',
            'password' => Hash::make('121314'),
            'role'     => 'manager',
            'status'   => 'Active',
        ]);

        // ── SYSTEM SETTINGS ────────────────────────────
        SystemSetting::create([
            'company_name'        => 'Naturasi Cheese Co.',
            'company_description' => 'Premium cheese manufacturing since 2020.',
            'theme'               => 'default',
        ]);

        // ── INVENTORY ──────────────────────────────────
        $inventoryData = [
            ['Mozzarella Cheese', 'Cheese Product', 120, 'kg', 50],
            ['Cheddar Cheese',    'Cheese Product', 20,  'kg', 40],
            ['Fresh Milk',        'Raw Materials',  500, 'L',  100],
            ['Rennet',            'Ingredients',    5,   'kg', 2],
            ['Salt',              'Ingredients',    30,  'kg', 10],
            ['Cheese Cultures',   'Ingredients',    2,   'kg', 1],
            ['Packaging Boxes',   'Packaging',      200, 'pcs', 50],
            ['Vacuum Bags',       'Packaging',      15,  'pcs', 30],
        ];

        foreach ($inventoryData as [$name, $cat, $qty, $unit, $reorder]) {
            InventoryItem::create([
                'product_name'  => $name,
                'category'      => $cat,
                'quantity'      => $qty,
                'unit'          => $unit,
                'reorder_level' => $reorder,
                'updated_by'    => $admin->id,
            ]);
        }

        // ── PRODUCTION BATCHES ─────────────────────────
        ProductionBatch::create([
            'batch_number'    => 'B-2025-001',
            'product_type'    => 'Mozzarella Cheese',
            'quantity'        => 80,
            'production_date' => now()->subDays(5),
            'status'          => 'In Production',
            'remarks'         => 'Batch started this morning.',
            'staff_id'        => $production->id,
        ]);

        ProductionBatch::create([
            'batch_number'    => 'B-2025-002',
            'product_type'    => 'Cheddar Cheese',
            'quantity'        => 120,
            'production_date' => now()->subDays(11),
            'status'          => 'Completed',
            'remarks'         => 'Batch curing completed.',
            'staff_id'        => $production->id,
        ]);

        // ── BATCHES ────────────────────────────────────
        Batch::create([
            'batch_id'        => 'B-2025-001',
            'cheese_type'     => 'Mozzarella',
            'quantity'        => 80,
            'start_date'      => now()->subDays(5),
            'completion_date' => now()->addDays(2),
            'status'          => 'In Production',
            'staff_id'        => $production->id,
            'remarks'         => 'Batch started this morning.',
        ]);

        Batch::create([
            'batch_id'        => 'B-2025-002',
            'cheese_type'     => 'Cheddar',
            'quantity'        => 120,
            'start_date'      => now()->subDays(11),
            'completion_date' => now()->subDays(3),
            'status'          => 'Completed',
            'staff_id'        => $production->id,
            'remarks'         => 'Batch curing completed.',
        ]);
    }
}
