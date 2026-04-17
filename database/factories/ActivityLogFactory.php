<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ActivityLogFactory extends Factory {
    public function definition(): array {
        return [
            'user_id'    => null,
            'username'   => $this->faker->userName(),
            'module'     => $this->faker->randomElement(['Reports', 'Inventory', 'Production']),
            'action'     => $this->faker->randomElement(['Generated Report', 'Created', 'Updated']),
            'details'    => $this->faker->sentence(),
            'ip_address' => $this->faker->ipv4(),
        ];
    }
}
