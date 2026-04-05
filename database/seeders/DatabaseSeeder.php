<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
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

        User::create([
            'username' => 'inventory',
            'password' => Hash::make('5678'),
            'role'     => 'inventory',
            'status'   => 'Active',
        ]);

        User::create([
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

        foreach ($rawMaterials as [$name, $cat, $qty, $unit, $reorder]) {
            InventoryItem::create([
                'product_name'  => $name,
                'category'      => $cat,
                'quantity'      => $qty,
                'unit'          => $unit,
                'reorder_level' => $reorder,
                'updated_by'    => $admin->id,
            ]);
        }
    }
}