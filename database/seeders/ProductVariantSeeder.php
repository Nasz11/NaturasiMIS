<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_variants')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $variants = [
            ['id'=>2,  'cheese_product'=>'Burrata',           'variant_name'=>'50g',          'weight_grams'=>50],
            ['id'=>3,  'cheese_product'=>'Burrata',           'variant_name'=>'125g',         'weight_grams'=>125],
            ['id'=>4,  'cheese_product'=>'Burrata',           'variant_name'=>'150g',         'weight_grams'=>150],
            ['id'=>5,  'cheese_product'=>'Burrata',           'variant_name'=>'250g Solo',    'weight_grams'=>250],
            ['id'=>6,  'cheese_product'=>'Burrata',           'variant_name'=>'250g Duo',     'weight_grams'=>250],
            ['id'=>7,  'cheese_product'=>'Burrata',           'variant_name'=>'Cherry',       'weight_grams'=>125],
            ['id'=>8,  'cheese_product'=>'Burrata',           'variant_name'=>'Jamon',        'weight_grams'=>125],
            ['id'=>9,  'cheese_product'=>'Burrata',           'variant_name'=>'Truffle Combi','weight_grams'=>125],
            ['id'=>10, 'cheese_product'=>'Burrata',           'variant_name'=>'200g',         'weight_grams'=>200],
            ['id'=>11, 'cheese_product'=>'Burrata',           'variant_name'=>'200g Truffle', 'weight_grams'=>200],
            ['id'=>12, 'cheese_product'=>'Burrata',           'variant_name'=>'2/250g',       'weight_grams'=>500],
            ['id'=>14, 'cheese_product'=>'Stracciatella',     'variant_name'=>'Reg Tub',      'weight_grams'=>500],
            ['id'=>15, 'cheese_product'=>'Stracciatella',     'variant_name'=>'250g',         'weight_grams'=>250],
            ['id'=>16, 'cheese_product'=>'Stracciatella',     'variant_name'=>'334g',         'weight_grams'=>334],
            ['id'=>17, 'cheese_product'=>'Stracciatella',     'variant_name'=>'668g',         'weight_grams'=>668],
            ['id'=>18, 'cheese_product'=>'Stracciatella',     'variant_name'=>'Combi',        'weight_grams'=>500],
            ['id'=>19, 'cheese_product'=>'Mozzarella Log',    'variant_name'=>'1kg',          'weight_grams'=>1000],
            ['id'=>20, 'cheese_product'=>'Provola',           'variant_name'=>'1kg',          'weight_grams'=>1000],
            ['id'=>21, 'cheese_product'=>'Cherry Mozzarella', 'variant_name'=>'250g',         'weight_grams'=>250],
            ['id'=>22, 'cheese_product'=>'Cherry Mozzarella', 'variant_name'=>'1kg',          'weight_grams'=>1000],
        ];

        DB::table('product_variants')->insert($variants);
    }
}