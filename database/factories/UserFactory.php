<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
class UserFactory extends Factory {
    public function definition(): array {
        return [
            'username' => $this->faker->unique()->userName(),
            'email'    => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'Active',
            'notifications_enabled' => true,
            'two_factor_enabled'    => false,
            'theme'    => 'default',
            'language' => 'en',
        ];
    }
}
