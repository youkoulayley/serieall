<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * UserFactory.
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->faker;

        return [
            'id' => $faker->unique()->randomNumber(5),
            'username' => $faker->name,
            'user_url' => $faker->slug,
            'email' => $faker->unique()->safeEmail(),
            'role' => 1,
            'suspended' => 0,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'activated' => 1,
            'edito' => $faker->jobTitle,
            'antispoiler' => 1,
            'website' => '',
            'twitter' => '',
            'facebook' => '',
            'ip' => null,
        ];
    }
}
