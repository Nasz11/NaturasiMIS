<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ProductionBatchFactory extends Factory {
    public function definition(): array {
        return [
            'batch_number'    => 'BATCH-' . $this->faker->unique()->numberBetween(1000, 9999),
            'product_type'    => $this->faker->randomElement(['Gouda', 'Cheddar', 'Mozzarella']),
            'quantity'        => $this->faker->numberBetween(10, 300),
            'production_date' => $this->faker->date(),
            'status'          => $this->faker->randomElement(['In Production', 'Curing', 'Completed']),
            'remarks'         => $this->faker->sentence(),
            'staff_id'        => null,
            'is_archived'     => false,
        ];
    }
}
