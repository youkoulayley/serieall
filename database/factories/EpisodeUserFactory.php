<?php

namespace Database\Factories;

use App\Models\Episode_user;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * EpisodeUserFactory.
 */
class EpisodeUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode_user::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->faker;

        return [
            'rate' => $faker->numberBetween(0, 20),
        ];
    }
}
