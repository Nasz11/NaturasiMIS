<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class OrderFactory extends Factory {
    public function definition(): array {
        return [
            'po_number'    => 'PO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'client_id'    => null,
            'client_name'  => $this->faker->company(),
            'order_date'   => $this->faker->date(),
            'quantity'     => $this->faker->numberBetween(1, 100),
            'unit'         => $this->faker->randomElement(['kg', 'pcs', 'boxes']),
            'status'       => $this->faker->randomElement(['Pending', 'Confirmed', 'Cancelled']),
            'notes'        => $this->faker->sentence(),
            'created_by'   => null,
            'confirmed_at' => null,
            'is_archived'  => false,
        ];
    }
}
