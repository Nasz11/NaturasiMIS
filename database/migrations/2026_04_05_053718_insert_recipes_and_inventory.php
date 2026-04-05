<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recipes
        DB::table('recipes')->insertOrIgnore([
            ['cheese_product'=>'Burrata',           'ingredient_name'=>'Cagliata', 'quantity_needed'=>0.4,  'unit'=>'kg'],
            ['cheese_product'=>'Stracciatella',     'ingredient_name'=>'Cagliata', 'quantity_needed'=>0.4,  'unit'=>'kg'],
            ['cheese_product'=>'Mozzarella Log',    'ingredient_name'=>'Cagliata', 'quantity_needed'=>1.0,  'unit'=>'kg'],
            ['cheese_product'=>'Provola',           'ingredient_name'=>'Cagliata', 'quantity_needed'=>1.0,  'unit'=>'kg'],
            ['cheese_product'=>'Cherry Mozzarella', 'ingredient_name'=>'Cagliata', 'quantity_needed'=>1.0,  'unit'=>'kg'],
        ]);

        // Inventory Items
        DB::table('inventory_items')->insertOrIgnore([
            ['id'=>1,  'product_name'=>'Burrata',               'quantity'=>0, 'unit'=>'kg'],
            ['id'=>2,  'product_name'=>'Stracciatella',         'quantity'=>0, 'unit'=>'kg'],
            ['id'=>3,  'product_name'=>'Cherry Mozzarella',     'quantity'=>0, 'unit'=>'kg'],
            ['id'=>5,  'product_name'=>'Provola',               'quantity'=>0, 'unit'=>'kg'],
            ['id'=>6,  'product_name'=>'Mozzarella di Latte',   'quantity'=>0, 'unit'=>'kg'],
            ['id'=>7,  'product_name'=>'Cagliata',              'quantity'=>0, 'unit'=>'kg'],
            ['id'=>8,  'product_name'=>'Fresh Milk',            'quantity'=>0, 'unit'=>'L'],
            ['id'=>9,  'product_name'=>'Cream',                 'quantity'=>0, 'unit'=>'L'],
            ['id'=>10, 'product_name'=>'Iodized Salt',          'quantity'=>0, 'unit'=>'kg'],
            ['id'=>11, 'product_name'=>'Rock Salt',             'quantity'=>0, 'unit'=>'kg'],
            ['id'=>12, 'product_name'=>'Trisodium',             'quantity'=>0, 'unit'=>'kg'],
            ['id'=>13, 'product_name'=>'Rennet',                'quantity'=>0, 'unit'=>'kg'],
            ['id'=>14, 'product_name'=>'Citric Acid',           'quantity'=>0, 'unit'=>'kg'],
            ['id'=>15, 'product_name'=>'Palm Oil',              'quantity'=>0, 'unit'=>'L'],
            ['id'=>16, 'product_name'=>'Skimmed Milk',          'quantity'=>0, 'unit'=>'L'],
            ['id'=>17, 'product_name'=>'High Melt Starch',      'quantity'=>0, 'unit'=>'kg'],
            ['id'=>18, 'product_name'=>'Butter Flavor',         'quantity'=>0, 'unit'=>'kg'],
            ['id'=>19, 'product_name'=>'Butter Milk',           'quantity'=>0, 'unit'=>'L'],
            ['id'=>20, 'product_name'=>'Parmesan Flavor',       'quantity'=>0, 'unit'=>'kg'],
            ['id'=>21, 'product_name'=>'Cheddar Flavor',        'quantity'=>0, 'unit'=>'kg'],
            ['id'=>22, 'product_name'=>'Milk Powder',           'quantity'=>0, 'unit'=>'kg'],
            ['id'=>23, 'product_name'=>'Traditional Mozzarella','quantity'=>0, 'unit'=>'kg'],
        ]);
    }

    public function down(): void
    {
        DB::table('recipes')->whereIn('cheese_product', ['Burrata','Stracciatella','Mozzarella Log','Provola','Cherry Mozzarella'])->delete();
        DB::table('inventory_items')->whereIn('id', [1,2,3,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23])->delete();
    }
};