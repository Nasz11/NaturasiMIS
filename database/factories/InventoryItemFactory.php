<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class InventoryItemFactory extends Factory {
    public function definition(): array {
        return [
            'product_name'  => $this->faker->words(2, true),
            'category'      => 'Raw Materials',
            'quantity'      => $this->faker->numberBetween(10, 500),
            'unit'          => $this->faker->randomElement(['kg', 'liters', 'pcs']),
            'reorder_level' => 5,
            'cost_per_unit' => $this->faker->randomFloat(2, 10, 500),
            'status'        => 'In Stock',
            'updated_by'    => null,
            'is_archived'   => false,
        ];
    }
}
