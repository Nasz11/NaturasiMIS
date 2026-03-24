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
        // ── USERS ──────────────────────────────────────────────
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

        // ── SYSTEM SETTINGS ────────────────────────────────────
        SystemSetting::create([
            'company_name'        => 'Naturasi Cheese Co.',
            'company_description' => 'Premium cheese manufacturing since 2020.',
            'theme'               => 'default',
        ]);

        // ── INVENTORY — FINISHED PRODUCTS ─────────────────────
        $products = [
            ['Burrata',                  'Cheese Product', 0, 'kg',  20],
            ['Stracciatella',            'Cheese Product', 0, 'kg',  20],
            ['Cherry Mozzarella',        'Cheese Product', 0, 'kg',  20],
            ['Traditional Mozzarella',   'Cheese Product', 0, 'kg',  20],
            ['Provola',                  'Cheese Product', 0, 'kg',  20],
            ['Mozzarella di Latte',      'Cheese Product', 0, 'kg',  20],
        ];

        // ── INVENTORY — RAW MATERIALS ──────────────────────────
        $rawMaterials = [
            ['Cagliata',         'Raw Materials', 0, 'kg', 10],
            ['Fresh Milk',       'Raw Materials', 0, 'L',  100],
            ['Cream',            'Raw Materials', 0, 'L',  20],
            ['Iodized Salt',     'Raw Materials', 0, 'kg', 10],
            ['Rock Salt',        'Raw Materials', 0, 'kg', 10],
            ['Trisodium',        'Raw Materials', 0, 'kg', 5],
            ['Rennet',           'Raw Materials', 0, 'kg', 2],
            ['Citric Acid',      'Raw Materials', 0, 'kg', 5],
            ['Palm Oil',         'Raw Materials', 0, 'L',  10],
            ['Skimmed Milk',     'Raw Materials', 0, 'L',  50],
            ['High Melt Starch', 'Raw Materials', 0, 'kg', 5],
            ['Butter Flavor',    'Raw Materials', 0, 'kg', 2],
            ['Butter Milk',      'Raw Materials', 0, 'L',  20],
            ['Parmesan Flavor',  'Raw Materials', 0, 'kg', 2],
            ['Cheddar Flavor',   'Raw Materials', 0, 'kg', 2],
            ['Milk Powder',      'Raw Materials', 0, 'kg', 10],
        ];

        foreach (array_merge($products, $rawMaterials) as [$name, $cat, $qty, $unit, $reorder]) {
            InventoryItem::create([
                'product_name'  => $name,
                'category'      => $cat,
                'quantity'      => $qty,
                'unit'          => $unit,
                'reorder_level' => $reorder,
                'updated_by'    => $admin->id,
            ]);
        }

        // ── PRODUCTION BATCHES ─────────────────────────────────
        ProductionBatch::create([
            'batch_number'    => 'B-2026-001',
            'product_type'    => 'Burrata',
            'quantity'        => 50,
            'production_date' => now()->subDays(3),
            'status'          => 'In Production',
            'remarks'         => 'Initial batch.',
            'staff_id'        => $production->id,
        ]);

        ProductionBatch::create([
            'batch_number'    => 'B-2026-002',
            'product_type'    => 'Traditional Mozzarella',
            'quantity'        => 80,
            'production_date' => now()->subDays(7),
            'status'          => 'Completed',
            'remarks'         => 'Batch completed successfully.',
            'staff_id'        => $production->id,
        ]);

        // ── BATCHES ────────────────────────────────────────────
        Batch::create([
            'batch_id'        => 'B-2026-001',
            'cheese_type'     => 'Burrata',
            'quantity'        => 50,
            'start_date'      => now()->subDays(3),
            'completion_date' => now()->addDays(2),
            'status'          => 'In Production',
            'staff_id'        => $production->id,
            'remarks'         => 'Initial batch.',
        ]);

        Batch::create([
            'batch_id'        => 'B-2026-002',
            'cheese_type'     => 'Traditional Mozzarella',
            'quantity'        => 80,
            'start_date'      => now()->subDays(7),
            'completion_date' => now()->subDays(1),
            'status'          => 'Completed',
            'staff_id'        => $production->id,
            'remarks'         => 'Batch completed successfully.',
        ]);
    }
}